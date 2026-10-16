<?php

namespace App\Http\Controllers;

use App\Models\Kambing;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RelasiController extends Controller
{
    /**
     * Tampilkan halaman pengelolaan relasi silsilah mandiri.
     */
    public function index(Request $request)
    {
        $pejantans = Kambing::where('jenis_kelamin', 'jantan')->orderBy('nama')->get();
        $induks = Kambing::where('jenis_kelamin', 'betina')->orderBy('nama')->get();
        $allKambings = Kambing::with(['induk', 'pejantan'])->orderBy('nama')->get();

        $totalKambing = $allKambings->count();
        $terhubungCount = $allKambings->filter(fn ($k) => $k->induk_id || $k->pejantan_id)->count();
        $belumTerhubungCount = $totalKambing - $terhubungCount;

        // Daftar kambing yang memiliki anak (sebagai pejantan atau induk)
        $kambingDenganAnak = $allKambings->filter(function ($k) {
            return $k->anakSebagaiInduk()->exists() || $k->anakSebagaiPejantan()->exists();
        });

        // Filter daftar kambing yang belum punya orang tua untuk rekomendasi cepat
        $kambingTanpaOrangTua = $allKambings->filter(fn ($k) => !$k->induk_id && !$k->pejantan_id);

        $selectedKambingId = $request->query('kambing_id');

        return view('relasi.index', compact(
            'pejantans',
            'induks',
            'allKambings',
            'totalKambing',
            'terhubungCount',
            'belumTerhubungCount',
            'kambingDenganAnak',
            'kambingTanpaOrangTua',
            'selectedKambingId'
        ));
    }

    /**
     * Simpan relasi orang tua (pejantan/induk) ke anak-anak yang dipilih.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pejantan_id' => ['nullable', 'exists:kambings,id'],
            'induk_id' => ['nullable', 'exists:kambings,id'],
            'anak_ids' => ['required', 'array', 'min:1'],
            'anak_ids.*' => ['required', 'exists:kambings,id'],
        ], [
            'anak_ids.required' => 'Pilih minimal satu kambing sebagai anak.',
            'anak_ids.min' => 'Pilih minimal satu kambing sebagai anak.',
        ]);

        if (empty($validated['pejantan_id']) && empty($validated['induk_id'])) {
            throw ValidationException::withMessages([
                'pejantan_id' => 'Pilih minimal salah satu orang tua: Pejantan (Ayah) atau Induk (Ibu).',
            ]);
        }

        // Validasi jenis kelamin orang tua
        if (!empty($validated['pejantan_id'])) {
            $pejantan = Kambing::find($validated['pejantan_id']);
            if ($pejantan && $pejantan->jenis_kelamin !== 'jantan') {
                throw ValidationException::withMessages([
                    'pejantan_id' => 'Kambing yang dipilih sebagai ayah harus berjenis kelamin jantan.',
                ]);
            }
        }

        if (!empty($validated['induk_id'])) {
            $induk = Kambing::find($validated['induk_id']);
            if ($induk && $induk->jenis_kelamin !== 'betina') {
                throw ValidationException::withMessages([
                    'induk_id' => 'Kambing yang dipilih sebagai induk harus berjenis kelamin betina.',
                ]);
            }
        }

        $updatedCount = 0;
        foreach ($validated['anak_ids'] as $anakId) {
            // Mencegah anak memilih dirinya sendiri sebagai orang tua
            if ($anakId == ($validated['pejantan_id'] ?? null) || $anakId == ($validated['induk_id'] ?? null)) {
                continue;
            }

            $anak = Kambing::find($anakId);
            if ($anak) {
                if (array_key_exists('pejantan_id', $validated) && !empty($validated['pejantan_id'])) {
                    $anak->pejantan_id = $validated['pejantan_id'];
                }
                if (array_key_exists('induk_id', $validated) && !empty($validated['induk_id'])) {
                    $anak->induk_id = $validated['induk_id'];
                }
                $anak->save();
                $updatedCount++;
            }
        }

        $redirectTarget = $request->input('redirect_to');
        if ($redirectTarget && filter_var($redirectTarget, FILTER_VALIDATE_URL)) {
            return redirect($redirectTarget)->with('success', "Relasi silsilah berhasil dihubungkan untuk {$updatedCount} ekor anak.");
        }

        return redirect()->route('relasi.index')->with('success', "Relasi silsilah berhasil dihubungkan untuk {$updatedCount} ekor anak.");
    }

    /**
     * Putuskan relasi orang tua kambing (reset induk_id / pejantan_id).
     */
    public function destroy(Kambing $kambing, Request $request)
    {
        $tipe = $request->query('tipe', 'semua');

        if ($tipe === 'pejantan') {
            $kambing->pejantan_id = null;
            $pesan = "Relasi pejantan (ayah) untuk {$kambing->nama} berhasil dihapus.";
        } elseif ($tipe === 'induk') {
            $kambing->induk_id = null;
            $pesan = "Relasi induk (ibu) untuk {$kambing->nama} berhasil dihapus.";
        } else {
            $kambing->pejantan_id = null;
            $kambing->induk_id = null;
            $pesan = "Relasi silsilah orang tua untuk {$kambing->nama} berhasil diputuskan.";
        }

        $kambing->save();

        return back()->with('success', $pesan);
    }
}
