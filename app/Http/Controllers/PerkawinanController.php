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

        $betinas = Kambing::where('jenis_kelamin', 'betina')->where('status', '!=', 'mati')->orderBy('nama')->get();
        $pejantans = Kambing::where('jenis_kelamin', 'jantan')->where('status', '!=', 'mati')->orderBy('nama')->get();

        return view('perkawinans.index', compact('perkawinans', 'betinas', 'pejantans'));
    }

    public function create(Request $request)
    {
        $betinas = Kambing::where('jenis_kelamin', 'betina')->where('status', '!=', 'mati')->orderBy('nama')->get();
        $pejantans = Kambing::where('jenis_kelamin', 'jantan')->where('status', '!=', 'mati')->orderBy('nama')->get();

        $selectedBetinaId = $request->query('betina_id');
        $selectedPejantanId = $request->query('pejantan_id');

        return view('perkawinans.create', compact('betinas', 'pejantans', 'selectedBetinaId', 'selectedPejantanId'));
    }

    public function checkInbreeding(Request $request)
    {
        $betinaId = $request->input('betina_id');
        $pejantanId = $request->input('pejantan_id');
        $inbreeding = Kambing::checkInbreeding($betinaId, $pejantanId);

        return response()->json([
            'is_inbreeding' => !empty($inbreeding),
            'detail' => $inbreeding,
        ]);
    }

    public function store(Request $request)
    {
        $metode = $request->input('metode_kawin', 'alami');

        $rules = [
            'metode_kawin'  => ['nullable', 'in:alami,inseminasi,luar'],
            'betina_id'     => [
                'required',
                'exists:kambings,id',
                function ($attribute, $value, $fail) {
                    $kambing = Kambing::find($value);
                    if ($kambing && $kambing->jenis_kelamin !== 'betina') {
                        $fail('Kambing betina yang dipilih harus berjenis kelamin betina.');
                    }
                    if ($kambing && $kambing->status === 'mati') {
                        $fail('Kambing yang sudah mati tidak dapat dikawinkan.');
                    }
                },
            ],
            'tanggal_kawin' => ['required', 'date'],
            'status'        => ['required', 'in:menunggu,berhasil,tidak_berhasil'],
            'catatan'       => ['nullable', 'string'],
        ];

        if ($metode === 'alami') {
            $rules['pejantan_id'] = [
                'required',
                'exists:kambings,id',
                'different:betina_id',
                function ($attribute, $value, $fail) {
                    $kambing = Kambing::find($value);
                    if ($kambing && $kambing->jenis_kelamin !== 'jantan') {
                        $fail('Pejantan yang dipilih harus berjenis kelamin jantan.');
                    }
                    if ($kambing && $kambing->status === 'mati') {
                        $fail('Pejantan yang sudah mati tidak dapat dipilih.');
                    }
                },
            ];
        } else {
            $rules['nama_pejantan_luar'] = ['required', 'string', 'max:255'];
            $rules['pejantan_id'] = ['nullable'];
        }

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request, $metode) {
            if ($metode === 'alami' && $request->filled('pejantan_id')) {
                $inbreeding = Kambing::checkInbreeding($request->input('betina_id'), $request->input('pejantan_id'));
                if ($inbreeding) {
                    $validator->errors()->add('pejantan_id', "Dilarang perkawinan sedarah ({$inbreeding['title']}): {$inbreeding['message']}");
                }
            }
        });

        $validated = $validator->validate();

        // Handle Pejantan Luar / Inseminasi Buatan
        if ($metode !== 'alami') {
            $namaLuar = trim($validated['nama_pejantan_luar']);
            $catatanKambing = $metode === 'inseminasi'
                ? 'Inseminasi Buatan (Straw IB)'
                : 'Pejantan luar kandang';

            $pejantanRecord = Kambing::where('nama', $namaLuar)
                ->where('jenis_kelamin', 'jantan')
                ->first();

            if (!$pejantanRecord) {
                $pejantanRecord = Kambing::create([
                    'nama'          => $namaLuar,
                    'jenis_kelamin' => 'jantan',
                    'status'        => 'hidup',
                    'catatan'       => $catatanKambing,
                ]);
            }

            $validated['pejantan_id'] = $pejantanRecord->id;
            $prefixKet = $metode === 'inseminasi' ? '[Inseminasi Buatan (IB)]' : '[Pejantan Luar Kandang]';
            $validated['catatan'] = trim($prefixKet . ' ' . ($validated['catatan'] ?? ''));
        }

        unset($validated['nama_pejantan_luar'], $validated['metode_kawin'], $validated['abaikan_inbreeding']);

        $perkawinan = Perkawinan::create($validated);

        return redirect()->route('perkawinans.show', $perkawinan)->with('success', 'Catatan perkawinan berhasil disimpan.');
    }

    public function show(Perkawinan $perkawinan)
    {
        $perkawinan->load(['betina', 'pejantan']);

        $betinas = Kambing::where('jenis_kelamin', 'betina')->where('status', '!=', 'mati')->orderBy('nama')->get();
        $pejantans = Kambing::where('jenis_kelamin', 'jantan')->where('status', '!=', 'mati')->orderBy('nama')->get();

        return view('perkawinans.show', compact('perkawinan', 'betinas', 'pejantans'));
    }

    public function edit(Perkawinan $perkawinan)
    {
        $betinas = Kambing::where('jenis_kelamin', 'betina')->where('status', '!=', 'mati')->orderBy('nama')->get();
        $pejantans = Kambing::where('jenis_kelamin', 'jantan')->where('status', '!=', 'mati')->orderBy('nama')->get();

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
                    if ($kambing && $kambing->status === 'mati') {
                        $fail('Kambing yang sudah mati tidak dapat dikawinkan.');
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

            $inbreeding = Kambing::checkInbreeding($request->input('betina_id'), $request->input('pejantan_id'));
            if ($inbreeding) {
                $validator->errors()->add('pejantan_id', "Dilarang perkawinan sedarah ({$inbreeding['title']}): {$inbreeding['message']}");
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
