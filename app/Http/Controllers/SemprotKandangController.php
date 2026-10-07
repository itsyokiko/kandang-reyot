<?php

namespace App\Http\Controllers;

use App\Models\Kambing;
use App\Models\PengingatPerawatan;
use Illuminate\Http\Request;

class SemprotKandangController extends Controller
{
    public function index()
    {
        $jadwalAktif = PengingatPerawatan::where('jenis_perawatan', 'Semprot Kandang')
            ->where('status', 'belum_diberikan')
            ->orderBy('tanggal')
            ->first();

        $riwayatSemprot = PengingatPerawatan::where('jenis_perawatan', 'Semprot Kandang')
            ->where('status', 'sudah_diberikan')
            ->latest('tanggal_pemberian')
            ->get();

        $intervalDefault = (int) config('kandang.interval_semprot_kandang_hari', 14);

        return view('semprot_kandang.index', compact('jadwalAktif', 'riwayatSemprot', 'intervalDefault'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'interval_hari' => 'nullable|integer|min:1',
            'catatan' => 'nullable|string',
        ], [
            'tanggal.required' => 'Tanggal jadwal semprot kandang wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
        ]);

        // Semprot Kandang adalah perawatan fasilitas kandang, tidak terkait kambing individual.
        // Di backend, kita kaitkan ke data kambing pertama yang tersedia untuk memenuhi relasi database.
        $kambing = Kambing::first();
        if (!$kambing) {
            $kambing = Kambing::create([
                'nama' => 'Fasilitas Kandang',
                'jenis_kelamin' => 'jantan',
                'status' => 'hidup',
            ]);
        }

        $interval = $validated['interval_hari'] ?? (int) config('kandang.interval_semprot_kandang_hari', 14);
        $catatanText = $validated['catatan']
            ? trim($validated['catatan']) . " (Interval: {$interval} hari)"
            : "Jadwal disinfeksi fasilitas kandang (Interval: {$interval} hari)";

        PengingatPerawatan::create([
            'kambing_id' => $kambing->id,
            'jenis_perawatan' => 'Semprot Kandang',
            'tanggal' => $validated['tanggal'],
            'status' => 'belum_diberikan',
            'catatan' => $catatanText,
        ]);

        return redirect()->route('semprot-kandang.index')
            ->with('success', 'Jadwal semprot kandang berhasil ditetapkan.');
    }
}
