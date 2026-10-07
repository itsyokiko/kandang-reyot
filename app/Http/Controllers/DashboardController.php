<?php

namespace App\Http\Controllers;

use App\Models\Kambing;
use App\Models\Kelahiran;
use App\Models\PengingatPerawatan;
use App\Models\Perkawinan;
use App\Models\RiwayatSakit;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Statistik Kambing
        $totalKambing = Kambing::count();
        $totalJantan = Kambing::where('jenis_kelamin', 'jantan')->count();
        $totalBetina = Kambing::where('jenis_kelamin', 'betina')->count();
        $totalHidup = Kambing::where('status', 'hidup')->count();
        $totalDijual = Kambing::where('status', 'dijual')->count();
        $totalMati = Kambing::where('status', 'mati')->count();

        // 2. Statistik Breeding
        $totalPerkawinan = Perkawinan::count();
        $perkawinanMenunggu = Perkawinan::where('status', 'menunggu')->count();
        $perkawinanBerhasil = Perkawinan::where('status', 'berhasil')->count();
        $perkawinanTidakBerhasil = Perkawinan::where('status', 'tidak_berhasil')->count();
        $totalKelahiran = Kelahiran::count();
        $totalAnak = Kambing::whereNotNull('kelahiran_id')->count();

        // 3. Kelahiran Terbaru
        $kelahiranTerbaru = Kelahiran::with(['induk', 'pejantan', 'anaks'])
            ->latest('tanggal_kelahiran')
            ->take(5)
            ->get();

        // 4. Perkawinan Terbaru
        $perkawinanTerbaru = Perkawinan::with(['betina', 'pejantan'])
            ->latest('tanggal_kawin')
            ->take(5)
            ->get();

        // 5. Statistik Ras (Dinamis dari Database)
        $statistikRas = Kambing::select(
            DB::raw("CASE WHEN ras IS NULL OR TRIM(ras) = '' THEN 'Tanpa ras' ELSE ras END as nama_ras"),
            DB::raw("COUNT(*) as total")
        )
        ->groupBy('nama_ras')
        ->orderByDesc('total')
        ->get();

        // 6. Kambing Sedang Sakit
        $kambingSedangSakit = RiwayatSakit::with('kambing')
            ->where('status', 'sedang_sakit')
            ->latest('tanggal_mulai')
            ->get();

        // 7. Pengingat Perawatan (Aktif: belum_diberikan)
        $today = now()->startOfDay();
        $tomorrow = now()->addDay()->startOfDay();

        $pengingatHariIni = PengingatPerawatan::with('kambing')
            ->where('status', 'belum_diberikan')
            ->whereDate('tanggal', $today)
            ->get();

        $pengingatBesok = PengingatPerawatan::with('kambing')
            ->where('status', 'belum_diberikan')
            ->whereDate('tanggal', $tomorrow)
            ->get();

        $pengingatTerlambat = PengingatPerawatan::with('kambing')
            ->where('status', 'belum_diberikan')
            ->whereDate('tanggal', '<', $today)
            ->get();

        // 8. Kesiapan Kawin Betina
        $betinas = Kambing::where('jenis_kelamin', 'betina')
            ->where('status', 'hidup')
            ->with(['kelahiranSebagaiInduk'])
            ->get();

        $betinaSiapKawinCount = 0;
        $betinaMasaPemulihanCount = 0;

        foreach ($betinas as $b) {
            $info = $b->kesiapan_kawin_info;
            if ($info && $info['status'] === 'siap') {
                $betinaSiapKawinCount++;
            } elseif ($info && $info['status'] === 'pemulihan') {
                $betinaMasaPemulihanCount++;
            }
        }

        return view('dashboard.index', compact(
            'totalKambing',
            'totalJantan',
            'totalBetina',
            'totalHidup',
            'totalDijual',
            'totalMati',
            'totalPerkawinan',
            'perkawinanMenunggu',
            'perkawinanBerhasil',
            'perkawinanTidakBerhasil',
            'totalKelahiran',
            'totalAnak',
            'kelahiranTerbaru',
            'perkawinanTerbaru',
            'statistikRas',
            'kambingSedangSakit',
            'pengingatHariIni',
            'pengingatBesok',
            'pengingatTerlambat',
            'betinaSiapKawinCount',
            'betinaMasaPemulihanCount'
        ));
    }
}
