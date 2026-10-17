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

        return view('kelahirans.index', compact('kelahirans', 'perkawinans', 'betinas', 'pejantans'));
    }

    public function create(Request $request)
    {
        $perkawinans = Perkawinan::with(['betina', 'pejantan'])
            ->latest('tanggal_kawin')
            ->get();

        $betinas  = Kambing::where('jenis_kelamin', 'betina')->orderBy('nama')->get();
        $pejantans = Kambing::where('jenis_kelamin', 'jantan')->orderBy('nama')->get();

        $selectedPerkawinanId = $request->query('perkawinan_id');
        $selectedPerkawinan   = $selectedPerkawinanId ? Perkawinan::find($selectedPerkawinanId) : null;

        $selectedIndukId    = $selectedPerkawinan ? $selectedPerkawinan->betina_id   : $request->query('induk_id');
        $selectedPejantanId = $selectedPerkawinan ? $selectedPerkawinan->pejantan_id : $request->query('pejantan_id');

        return view('kelahirans.create', compact(
            'perkawinans',
            'betinas',
            'pejantans',
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
            // anak bisa link ke kambing yang ada (kambing_id), atau anak baru (nama wajib)
            'anak.*.kambing_id'         => ['nullable', 'exists:kambings,id'],
            'anak.*.nama'               => ['nullable', 'string', 'max:255'],
            'anak.*.jenis_kelamin'      => ['nullable', 'in:jantan,betina'],
            'anak.*.tanggal_lahir'      => ['required', 'date'],
            'anak.*.ras'                => ['nullable', 'string', 'max:255'],
            'anak.*.catatan'            => ['nullable', 'string'],
        ], [
            'anak.*.tanggal_lahir.required' => 'Tanggal lahir setiap anak wajib diisi.',
        ]);

        // Tambahan validasi: jika tidak ada kambing_id, nama wajib & unik
        $validator->after(function ($v) use ($request) {
            $namaList = [];
            foreach ((array) $request->input('anak', []) as $i => $item) {
                $isExisting = !empty($item['kambing_id']);
                if (!$isExisting) {
                    if (empty($item['nama'])) {
                        $v->errors()->add("anak.{$i}.nama", 'Nama anak wajib diisi jika bukan memilih kambing yang sudah ada.');
                    } else {
                        $nama = $item['nama'];
                        if (in_array(mb_strtolower($nama), $namaList)) {
                            $v->errors()->add("anak.{$i}.nama", "Nama anak tidak boleh sama dalam satu pengisian.");
                        }
                        $namaList[] = mb_strtolower($nama);
                        if (Kambing::where('nama', $nama)->exists()) {
                            $v->errors()->add("anak.{$i}.nama", "Nama kambing \"{$nama}\" sudah digunakan.");
                        }
                    }
                    if (empty($item['jenis_kelamin'])) {
                        $v->errors()->add("anak.{$i}.jenis_kelamin", 'Jenis kelamin anak wajib dipilih.');
                    }
                }
            }

            // Validasi perkawinan match
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

        $createdKelahirans = DB::transaction(function () use ($validated, $request) {
            $indukId    = $validated['induk_id'];
            $pejantanId = $validated['pejantan_id'];
            $perkawinanId = $validated['perkawinan_id'] ?? null;
            $catatan    = $validated['catatan'] ?? null;
            $induk      = Kambing::find($indukId);

            // Group anak by tanggal_lahir → satu kelahiran per tanggal unik
            $groups = [];
            foreach ($validated['anak'] as $item) {
                $tgl = $item['tanggal_lahir'];
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
                    if (!empty($childData['kambing_id'])) {
                        // Link kambing existing ke kelahiran ini
                        Kambing::where('id', $childData['kambing_id'])->update([
                            'kelahiran_id' => $kelahiran->id,
                            'induk_id'     => $indukId,
                            'pejantan_id'  => $pejantanId,
                            'tanggal_lahir' => $tglLahir,
                        ]);
                    } else {
                        // Buat kambing baru
                        Kambing::create([
                            'nama'         => $childData['nama'],
                            'jenis_kelamin' => $childData['jenis_kelamin'],
                            'ras'          => !empty($childData['ras']) ? $childData['ras'] : ($induk?->ras),
                            'tanggal_lahir' => $tglLahir,
                            'status'       => 'hidup',
                            'catatan'      => $childData['catatan'] ?? null,
                            'kelahiran_id' => $kelahiran->id,
                            'induk_id'     => $indukId,
                            'pejantan_id'  => $pejantanId,
                        ]);
                    }
                }

                $created[] = $kelahiran;
            }

            // Tandai perkawinan berhasil
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
