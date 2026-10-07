<?php

namespace App\Http\Controllers;

use App\Models\Kambing;
use App\Models\Perkawinan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PerkawinanController extends Controller
{
    public function index()
    {
        $perkawinans = Perkawinan::with(['betina', 'pejantan'])
            ->latest('tanggal_kawin')
            ->get();

        $betinas = Kambing::where('jenis_kelamin', 'betina')->orderBy('nama')->get();
        $pejantans = Kambing::where('jenis_kelamin', 'jantan')->orderBy('nama')->get();

        return view('perkawinans.index', compact('perkawinans', 'betinas', 'pejantans'));
    }

    public function create(Request $request)
    {
        $betinas = Kambing::where('jenis_kelamin', 'betina')->orderBy('nama')->get();
        $pejantans = Kambing::where('jenis_kelamin', 'jantan')->orderBy('nama')->get();

        $selectedBetinaId = $request->query('betina_id');
        $selectedPejantanId = $request->query('pejantan_id');

        return view('perkawinans.create', compact('betinas', 'pejantans', 'selectedBetinaId', 'selectedPejantanId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'betina_id' => [
                'required',
                'exists:kambings,id',
                function ($attribute, $value, $fail) {
                    $kambing = Kambing::find($value);
                    if ($kambing && $kambing->jenis_kelamin !== 'betina') {
                        $fail('Kambing betina yang dipilih harus berjenis kelamin betina.');
                    }
                },
            ],
            'pejantan_id' => [
                'required',
                'exists:kambings,id',
                'different:betina_id',
                function ($attribute, $value, $fail) {
                    $kambing = Kambing::find($value);
                    if ($kambing && $kambing->jenis_kelamin !== 'jantan') {
                        $fail('Pejantan yang dipilih harus berjenis kelamin jantan.');
                    }
                },
            ],
            'tanggal_kawin' => ['required', 'date'],
            'status' => ['required', 'in:menunggu,berhasil,tidak_berhasil'],
            'catatan' => ['nullable', 'string'],
        ]);

        $perkawinan = Perkawinan::create($validated);

        return redirect()->route('perkawinans.show', $perkawinan)->with('success', 'Catatan perkawinan berhasil disimpan.');
    }

    public function show(Perkawinan $perkawinan)
    {
        $perkawinan->load(['betina', 'pejantan']);

        $betinas = Kambing::where('jenis_kelamin', 'betina')->orderBy('nama')->get();
        $pejantans = Kambing::where('jenis_kelamin', 'jantan')->orderBy('nama')->get();

        return view('perkawinans.show', compact('perkawinan', 'betinas', 'pejantans'));
    }

    public function edit(Perkawinan $perkawinan)
    {
        $betinas = Kambing::where('jenis_kelamin', 'betina')->orderBy('nama')->get();
        $pejantans = Kambing::where('jenis_kelamin', 'jantan')->orderBy('nama')->get();

        return view('perkawinans.edit', compact('perkawinan', 'betinas', 'pejantans'));
    }

    public function update(Request $request, Perkawinan $perkawinan)
    {
        $validator = Validator::make($request->all(), [
            'betina_id' => [
                'required',
                'exists:kambings,id',
                function ($attribute, $value, $fail) {
                    $kambing = Kambing::find($value);
                    if ($kambing && $kambing->jenis_kelamin !== 'betina') {
                        $fail('Kambing betina yang dipilih harus berjenis kelamin betina.');
                    }
                },
            ],
            'pejantan_id' => [
                'required',
                'exists:kambings,id',
                'different:betina_id',
                function ($attribute, $value, $fail) {
                    $kambing = Kambing::find($value);
                    if ($kambing && $kambing->jenis_kelamin !== 'jantan') {
                        $fail('Pejantan yang dipilih harus berjenis kelamin jantan.');
                    }
                },
            ],
            'tanggal_kawin' => ['required', 'date'],
            'status' => ['required', 'in:menunggu,berhasil,tidak_berhasil'],
            'catatan' => ['nullable', 'string'],
        ]);

        $validator->after(function ($validator) use ($request, $perkawinan) {
            if ($perkawinan->kelahirans()->exists()) {
                if ($request->input('betina_id') != $perkawinan->betina_id || $request->input('pejantan_id') != $perkawinan->pejantan_id) {
                    $validator->errors()->add('betina_id', 'Pasangan perkawinan tidak dapat diubah karena sudah memiliki catatan kelahiran terkait.');
                }
            }
        });

        $validated = $validator->validate();

        $perkawinan->update($validated);

        return redirect()->route('perkawinans.show', $perkawinan)->with('success', 'Catatan perkawinan berhasil diperbarui.');
    }

    public function destroy(Perkawinan $perkawinan)
    {
        $perkawinan->delete();

        return redirect()->route('perkawinans.index')->with('success', 'Catatan perkawinan berhasil dihapus.');
    }
}
