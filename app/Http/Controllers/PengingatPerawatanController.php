<?php

namespace App\Http\Controllers;

use App\Models\Kambing;
use App\Models\PengingatPerawatan;
use Illuminate\Http\Request;

class PengingatPerawatanController extends Controller
{
    public function index()
    {
        $pengingatPerawatans = PengingatPerawatan::with('kambing')
            ->orderByRaw("CASE WHEN status = 'belum_diberikan' THEN 0 ELSE 1 END")
            ->latest('tanggal')
            ->paginate(15);

        $kambings = Kambing::where('status', '!=', 'mati')->orderBy('nama')->get();

        return view('pengingat_perawatan.index', compact('pengingatPerawatans', 'kambings'));
    }

    public function create()
    {
        $kambings = Kambing::where('status', '!=', 'mati')->orderBy('nama')->get();
        $selectedKambingId = request('kambing_id');

        return view('pengingat_perawatan.create', compact('kambings', 'selectedKambingId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kambing_id' => 'required|exists:kambings,id',
            'jenis_perawatan' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'catatan' => 'nullable|string',
        ], [
            'kambing_id.required' => 'Kambing wajib dipilih.',
            'kambing_id.exists' => 'Kambing yang dipilih tidak valid.',
            'jenis_perawatan.required' => 'Jenis perawatan wajib diisi.',
            'tanggal.required' => 'Tanggal jadwal perawatan wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
        ]);

        $validated['status'] = 'belum_diberikan';

        PengingatPerawatan::create($validated);

        return redirect()->route('pengingat-perawatan.index')
            ->with('success', 'Pengingat perawatan berhasil dibuat.');
    }

    public function selesai(Request $request, PengingatPerawatan $pengingatPerawatan)
    {
        $tanggalJadwalStr = $pengingatPerawatan->tanggal ? $pengingatPerawatan->tanggal->format('Y-m-d') : '';

        $validated = $request->validate([
            'tanggal_pemberian' => 'nullable|date|after_or_equal:' . $tanggalJadwalStr,
        ], [
            'tanggal_pemberian.after_or_equal' => 'Tanggal pemberian tidak boleh sebelum tanggal jadwal perawatan.',
        ]);

        $tanggalPemberian = $validated['tanggal_pemberian'] ?? now()->toDateString();

        $pengingatPerawatan->update([
            'status' => 'sudah_diberikan',
            'tanggal_pemberian' => $tanggalPemberian,
        ]);

        if (trim(strtolower($pengingatPerawatan->jenis_perawatan)) === 'semprot kandang') {
            $interval = (int) config('kandang.interval_semprot_kandang_hari', 14);
            $nextDate = \Carbon\Carbon::parse($tanggalPemberian)->startOfDay()->addDays($interval)->toDateString();

            PengingatPerawatan::create([
                'kambing_id' => $pengingatPerawatan->kambing_id,
                'jenis_perawatan' => 'Semprot Kandang',
                'tanggal' => $nextDate,
                'status' => 'belum_diberikan',
                'catatan' => 'Jadwal otomatis berikutnya (interval ' . $interval . ' hari)',
            ]);
        }

        return redirect()->back()
            ->with('success', 'Pengingat perawatan berhasil dikonfirmasi sudah diberikan.');
    }

    public function destroy(PengingatPerawatan $pengingatPerawatan)
    {
        $pengingatPerawatan->delete();

        return redirect()->back()
            ->with('success', 'Pengingat perawatan berhasil dihapus.');
    }
}
