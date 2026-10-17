<?php

namespace App\Http\Controllers;

use App\Models\Kambing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class KambingController extends Controller
{
    public function index(Request $request)
    {
        $query = Kambing::query();

        // Search berdasarkan nama
        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . trim($request->input('search')) . '%');
        }

        // Filter jenis kelamin
        if ($request->filled('jenis_kelamin') && in_array($request->input('jenis_kelamin'), ['jantan', 'betina'])) {
            $query->where('jenis_kelamin', $request->input('jenis_kelamin'));
        }

        // Filter status
        if ($request->filled('status') && in_array($request->input('status'), ['hidup', 'mati', 'dijual'])) {
            $query->where('status', $request->input('status'));
        }

        // Filter ras
        if ($request->filled('ras')) {
            $query->where('ras', $request->input('ras'));
        }

        // Filter tanggal lahir dari
        if ($request->filled('tanggal_lahir_dari')) {
            $query->whereDate('tanggal_lahir', '>=', $request->input('tanggal_lahir_dari'));
        }

        // Filter tanggal lahir sampai
        if ($request->filled('tanggal_lahir_sampai')) {
            $query->whereDate('tanggal_lahir', '<=', $request->input('tanggal_lahir_sampai'));
        }

        // Filter kategori usia: anak (< 6 bulan), muda (6 - 12 bulan), dewasa (> 12 bulan)
        if ($request->filled('kategori_usia')) {
            $kat = $request->input('kategori_usia');
            $now = now();
            if ($kat === 'anak') {
                $query->whereNotNull('tanggal_lahir')
                      ->whereDate('tanggal_lahir', '>', $now->copy()->subMonths(6));
            } elseif ($kat === 'muda') {
                $query->whereNotNull('tanggal_lahir')
                      ->whereDate('tanggal_lahir', '<=', $now->copy()->subMonths(6))
                      ->whereDate('tanggal_lahir', '>=', $now->copy()->subMonths(12));
            } elseif ($kat === 'dewasa') {
                $query->whereNotNull('tanggal_lahir')
                      ->whereDate('tanggal_lahir', '<', $now->copy()->subMonths(12));
            }
        }

        $kambings = $query->latest()->paginate(10)->withQueryString();

        // Ambil pilihan ras unik yang ada di database
        $availableRas = Kambing::whereNotNull('ras')
            ->where('ras', '!=', '')
            ->distinct()
            ->orderBy('ras')
            ->pluck('ras');

        return view('kambings.index', compact('kambings', 'availableRas'));
    }

    public function create()
    {
        return view('kambings.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kambings', 'nama')->where('user_id', Auth::id()),
            ],
            'jenis_kelamin' => ['required', 'in:jantan,betina'],
            'ras' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'status' => ['required', 'in:hidup,mati,dijual'],
            'catatan' => ['nullable', 'string'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,heic,heif', 'max:15360'],
        ], [
            'foto.max' => 'Ukuran foto terlalu besar (maksimal 15MB).',
            'foto.image' => 'File yang diunggah harus berupa gambar (JPG, PNG, WEBP).',
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('kambing', 'public');
        }

        $kambing = Kambing::create($validated);

        return redirect()->route('kambings.show', $kambing)->with('success', 'Data kambing berhasil ditambahkan.');
    }

    public function show(Kambing $kambing)
    {
        $kambing->load(['induk', 'pejantan', 'kelahiran', 'riwayatSakit', 'pengingatPerawatan']);

        if ($kambing->jenis_kelamin === 'betina') {
            $kambing->load(['perkawinanBetina.pejantan', 'kelahiranSebagaiInduk.anaks']);
        } else {
            $kambing->load(['perkawinanPejantan.betina', 'kelahiranSebagaiPejantan.anaks']);
        }

        return view('kambings.show', compact('kambing'));
    }

    public function edit(Kambing $kambing)
    {
        return view('kambings.edit', compact('kambing'));
    }

    public function update(Request $request, Kambing $kambing)
    {
        $validator = Validator::make($request->all(), [
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kambings', 'nama')->where('user_id', Auth::id())->ignore($kambing->id),
            ],
            'jenis_kelamin' => ['required', 'in:jantan,betina'],
            'ras' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'status' => ['required', 'in:hidup,mati,dijual'],
            'catatan' => ['nullable', 'string'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,heic,heif', 'max:15360'],
        ], [
            'foto.max' => 'Ukuran foto terlalu besar (maksimal 15MB).',
            'foto.image' => 'File yang diunggah harus berupa gambar (JPG, PNG, WEBP).',
        ]);

        $validator->after(function ($validator) use ($request, $kambing) {
            $newGender = $request->input('jenis_kelamin');
            if ($newGender !== $kambing->jenis_kelamin) {
                if ($kambing->jenis_kelamin === 'betina' && ($kambing->anakSebagaiInduk()->exists() || $kambing->perkawinanBetina()->exists() || $kambing->kelahiranSebagaiInduk()->exists())) {
                    $validator->errors()->add('jenis_kelamin', 'Jenis kelamin tidak dapat diubah menjadi jantan karena kambing ini memiliki riwayat sebagai betina/induk.');
                }
                if ($kambing->jenis_kelamin === 'jantan' && ($kambing->anakSebagaiPejantan()->exists() || $kambing->perkawinanPejantan()->exists() || $kambing->kelahiranSebagaiPejantan()->exists())) {
                    $validator->errors()->add('jenis_kelamin', 'Jenis kelamin tidak dapat diubah menjadi betina karena kambing ini memiliki riwayat sebagai pejantan.');
                }
            }
        });

        $validated = $validator->validate();

        if ($request->hasFile('foto')) {
            if ($kambing->foto && Storage::disk('public')->exists($kambing->foto)) {
                Storage::disk('public')->delete($kambing->foto);
            }
            $validated['foto'] = $request->file('foto')->store('kambing', 'public');
        } else {
            unset($validated['foto']);
        }

        $kambing->update($validated);

        return redirect()->route('kambings.show', $kambing)->with('success', 'Data kambing berhasil diperbarui.');
    }

    public function destroy(Kambing $kambing)
    {
        if ($kambing->foto && Storage::disk('public')->exists($kambing->foto)) {
            Storage::disk('public')->delete($kambing->foto);
        }

        $kambing->delete();

        return redirect()->route('kambings.index')->with('success', 'Data kambing berhasil dihapus.');
    }

    /** Search kambing by name for autocomplete — returns JSON. */
    public function autocomplete(Request $request)
    {
        $q = trim($request->input('q', ''));
        $excludeId = $request->input('exclude_id'); // exclude a specific kambing (e.g. induk itself)

        $query = Kambing::select('id', 'nama', 'ras', 'jenis_kelamin', 'tanggal_lahir', 'induk_id', 'pejantan_id')
            ->where('status', 'hidup');

        if ($q !== '') {
            $query->where('nama', 'like', '%' . $q . '%');
        }

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return response()->json(
            $query->orderBy('nama')->limit(10)->get()
        );
    }
}
