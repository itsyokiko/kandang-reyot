<?php

namespace App\Http\Controllers;

use App\Models\Kambing;

class KesiapanKawinController extends Controller
{
    public function index()
    {
        $betinas = Kambing::where('jenis_kelamin', 'betina')
            ->where('status', 'hidup')
            ->with(['kelahiranSebagaiInduk'])
            ->get();

        $siapDikawinkan = collect();
        $masaPemulihan = collect();
        $belumAdaRiwayat = collect();

        foreach ($betinas as $kambing) {
            $info = $kambing->kesiapan_kawin_info;
            if (!$info) {
                continue;
            }

            $data = array_merge(['kambing' => $kambing], $info);

            if ($info['status'] === 'siap') {
                $siapDikawinkan->push($data);
            } elseif ($info['status'] === 'pemulihan') {
                $masaPemulihan->push($data);
            } else {
                $belumAdaRiwayat->push($data);
            }
        }

        $intervalHari = config('kandang.masa_pemulihan_hari', 60);

        return view('kesiapan_kawin.index', compact(
            'siapDikawinkan',
            'masaPemulihan',
            'belumAdaRiwayat',
            'intervalHari'
        ));
    }
}
