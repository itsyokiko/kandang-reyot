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

        $betinas = Kambing::where('jenis_kelamin', 'betina')->orderBy('nama')->get();
        $pejantans = Kambing::where('jenis_kelamin', 'jantan')->orderBy('nama')->get();

        return view('kelahirans.index', compact('kelahirans', 'perkawinans', 'betinas', 'pejantans'));
    }

    public function create(Request $request)
    {
        $perkawinans = Perkawinan::with(['betina', 'pejantan'])
            ->latest('tanggal_kawin')
            ->get();

        $betinas = Kambing::where('jenis_kelamin', 'betina')->orderBy('nama')->get();
        $pejantans = Kambing::where('jenis_kelamin', 'jantan')->orderBy('nama')->get();

        $selectedPerkawinanId = $request->query('perkawinan_id');
        $selectedPerkawinan = $selectedPerkawinanId ? Perkawinan::find($selectedPerkawinanId) : null;

        $selectedIndukId = $selectedPerkawinan ? $selectedPerkawinan->betina_id : $request->query('induk_id');
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'perkawinan_id' => ['nullable', 'exists:perkawinans,id'],
            'induk_id' => [
                'required',
                'exists:kambings,id',
                function ($attribute, $value, $fail) {
                    $kambing = Kambing::find($value);
                    if ($kambing && $kambing->jenis_kelamin !== 'betina') {
                        $fail('Induk yang dipilih harus berjenis kelamin betina.');
                    }
                },
            ],
            'pejantan_id' => [
                'required',
                'exists:kambings,id',
                'different:induk_id',
                function ($attribute, $value, $fail) {
                    $kambing = Kambing::find($value);
                    if ($kambing && $kambing->jenis_kelamin !== 'jantan') {
                        $fail('Pejantan yang dipilih harus berjenis kelamin jantan.');
                    }
                },
            ],
            'tanggal_kelahiran' => ['required', 'date'],
            'catatan' => ['nullable', 'string'],
            'anak' => ['required', 'array', 'min:1'],
            'anak.*.nama' => ['required', 'string', 'max:255'],
            'anak.*.jenis_kelamin' => ['required', 'in:jantan,betina'],
            'anak.*.ras' => ['nullable', 'string', 'max:255'],
            'anak.*.catatan' => ['nullable', 'string'],
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->filled('perkawinan_id')) {
                $perkawinan = Perkawinan::find($request->input('perkawinan_id'));
                if ($perkawinan) {
                    if ($perkawinan->betina_id != $request->input('induk_id') || $perkawinan->pejantan_id != $request->input('pejantan_id')) {
                        $validator->errors()->add('perkawinan_id', 'Pasangan perkawinan yang dipilih tidak sesuai dengan induk dan pejantan yang dipilih.');
                    }
                }
            }

            $anaks = $request->input('anak', []);
            $names = [];
            foreach ($anaks as $idx => $anak) {
                $name = isset($anak['nama']) ? trim(mb_strtolower($anak['nama'])) : '';
                if ($name !== '') {
                    if (in_array($name, $names, true)) {
                        $validator->errors()->add("anak.{$idx}.nama", 'Nama anak tidak boleh sama dalam satu peristiwa kelahiran.');
                    }
                    $names[] = $name;
                }
            }
        });

        $validated = $validator->validate();

        $kelahiran = DB::transaction(function () use ($validated) {
            $kelahiran = Kelahiran::create([
                'perkawinan_id' => $validated['perkawinan_id'] ?? null,
                'induk_id' => $validated['induk_id'],
                'pejantan_id' => $validated['pejantan_id'],
                'tanggal_kelahiran' => $validated['tanggal_kelahiran'],
                'catatan' => $validated['catatan'] ?? null,
            ]);

            $induk = Kambing::find($validated['induk_id']);

            foreach ($validated['anak'] as $childData) {
                Kambing::create([
                    'nama' => $childData['nama'],
                    'jenis_kelamin' => $childData['jenis_kelamin'],
                    'ras' => !empty($childData['ras']) ? $childData['ras'] : ($induk ? $induk->ras : null),
                    'tanggal_lahir' => $validated['tanggal_kelahiran'],
                    'status' => 'hidup',
                    'catatan' => $childData['catatan'] ?? null,
                    'kelahiran_id' => $kelahiran->id,
                    'induk_id' => $validated['induk_id'],
                    'pejantan_id' => $validated['pejantan_id'],
                ]);
            }

            if (!empty($validated['perkawinan_id'])) {
                $perkawinan = Perkawinan::find($validated['perkawinan_id']);
                if ($perkawinan && $perkawinan->status !== 'berhasil') {
                    $perkawinan->update(['status' => 'berhasil']);
                }
            }

            return $kelahiran;
        });

        return redirect()->route('kelahirans.show', $kelahiran)->with('success', 'Catatan kelahiran dan data anak berhasil disimpan.');
    }

    public function show(Kelahiran $kelahiran)
    {
        $kelahiran->load(['induk', 'pejantan', 'perkawinan', 'anaks']);

        $perkawinans = Perkawinan::with(['betina', 'pejantan'])
            ->latest('tanggal_kawin')
            ->get();

        $betinas = Kambing::where('jenis_kelamin', 'betina')->orderBy('nama')->get();
        $pejantans = Kambing::where('jenis_kelamin', 'jantan')->orderBy('nama')->get();

        return view('kelahirans.show', compact('kelahiran', 'perkawinans', 'betinas', 'pejantans'));
    }

    public function edit(Kelahiran $kelahiran)
    {
        $kelahiran->load(['induk', 'pejantan', 'perkawinan', 'anaks']);

        $perkawinans = Perkawinan::with(['betina', 'pejantan'])
            ->latest('tanggal_kawin')
            ->get();

        $betinas = Kambing::where('jenis_kelamin', 'betina')->orderBy('nama')->get();
        $pejantans = Kambing::where('jenis_kelamin', 'jantan')->orderBy('nama')->get();

        return view('kelahirans.edit', compact('kelahiran', 'perkawinans', 'betinas', 'pejantans'));
    }

    public function update(Request $request, Kelahiran $kelahiran)
    {
        $validator = Validator::make($request->all(), [
            'perkawinan_id' => ['nullable', 'exists:perkawinans,id'],
            'induk_id' => [
                'required',
                'exists:kambings,id',
                function ($attribute, $value, $fail) {
                    $kambing = Kambing::find($value);
                    if ($kambing && $kambing->jenis_kelamin !== 'betina') {
                        $fail('Induk yang dipilih harus berjenis kelamin betina.');
                    }
                },
            ],
            'pejantan_id' => [
                'required',
                'exists:kambings,id',
                'different:induk_id',
                function ($attribute, $value, $fail) {
                    $kambing = Kambing::find($value);
                    if ($kambing && $kambing->jenis_kelamin !== 'jantan') {
                        $fail('Pejantan yang dipilih harus berjenis kelamin jantan.');
                    }
                },
            ],
            'tanggal_kelahiran' => ['required', 'date'],
            'catatan' => ['nullable', 'string'],
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->filled('perkawinan_id')) {
                $perkawinan = Perkawinan::find($request->input('perkawinan_id'));
                if ($perkawinan) {
                    if ($perkawinan->betina_id != $request->input('induk_id') || $perkawinan->pejantan_id != $request->input('pejantan_id')) {
                        $validator->errors()->add('perkawinan_id', 'Pasangan perkawinan yang dipilih tidak sesuai dengan induk dan pejantan yang dipilih.');
                    }
                }
            }
        });

        $validated = $validator->validate();

        DB::transaction(function () use ($kelahiran, $validated) {
            $kelahiran->update([
                'perkawinan_id' => $validated['perkawinan_id'] ?? null,
                'induk_id' => $validated['induk_id'],
                'pejantan_id' => $validated['pejantan_id'],
                'tanggal_kelahiran' => $validated['tanggal_kelahiran'],
                'catatan' => $validated['catatan'] ?? null,
            ]);

            // Sync birth date and parent IDs on existing children of this birth
            Kambing::where('kelahiran_id', $kelahiran->id)->update([
                'tanggal_lahir' => $validated['tanggal_kelahiran'],
                'induk_id' => $validated['induk_id'],
                'pejantan_id' => $validated['pejantan_id'],
            ]);
        });

        return redirect()->route('kelahirans.show', $kelahiran)->with('success', 'Catatan kelahiran berhasil diperbarui.');
    }

    public function destroy(Kelahiran $kelahiran)
    {
        DB::transaction(function () use ($kelahiran) {
            // Safely unlink children from this kelahiran
            Kambing::where('kelahiran_id', $kelahiran->id)->update([
                'kelahiran_id' => null,
            ]);

            $kelahiran->delete();
        });

        return redirect()->route('kelahirans.index')->with('success', 'Catatan kelahiran berhasil dihapus.');
    }
}
