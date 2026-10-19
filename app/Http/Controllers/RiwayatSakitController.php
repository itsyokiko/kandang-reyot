<?php

namespace App\Http\Controllers;

use App\Models\Kambing;
use App\Models\RiwayatSakit;
use Illuminate\Http\Request;

class RiwayatSakitController extends Controller
{
    public function index()
    {
        $riwayatSakits = RiwayatSakit::with('kambing')
            ->orderByRaw("CASE WHEN status = 'sedang_sakit' THEN 0 ELSE 1 END")
            ->latest('tanggal_mulai')
            ->paginate(15);

        $kambings = Kambing::where('status', 'hidup')->orderBy('nama')->get();

        return view('riwayat_sakit.index', compact('riwayatSakits', 'kambings'));
    }

    public function create()
    {
        $kambings = Kambing::where('status', 'hidup')->orderBy('nama')->get();
        $selectedKambingId = request('kambing_id');

        return view('riwayat_sakit.create', compact('kambings', 'selectedKambingId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kambing_id' => [
                'required',
                'exists:kambings,id',
                function ($attribute, $value, $fail) use ($request) {
                    $k = Kambing::find($value);
                    if ($k && $k->status !== 'hidup') {
                        $fail("Kambing yang berstatus {$k->status} tidak dapat dicatat sakit.");
                        return;
                    }
                    if ($k && RiwayatSakit::where('kambing_id', $value)->where('status', 'sedang_sakit')->exists()) {
                        $fail("Kambing {$k->nama} saat ini sedang dalam status sakit aktif. Harap konfirmasi kesembuhan riwayat sebelumnya terlebih dahulu.");
                        return;
                    }
                    if ($k && $k->tanggal_lahir && $request->filled('tanggal_mulai')) {
                        if ($request->input('tanggal_mulai') < $k->tanggal_lahir->toDateString()) {
                            $fail("Tanggal mulai sakit tidak boleh sebelum tanggal lahir kambing ({$k->tanggal_lahir->format('d/m/Y')}).");
                        }
                    }
                },
            ],
            'tanggal_mulai' => 'required|date|before_or_equal:today',
            'catatan' => 'nullable|string',
        ], [
            'kambing_id.required' => 'Kambing wajib dipilih.',
            'kambing_id.exists' => 'Kambing yang dipilih tidak valid.',
            'tanggal_mulai.required' => 'Tanggal mulai sakit wajib diisi.',
            'tanggal_mulai.date' => 'Format tanggal mulai sakit tidak valid.',
            'tanggal_mulai.before_or_equal' => 'Tanggal mulai sakit tidak boleh di masa depan.',
        ]);

        $validated['status'] = 'sedang_sakit';

        RiwayatSakit::create($validated);

        return redirect()->route('riwayat-sakit.index')
            ->with('success', 'Catatan kambing sakit berhasil disimpan.');
    }

    public function konfirmasi(Request $request, RiwayatSakit $riwayatSakit)
    {
        $tanggalMulaiStr = $riwayatSakit->tanggal_mulai ? $riwayatSakit->tanggal_mulai->format('Y-m-d') : '';

        $validated = $request->validate([
            'status' => 'required|in:sembuh,mati',
            'tanggal_selesai' => 'required|date|before_or_equal:today|after_or_equal:' . $tanggalMulaiStr,
        ], [
            'status.required' => 'Status hasil konfirmasi wajib dipilih.',
            'status.in' => 'Status hasil harus sembuh atau mati.',
            'tanggal_selesai.required' => 'Tanggal selesai / kematian wajib diisi.',
            'tanggal_selesai.before_or_equal' => 'Tanggal selesai tidak boleh di masa depan.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai sakit.',
        ]);

        if ($validated['status'] === 'mati') {
            $riwayatSakit->kambing->update(['status' => 'mati']);
            $riwayatSakit->kambing->pengingatPerawatan()->where('status', 'belum_diberikan')->delete();
        }

        $riwayatSakit->update([
            'status' => $validated['status'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
        ]);

        return redirect()->back()
            ->with('success', 'Status riwayat sakit kambing berhasil dikonfirmasi.');
    }

    public function destroy(RiwayatSakit $riwayatSakit)
    {
        $riwayatSakit->delete();

        return redirect()->back()
            ->with('success', 'Catatan riwayat sakit berhasil dihapus.');
    }
}
