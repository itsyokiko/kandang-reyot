<?php

namespace App\Http\Controllers;

use App\Models\Kambing;
use App\Models\PengingatPerawatan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PengingatPerawatanController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'jadwal'); // 'jadwal' (akan datang) atau 'riwayat' (sudah dilakukan)

        $query = PengingatPerawatan::with('kambing');

        if ($tab === 'riwayat') {
            $query->where('status', 'sudah_diberikan')->latest('tanggal_pemberian');
        } else {
            $query->where('status', 'belum_diberikan')->orderBy('tanggal', 'asc');
        }

        if ($request->filled('kambing_id')) {
            $query->where('kambing_id', $request->input('kambing_id'));
        }

        if ($request->filled('jenis_perawatan')) {
            $query->where('jenis_perawatan', $request->input('jenis_perawatan'));
        }

        $pengingatPerawatans = $query->paginate(15)->withQueryString();

        $kambings = Kambing::where('status', 'hidup')->orderBy('nama')->get();

        $countJadwal = PengingatPerawatan::where('status', 'belum_diberikan')->count();
        $countRiwayat = PengingatPerawatan::where('status', 'sudah_diberikan')->count();

        return view('pengingat_perawatan.index', compact('pengingatPerawatans', 'kambings', 'tab', 'countJadwal', 'countRiwayat'));
    }

    public function create()
    {
        $kambings = Kambing::where('status', 'hidup')->orderBy('nama')->get();
        $selectedKambingId = request('kambing_id');

        return view('pengingat_perawatan.create', compact('kambings', 'selectedKambingId'));
    }

    public function store(Request $request)
    {
        $isRiwayat = $request->input('tipe_input') === 'riwayat';

        if ($isRiwayat) {
            $validated = $request->validate([
                'kambing_id' => [
                    'required',
                    'exists:kambings,id',
                    function ($attribute, $value, $fail) {
                        $k = Kambing::find($value);
                        if ($k && $k->status !== 'hidup') {
                            $fail("Kambing yang berstatus {$k->status} tidak dapat dijadwalkan/diberi perawatan.");
                        }
                    },
                ],
                'jenis_perawatan' => 'required|string|max:255',
                'tanggal_pemberian' => 'required|date|before_or_equal:today',
                'catatan' => 'nullable|string',
                'ulang_hari' => 'nullable|integer|min:1',
                'tanggal_berikutnya' => 'nullable|date|after:tanggal_pemberian',
            ], [
                'kambing_id.required' => 'Kambing wajib dipilih.',
                'jenis_perawatan.required' => 'Jenis perawatan wajib diisi.',
                'tanggal_pemberian.required' => 'Tanggal pelaksanaan perawatan wajib diisi.',
                'tanggal_pemberian.before_or_equal' => 'Tanggal riwayat pelaksanaan tidak boleh di masa depan.',
                'tanggal_berikutnya.after' => 'Jadwal berikutnya harus setelah tanggal pelaksanaan.',
            ]);

            PengingatPerawatan::create([
                'kambing_id' => $validated['kambing_id'],
                'jenis_perawatan' => $validated['jenis_perawatan'],
                'tanggal' => $validated['tanggal_pemberian'],
                'tanggal_pemberian' => $validated['tanggal_pemberian'],
                'status' => 'sudah_diberikan',
                'catatan' => $validated['catatan'] ?? null,
            ]);

            $nextDate = null;
            if (!empty($validated['tanggal_berikutnya'])) {
                $nextDate = $validated['tanggal_berikutnya'];
            } elseif (!empty($validated['ulang_hari'])) {
                $nextDate = Carbon::parse($validated['tanggal_pemberian'])->addDays((int) $validated['ulang_hari'])->toDateString();
            }

            if ($nextDate) {
                PengingatPerawatan::create([
                    'kambing_id' => $validated['kambing_id'],
                    'jenis_perawatan' => $validated['jenis_perawatan'],
                    'tanggal' => $nextDate,
                    'status' => 'belum_diberikan',
                    'catatan' => 'Jadwal berkala lanjutan dari tanggal ' . Carbon::parse($validated['tanggal_pemberian'])->format('d/m/Y'),
                ]);
            }

            return redirect()->route('pengingat-perawatan.index', ['tab' => 'riwayat'])
                ->with('success', 'Riwayat perawatan berhasil dicatat' . ($nextDate ? ' dan jadwal berikutnya otomatis dibuat.' : '.'));
        }

        // Mode Jadwal Biasa
        $validated = $request->validate([
            'kambing_id' => [
                'required',
                'exists:kambings,id',
                function ($attribute, $value, $fail) {
                    $k = Kambing::find($value);
                    if ($k && $k->status !== 'hidup') {
                        $fail("Kambing yang berstatus {$k->status} tidak dapat dijadwalkan perawatan.");
                    }
                },
            ],
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
        $validated = $request->validate([
            'tanggal_pemberian' => 'nullable|date',
            'ulang_hari' => 'nullable|integer|min:1',
            'tanggal_berikutnya' => 'nullable|date',
        ]);

        $tanggalPemberian = $validated['tanggal_pemberian'] ?? now()->toDateString();

        $pengingatPerawatan->update([
            'status' => 'sudah_diberikan',
            'tanggal_pemberian' => $tanggalPemberian,
        ]);

        $nextDate = null;
        if (!empty($validated['tanggal_berikutnya'])) {
            $nextDate = $validated['tanggal_berikutnya'];
        } elseif (!empty($validated['ulang_hari'])) {
            $nextDate = Carbon::parse($tanggalPemberian)->addDays((int) $validated['ulang_hari'])->toDateString();
        } elseif (trim(strtolower($pengingatPerawatan->jenis_perawatan)) === 'semprot kandang') {
            $interval = (int) config('kandang.interval_semprot_kandang_hari', 14);
            $nextDate = Carbon::parse($tanggalPemberian)->startOfDay()->addDays($interval)->toDateString();
        }

        if ($nextDate) {
            PengingatPerawatan::create([
                'kambing_id' => $pengingatPerawatan->kambing_id,
                'jenis_perawatan' => $pengingatPerawatan->jenis_perawatan,
                'tanggal' => $nextDate,
                'status' => 'belum_diberikan',
                'catatan' => 'Jadwal otomatis berikutnya lanjutan tanggal ' . Carbon::parse($tanggalPemberian)->format('d/m/Y'),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Pengingat perawatan berhasil dikonfirmasi sudah diberikan' . ($nextDate ? ' dan jadwal berikutnya otomatis dibuat.' : '.'));
    }

    public function destroy(PengingatPerawatan $pengingatPerawatan)
    {
        $pengingatPerawatan->delete();

        return redirect()->back()
            ->with('success', 'Pengingat perawatan berhasil dihapus.');
    }
}
