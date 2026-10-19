<?php

namespace App\Http\Controllers;

use App\Models\Kambing;
use App\Models\Kelahiran;
use App\Models\Perkawinan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class KelahiranController extends Controller
{
    private function getIndukQuery()
    {
        $batasCempe = now()->subMonths(6)->toDateString();

        return Kambing::where('jenis_kelamin', 'betina')
            ->where('status', 'hidup')
            ->where(function ($q) use ($batasCempe) {
                $q->whereNull('tanggal_lahir')
                  ->orWhere('tanggal_lahir', '<=', $batasCempe);
            })
            ->orderBy('nama');
    }

    private function getPejantanQuery()
    {
        $batasCempe = now()->subMonths(6)->toDateString();

        return Kambing::where('jenis_kelamin', 'jantan')
            ->where('status', 'hidup')
            ->where(function ($q) use ($batasCempe) {
                $q->whereNull('tanggal_lahir')
                  ->orWhere('tanggal_lahir', '<=', $batasCempe);
            })
            ->orderBy('nama');
    }

    public function index()
    {
        $kelahirans = Kelahiran::with(['induk', 'pejantan', 'perkawinan', 'anaks'])
            ->latest('tanggal_kelahiran')
            ->get();

        $perkawinans = Perkawinan::with(['betina', 'pejantan'])
            ->latest('tanggal_kawin')
            ->get();

        $betinas  = $this->getIndukQuery()->get();
        $pejantans = $this->getPejantanQuery()->get();

        $allKambings = Kambing::select('id', 'nama', 'ras', 'jenis_kelamin', 'tanggal_lahir', 'induk_id', 'pejantan_id')
            ->orderBy('nama')
            ->get();

        return view('kelahirans.index', compact('kelahirans', 'perkawinans', 'betinas', 'pejantans', 'allKambings'));
    }

    public function create(Request $request)
    {
        $perkawinans = Perkawinan::with(['betina', 'pejantan'])
            ->latest('tanggal_kawin')
            ->get();

        $betinas  = $this->getIndukQuery()->get();
        $pejantans = $this->getPejantanQuery()->get();

        $allKambings = Kambing::select('id', 'nama', 'ras', 'jenis_kelamin', 'tanggal_lahir', 'induk_id', 'pejantan_id')
            ->orderBy('nama')
            ->get();

        $selectedPerkawinanId = $request->query('perkawinan_id');
        $selectedPerkawinan   = $selectedPerkawinanId ? Perkawinan::find($selectedPerkawinanId) : null;

        $selectedIndukId    = $selectedPerkawinan ? $selectedPerkawinan->betina_id   : $request->query('induk_id');
        $selectedPejantanId = $selectedPerkawinan ? $selectedPerkawinan->pejantan_id : $request->query('pejantan_id');

        return view('kelahirans.create', compact(
            'perkawinans',
            'betinas',
            'pejantans',
            'allKambings',
            'selectedPerkawinanId',
            'selectedIndukId',
            'selectedPejantanId'
        ));
    }

    /**
     * API: return kambing yang sudah punya relasi induk/pejantan tertentu,
     * yang belum terikat ke kelahiran manapun — atau sudah terikat tp bisa diubah.
     */
    public function anakByParents(Request $request)
    {
        $indukId    = $request->input('induk_id');
        $pejantanId = $request->input('pejantan_id');

        if (!$indukId && !$pejantanId) {
            return response()->json([]);
        }

        $query = Kambing::select('id', 'nama', 'ras', 'jenis_kelamin', 'tanggal_lahir', 'induk_id', 'pejantan_id', 'kelahiran_id');

        if ($indukId) {
            $query->where('induk_id', $indukId);
        }
        if ($pejantanId) {
            $query->where('pejantan_id', $pejantanId);
        }

        return response()->json($query->orderBy('tanggal_lahir')->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'perkawinan_id' => ['nullable', 'exists:perkawinans,id'],
            'induk_id'      => [
                'required',
                'exists:kambings,id',
                function ($attribute, $value, $fail) {
                    $k = Kambing::find($value);
                    if ($k && $k->jenis_kelamin !== 'betina') {
                        $fail('Induk yang dipilih harus berjenis kelamin betina.');
                    }
                    if ($k && $k->status !== 'hidup') {
                        $fail("Induk yang berstatus {$k->status} tidak dapat dicatat melahirkan.");
                    }
                    if ($k && $k->tanggal_lahir && $k->tanggal_lahir->diffInMonths(now()) < 6) {
                        $fail('Induk masih cempe (usia di bawah 6 bulan) dan belum matang secara biologis.');
                    }
                },
            ],
            'pejantan_id' => [
                'nullable',
                'exists:kambings,id',
                'different:induk_id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $k = Kambing::find($value);
                        if ($k && $k->jenis_kelamin !== 'jantan') {
                            $fail('Pejantan yang dipilih harus berjenis kelamin jantan.');
                        }
                        if ($k && $k->status !== 'hidup') {
                            $fail("Pejantan yang berstatus {$k->status} tidak dapat dipilih.");
                        }
                    }
                },
            ],
            'catatan'                       => ['nullable', 'string'],
            'anak'                          => ['required', 'array', 'min:1'],
            'anak.*.kambing_id'             => ['nullable', 'exists:kambings,id'],
            'anak.*.nama'                   => ['nullable', 'string', 'max:255'],
            'anak.*.jenis_kelamin'          => ['nullable', 'in:jantan,betina'],
            'anak.*.tanggal_lahir'          => ['required', 'date', 'before_or_equal:today'],
            'anak.*.ras'                    => ['nullable', 'string', 'max:255'],
            'anak.*.pejantan_id'            => ['nullable', 'string'],
            'anak.*.nama_pejantan_luar'     => ['nullable', 'string', 'max:255'],
            'anak.*.catatan'                => ['nullable', 'string'],
        ], [
            'anak.*.tanggal_lahir.required' => 'Tanggal lahir setiap anak wajib diisi.',
            'anak.*.tanggal_lahir.before_or_equal' => 'Tanggal lahir anak tidak boleh di masa depan.',
        ]);

        $validator->after(function ($v) use ($request) {
            $indukId = $request->input('induk_id');
            $pejantanId = $request->input('pejantan_id');
            $induk = $indukId ? Kambing::find($indukId) : null;
            $pejantan = $pejantanId ? Kambing::find($pejantanId) : null;

            // Validasi perkawinan jika ada
            $perkawinan = null;
            if ($request->filled('perkawinan_id')) {
                $perkawinan = Perkawinan::find($request->input('perkawinan_id'));
                if ($perkawinan) {
                    if (
                        $perkawinan->betina_id != $indukId ||
                        ($request->filled('pejantan_id') && $perkawinan->pejantan_id != $pejantanId)
                    ) {
                        $v->errors()->add('perkawinan_id', 'Pasangan perkawinan tidak sesuai dengan induk dan pejantan yang dipilih.');
                    }
                }
            }

            // Ambil riwayat tanggal kelahiran sebelumnya dari induk yang sama
            $existingKelahiranDates = $induk ? Kelahiran::where('induk_id', $induk->id)->pluck('tanggal_kelahiran')->all() : [];

            $namaList = [];
            foreach ((array) $request->input('anak', []) as $i => $item) {
                $nama = trim($item['nama'] ?? '');
                $kambingId = $item['kambing_id'] ?? null;
                $tglLahirAnak = $item['tanggal_lahir'] ?? null;

                if (empty($nama) && empty($kambingId)) {
                    $v->errors()->add("anak.{$i}.nama", 'Nama anak wajib diisi.');
                    continue;
                }

                // Cek agar anak bukan induk atau bapaknya sendiri
                if ($kambingId) {
                    if ($kambingId == $indukId) {
                        $v->errors()->add("anak.{$i}.kambing_id", 'Kambing tidak dapat menjadi anak dari dirinya sendiri.');
                    }
                    if ($pejantanId && $kambingId == $pejantanId) {
                        $v->errors()->add("anak.{$i}.kambing_id", 'Kambing tidak dapat menjadi bapak sekaligus anak dari dirinya sendiri.');
                    }
                }

                if (!empty($nama)) {
                    if (in_array(mb_strtolower($nama), $namaList)) {
                        $v->errors()->add("anak.{$i}.nama", "Nama anak \"{$nama}\" tidak boleh sama dalam satu peristiwa kelahiran.");
                    }
                    $namaList[] = mb_strtolower($nama);
                }

                // Jika kambing belum ada di DB, jenis kelamin wajib dipilih
                $exists = $kambingId ? true : (!empty($nama) && Kambing::where('nama', $nama)->exists());
                if (!$exists && empty($item['jenis_kelamin'])) {
                    $v->errors()->add("anak.{$i}.jenis_kelamin", 'Jenis kelamin anak wajib dipilih.');
                }

                // Validasi kronologi tanggal lahir anak
                if ($tglLahirAnak) {
                    // 1. Tidak boleh mendahului tanggal lahir induk
                    if ($induk && $induk->tanggal_lahir && $tglLahirAnak <= $induk->tanggal_lahir->toDateString()) {
                        $v->errors()->add("anak.{$i}.tanggal_lahir", "Tanggal lahir anak ({$tglLahirAnak}) tidak boleh mendahului/sama dengan tanggal lahir induknya ({$induk->tanggal_lahir->format('d/m/Y')}).");
                    }

                    // 2. Tidak boleh mendahului tanggal lahir pejantan
                    if ($pejantan && $pejantan->tanggal_lahir && $tglLahirAnak <= $pejantan->tanggal_lahir->toDateString()) {
                        $v->errors()->add("anak.{$i}.tanggal_lahir", "Tanggal lahir anak ({$tglLahirAnak}) tidak boleh mendahului/sama dengan tanggal lahir pejantan ({$pejantan->tanggal_lahir->format('d/m/Y')}).");
                    }

                    // 3. Tidak boleh mendahului tanggal perkawinan
                    if ($perkawinan && $perkawinan->tanggal_kawin && $tglLahirAnak < $perkawinan->tanggal_kawin->toDateString()) {
                        $v->errors()->add("anak.{$i}.tanggal_lahir", "Tanggal lahir anak ({$tglLahirAnak}) tidak boleh mendahului tanggal perkawinan ({$perkawinan->tanggal_kawin->format('d/m/Y')}).");
                    }

                    // 4. Jarak dengan kelahiran sebelumnya minimal 120 hari (masa bunting normal ±150 hari)
                    foreach ($existingKelahiranDates as $prevDate) {
                        $prevDateStr = \Carbon\Carbon::parse($prevDate)->toDateString();
                        if ($prevDateStr !== $tglLahirAnak) {
                            $diffDays = abs(\Carbon\Carbon::parse($tglLahirAnak)->diffInDays(\Carbon\Carbon::parse($prevDateStr)));
                            if ($diffDays < 120) {
                                $v->errors()->add("anak.{$i}.tanggal_lahir", "Induk {$induk->nama} sudah tercatat melahirkan pada " . \Carbon\Carbon::parse($prevDateStr)->format('d/m/Y') . ". Jarak antar kelahiran minimal 120 hari (selisih saat ini: {$diffDays} hari).");
                                break;
                            }
                        }
                    }
                }
            }
        });

        $validated = $validator->validate();

        $createdKelahirans = DB::transaction(function () use ($validated) {
            $indukId      = $validated['induk_id'];
            $pejantanId   = $validated['pejantan_id'] ?? null;
            $perkawinanId = $validated['perkawinan_id'] ?? null;
            $catatan      = $validated['catatan'] ?? null;
            $induk        = Kambing::find($indukId);

            // Group anak by tanggal_lahir -> satu record kelahiran per tanggal unik
            $groups = [];
            foreach ($validated['anak'] as $item) {
                $tgl = !empty($item['tanggal_lahir']) ? $item['tanggal_lahir'] : date('Y-m-d');
                $groups[$tgl][] = $item;
            }

            $created = [];
            foreach ($groups as $tglLahir => $anaks) {
                // Tentukan pejantan untuk kelahiran record: jika top-level ada, pakai itu.
                // Jika top-level kosong, tapi semua anak punya pejantan_id yang sama dan bukan null, pakai itu.
                $kelahiranPejantanId = $pejantanId;

                $kelahiran = Kelahiran::create([
                    'perkawinan_id'     => $perkawinanId,
                    'induk_id'          => $indukId,
                    'pejantan_id'       => $kelahiranPejantanId,
                    'tanggal_kelahiran' => $tglLahir,
                    'catatan'           => $catatan,
                ]);

                foreach ($anaks as $childData) {
                    $namaLuar = trim($childData['nama_pejantan_luar'] ?? '');
                    $childPejantanInput = $childData['pejantan_id'] ?? null;

                    if (!empty($namaLuar)) {
                        // Cari atau buat record kambing pejantan luar kandang
                        $pejantanLuar = Kambing::where('nama', $namaLuar)
                            ->where('jenis_kelamin', 'jantan')
                            ->first();
                        if (!$pejantanLuar) {
                            $pejantanLuar = Kambing::create([
                                'nama'          => $namaLuar,
                                'jenis_kelamin' => 'jantan',
                                'status'        => 'hidup',
                                'catatan'       => 'Pejantan luar kandang',
                            ]);
                        }
                        $childPejantan = $pejantanLuar->id;
                    } elseif ($childPejantanInput === 'none') {
                        $childPejantan = null;
                    } elseif (!empty($childPejantanInput) && is_numeric($childPejantanInput)) {
                        $childPejantan = (int) $childPejantanInput;
                    } else {
                        $childPejantan = $pejantanId ?: null;
                    }

                    $childRas = !empty($childData['ras']) ? $childData['ras'] : ($induk?->ras);

                    // Cari apakah kambing sudah ada di database (ID atau nama)
                    $existingKambing = null;
                    if (!empty($childData['kambing_id'])) {
                        $existingKambing = Kambing::find($childData['kambing_id']);
                    }
                    if (!$existingKambing && !empty($childData['nama'])) {
                        $existingKambing = Kambing::where('nama', trim($childData['nama']))->first();
                    }

                    if ($existingKambing) {
                        // Tautkan kambing existing ke catatan kelahiran ini
                        $existingKambing->update([
                            'kelahiran_id'  => $kelahiran->id,
                            'induk_id'      => $indukId,
                            'pejantan_id'   => $childPejantan,
                            'tanggal_lahir' => $tglLahir,
                            'ras'           => $childRas ?: $existingKambing->ras,
                        ]);
                    } else {
                        // Buat data kambing baru
                        Kambing::create([
                            'nama'          => trim($childData['nama']),
                            'jenis_kelamin' => $childData['jenis_kelamin'] ?? 'jantan',
                            'ras'           => $childRas,
                            'tanggal_lahir' => $tglLahir,
                            'status'        => 'hidup',
                            'catatan'       => $childData['catatan'] ?? null,
                            'kelahiran_id'  => $kelahiran->id,
                            'induk_id'      => $indukId,
                            'pejantan_id'   => $childPejantan,
                        ]);
                    }
                }

                // Jika kelahiran belum punya pejantan_id tapi anak-anaknya punya pejantan yang seragam, update
                if (!$kelahiran->pejantan_id) {
                    $uniqueFathers = $kelahiran->anaks()->pluck('pejantan_id')->filter()->unique();
                    if ($uniqueFathers->count() === 1) {
                        $kelahiran->update(['pejantan_id' => $uniqueFathers->first()]);
                    }
                }

                $created[] = $kelahiran;
            }

            // Tandai status perkawinan berhasil jika ada
            if ($perkawinanId) {
                $perkawinan = Perkawinan::find($perkawinanId);
                if ($perkawinan && $perkawinan->status !== 'berhasil') {
                    $perkawinan->update(['status' => 'berhasil']);
                }
            }

            return $created;
        });

        $jumlah = count($createdKelahirans);
        $msg = $jumlah > 1
            ? "Berhasil mencatat {$jumlah} peristiwa kelahiran sekaligus."
            : 'Catatan kelahiran dan data anak berhasil disimpan.';

        return redirect()->route('kelahirans.show', $createdKelahirans[0])->with('success', $msg);
    }

    public function show(Kelahiran $kelahiran)
    {
        $kelahiran->load(['induk', 'pejantan', 'perkawinan', 'anaks']);

        $perkawinans = Perkawinan::with(['betina', 'pejantan'])->latest('tanggal_kawin')->get();
        $betinas     = $this->getIndukQuery()->orWhere('id', $kelahiran->induk_id)->get();
        $pejantans   = $this->getPejantanQuery()->orWhere('id', $kelahiran->pejantan_id)->get();

        return view('kelahirans.show', compact('kelahiran', 'perkawinans', 'betinas', 'pejantans'));
    }

    public function edit(Kelahiran $kelahiran)
    {
        $kelahiran->load(['induk', 'pejantan', 'perkawinan', 'anaks']);

        $perkawinans = Perkawinan::with(['betina', 'pejantan'])->latest('tanggal_kawin')->get();
        $betinas     = $this->getIndukQuery()->orWhere('id', $kelahiran->induk_id)->get();
        $pejantans   = $this->getPejantanQuery()->orWhere('id', $kelahiran->pejantan_id)->get();

        return view('kelahirans.edit', compact('kelahiran', 'perkawinans', 'betinas', 'pejantans'));
    }

    public function update(Request $request, Kelahiran $kelahiran)
    {
        $validator = Validator::make($request->all(), [
            'perkawinan_id'     => ['nullable', 'exists:perkawinans,id'],
            'induk_id'          => [
                'required',
                'exists:kambings,id',
                function ($attribute, $value, $fail) use ($kelahiran) {
                    $k = Kambing::find($value);
                    if ($k && $k->jenis_kelamin !== 'betina') {
                        $fail('Induk yang dipilih harus berjenis kelamin betina.');
                    }
                    if ($k && $k->status !== 'hidup' && $k->id != $kelahiran->induk_id) {
                        $fail("Induk yang berstatus {$k->status} tidak dapat dicatat melahirkan.");
                    }
                    if ($k && $k->tanggal_lahir && $k->tanggal_lahir->diffInMonths(now()) < 6 && $k->id != $kelahiran->induk_id) {
                        $fail('Induk masih cempe (usia di bawah 6 bulan) dan belum matang secara biologis.');
                    }
                },
            ],
            'pejantan_id' => [
                'nullable',
                'exists:kambings,id',
                'different:induk_id',
                function ($attribute, $value, $fail) use ($kelahiran) {
                    if ($value) {
                        $k = Kambing::find($value);
                        if ($k && $k->jenis_kelamin !== 'jantan') {
                            $fail('Pejantan yang dipilih harus berjenis kelamin jantan.');
                        }
                        if ($k && $k->status !== 'hidup' && $k->id != $kelahiran->pejantan_id) {
                            $fail("Pejantan yang berstatus {$k->status} tidak dapat dipilih.");
                        }
                    }
                },
            ],
            'tanggal_kelahiran' => ['required', 'date', 'before_or_equal:today'],
            'catatan'           => ['nullable', 'string'],
        ], [
            'tanggal_kelahiran.before_or_equal' => 'Tanggal kelahiran tidak boleh di masa depan.',
        ]);

        $validator->after(function ($v) use ($request, $kelahiran) {
            $indukId = $request->input('induk_id');
            $pejantanId = $request->input('pejantan_id');
            $tglKelahiran = $request->input('tanggal_kelahiran');
            $induk = $indukId ? Kambing::find($indukId) : null;
            $pejantan = $pejantanId ? Kambing::find($pejantanId) : null;

            if ($request->filled('perkawinan_id')) {
                $perkawinan = Perkawinan::find($request->input('perkawinan_id'));
                if ($perkawinan) {
                    if (
                        $perkawinan->betina_id != $indukId ||
                        ($request->filled('pejantan_id') && $perkawinan->pejantan_id != $pejantanId)
                    ) {
                        $v->errors()->add('perkawinan_id', 'Pasangan perkawinan tidak sesuai dengan induk dan pejantan yang dipilih.');
                    }
                    if ($tglKelahiran && $perkawinan->tanggal_kawin && $tglKelahiran < $perkawinan->tanggal_kawin->toDateString()) {
                        $v->errors()->add('tanggal_kelahiran', "Tanggal kelahiran tidak boleh mendahului tanggal perkawinan ({$perkawinan->tanggal_kawin->format('d/m/Y')}).");
                    }
                }
            }

            if ($tglKelahiran) {
                if ($induk && $induk->tanggal_lahir && $tglKelahiran <= $induk->tanggal_lahir->toDateString()) {
                    $v->errors()->add('tanggal_kelahiran', "Tanggal kelahiran tidak boleh mendahului/sama dengan tanggal lahir induk ({$induk->tanggal_lahir->format('d/m/Y')}).");
                }
                if ($pejantan && $pejantan->tanggal_lahir && $tglKelahiran <= $pejantan->tanggal_lahir->toDateString()) {
                    $v->errors()->add('tanggal_kelahiran', "Tanggal kelahiran tidak boleh mendahului/sama dengan tanggal lahir pejantan ({$pejantan->tanggal_lahir->format('d/m/Y')}).");
                }

                if ($induk) {
                    $prevDates = Kelahiran::where('induk_id', $induk->id)
                        ->where('id', '!=', $kelahiran->id)
                        ->pluck('tanggal_kelahiran');

                    foreach ($prevDates as $prevDate) {
                        $diffDays = abs(\Carbon\Carbon::parse($tglKelahiran)->diffInDays(\Carbon\Carbon::parse($prevDate)));
                        if ($diffDays < 120) {
                            $v->errors()->add('tanggal_kelahiran', "Induk {$induk->nama} sudah memiliki catatan kelahiran lain pada tanggal " . \Carbon\Carbon::parse($prevDate)->format('d/m/Y') . ". Jarak antar kelahiran minimal 120 hari.");
                            break;
                        }
                    }
                }
            }
        });

        $validated = $validator->validate();

        DB::transaction(function () use ($kelahiran, $validated) {
            $kelahiran->update([
                'perkawinan_id'     => $validated['perkawinan_id'] ?? null,
                'induk_id'          => $validated['induk_id'],
                'pejantan_id'       => $validated['pejantan_id'] ?? null,
                'tanggal_kelahiran' => $validated['tanggal_kelahiran'],
                'catatan'           => $validated['catatan'] ?? null,
            ]);

            Kambing::where('kelahiran_id', $kelahiran->id)->update([
                'tanggal_lahir' => $validated['tanggal_kelahiran'],
                'induk_id'      => $validated['induk_id'],
                'pejantan_id'   => $validated['pejantan_id'] ?? null,
            ]);
        });

        return redirect()->route('kelahirans.show', $kelahiran)->with('success', 'Catatan kelahiran berhasil diperbarui.');
    }

    public function destroy(Kelahiran $kelahiran)
    {
        DB::transaction(function () use ($kelahiran) {
            Kambing::where('kelahiran_id', $kelahiran->id)->update(['kelahiran_id' => null]);
            $kelahiran->delete();
        });

        return redirect()->route('kelahirans.index')->with('success', 'Catatan kelahiran berhasil dihapus.');
    }
}
