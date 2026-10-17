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
    public function index()
    {
        $kelahirans = Kelahiran::with(['induk', 'pejantan', 'perkawinan', 'anaks'])
            ->latest('tanggal_kelahiran')
            ->get();

        $perkawinans = Perkawinan::with(['betina', 'pejantan'])
            ->latest('tanggal_kawin')
            ->get();

        $betinas  = Kambing::where('jenis_kelamin', 'betina')->orderBy('nama')->get();
        $pejantans = Kambing::where('jenis_kelamin', 'jantan')->orderBy('nama')->get();

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

        $betinas  = Kambing::where('jenis_kelamin', 'betina')->orderBy('nama')->get();
        $pejantans = Kambing::where('jenis_kelamin', 'jantan')->orderBy('nama')->get();

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
                },
            ],
            'pejantan_id' => [
                'required',
                'exists:kambings,id',
                'different:induk_id',
                function ($attribute, $value, $fail) {
                    $k = Kambing::find($value);
                    if ($k && $k->jenis_kelamin !== 'jantan') {
                        $fail('Pejantan yang dipilih harus berjenis kelamin jantan.');
                    }
                },
            ],
            'catatan'                   => ['nullable', 'string'],
            'anak'                      => ['required', 'array', 'min:1'],
            'anak.*.kambing_id'         => ['nullable', 'exists:kambings,id'],
            'anak.*.nama'               => ['nullable', 'string', 'max:255'],
            'anak.*.jenis_kelamin'      => ['nullable', 'in:jantan,betina'],
            'anak.*.tanggal_lahir'      => ['required', 'date'],
            'anak.*.ras'                => ['nullable', 'string', 'max:255'],
            'anak.*.pejantan_id'        => ['nullable', 'exists:kambings,id'],
            'anak.*.catatan'            => ['nullable', 'string'],
        ], [
            'anak.*.tanggal_lahir.required' => 'Tanggal lahir setiap anak wajib diisi.',
        ]);

        $validator->after(function ($v) use ($request) {
            $namaList = [];
            foreach ((array) $request->input('anak', []) as $i => $item) {
                $nama = trim($item['nama'] ?? '');
                $kambingId = $item['kambing_id'] ?? null;

                if (empty($nama) && empty($kambingId)) {
                    $v->errors()->add("anak.{$i}.nama", 'Nama anak wajib diisi.');
                    continue;
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
            }

            // Validasi kecocokan perkawinan
            if ($request->filled('perkawinan_id')) {
                $perkawinan = Perkawinan::find($request->input('perkawinan_id'));
                if ($perkawinan) {
                    if (
                        $perkawinan->betina_id != $request->input('induk_id') ||
                        $perkawinan->pejantan_id != $request->input('pejantan_id')
                    ) {
                        $v->errors()->add('perkawinan_id', 'Pasangan perkawinan tidak sesuai dengan induk dan pejantan yang dipilih.');
                    }
                }
            }
        });

        $validated = $validator->validate();

        $createdKelahirans = DB::transaction(function () use ($validated) {
            $indukId    = $validated['induk_id'];
            $pejantanId = $validated['pejantan_id'];
            $perkawinanId = $validated['perkawinan_id'] ?? null;
            $catatan    = $validated['catatan'] ?? null;
            $induk      = Kambing::find($indukId);

            // Group anak by tanggal_lahir -> satu record kelahiran per tanggal unik
            $groups = [];
            foreach ($validated['anak'] as $item) {
                $tgl = !empty($item['tanggal_lahir']) ? $item['tanggal_lahir'] : date('Y-m-d');
                $groups[$tgl][] = $item;
            }

            $created = [];
            foreach ($groups as $tglLahir => $anaks) {
                $kelahiran = Kelahiran::create([
                    'perkawinan_id'     => $perkawinanId,
                    'induk_id'          => $indukId,
                    'pejantan_id'       => $pejantanId,
                    'tanggal_kelahiran' => $tglLahir,
                    'catatan'           => $catatan,
                ]);

                foreach ($anaks as $childData) {
                    $childPejantan = !empty($childData['pejantan_id']) ? $childData['pejantan_id'] : $pejantanId;
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
        $betinas     = Kambing::where('jenis_kelamin', 'betina')->orderBy('nama')->get();
        $pejantans   = Kambing::where('jenis_kelamin', 'jantan')->orderBy('nama')->get();

        return view('kelahirans.show', compact('kelahiran', 'perkawinans', 'betinas', 'pejantans'));
    }

    public function edit(Kelahiran $kelahiran)
    {
        $kelahiran->load(['induk', 'pejantan', 'perkawinan', 'anaks']);

        $perkawinans = Perkawinan::with(['betina', 'pejantan'])->latest('tanggal_kawin')->get();
        $betinas     = Kambing::where('jenis_kelamin', 'betina')->orderBy('nama')->get();
        $pejantans   = Kambing::where('jenis_kelamin', 'jantan')->orderBy('nama')->get();

        return view('kelahirans.edit', compact('kelahiran', 'perkawinans', 'betinas', 'pejantans'));
    }

    public function update(Request $request, Kelahiran $kelahiran)
    {
        $validator = Validator::make($request->all(), [
            'perkawinan_id'     => ['nullable', 'exists:perkawinans,id'],
            'induk_id'          => [
                'required',
                'exists:kambings,id',
                function ($attribute, $value, $fail) {
                    $k = Kambing::find($value);
                    if ($k && $k->jenis_kelamin !== 'betina') {
                        $fail('Induk yang dipilih harus berjenis kelamin betina.');
                    }
                },
            ],
            'pejantan_id' => [
                'required',
                'exists:kambings,id',
                'different:induk_id',
                function ($attribute, $value, $fail) {
                    $k = Kambing::find($value);
                    if ($k && $k->jenis_kelamin !== 'jantan') {
                        $fail('Pejantan yang dipilih harus berjenis kelamin jantan.');
                    }
                },
            ],
            'tanggal_kelahiran' => ['required', 'date'],
            'catatan'           => ['nullable', 'string'],
        ]);

        $validator->after(function ($v) use ($request) {
            if ($request->filled('perkawinan_id')) {
                $perkawinan = Perkawinan::find($request->input('perkawinan_id'));
                if ($perkawinan) {
                    if (
                        $perkawinan->betina_id != $request->input('induk_id') ||
                        $perkawinan->pejantan_id != $request->input('pejantan_id')
                    ) {
                        $v->errors()->add('perkawinan_id', 'Pasangan perkawinan tidak sesuai dengan induk dan pejantan yang dipilih.');
                    }
                }
            }
        });

        $validated = $validator->validate();

        DB::transaction(function () use ($kelahiran, $validated) {
            $kelahiran->update([
                'perkawinan_id'     => $validated['perkawinan_id'] ?? null,
                'induk_id'          => $validated['induk_id'],
                'pejantan_id'       => $validated['pejantan_id'],
                'tanggal_kelahiran' => $validated['tanggal_kelahiran'],
                'catatan'           => $validated['catatan'] ?? null,
            ]);

            Kambing::where('kelahiran_id', $kelahiran->id)->update([
                'tanggal_lahir' => $validated['tanggal_kelahiran'],
                'induk_id'      => $validated['induk_id'],
                'pejantan_id'   => $validated['pejantan_id'],
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
