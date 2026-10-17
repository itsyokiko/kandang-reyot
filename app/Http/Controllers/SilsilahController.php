<?php

namespace App\Http\Controllers;

use App\Models\Kambing;
use Illuminate\Http\Request;

class SilsilahController extends Controller
{
    public function index(Request $request, ?Kambing $kambing = null)
    {
        $allKambings = Kambing::orderBy('nama')->get();

        if (!$kambing && $allKambings->isNotEmpty()) {
            $kambing = $allKambings->first();
        }

        $silsilahData = $kambing ? $this->getTreeData($kambing) : null;

        return view('silsilah.index', compact('allKambings', 'kambing', 'silsilahData'));
    }

    public function data(Kambing $kambing)
    {
        return response()->json($this->getTreeData($kambing));
    }

    private function getTreeData(Kambing $kambing): array
    {
        // Load parents and their parents (grandparents)
        $kambing->load([
            'induk.induk',
            'induk.pejantan',
            'pejantan.induk',
            'pejantan.pejantan',
        ]);

        // Siblings in Big Family Tree:
        // 1. Saudara Kandung (Ibu & Bapak sama)
        // 2. Saudara Se-Induk (Ibu sama, Bapak beda / luar)
        // 3. Saudara Se-Bapak (Bapak sama, Ibu beda)
        $saudaraKandung = collect();
        $saudaraSeinduk = collect();
        $saudaraSebapak = collect();

        if ($kambing->induk_id && $kambing->pejantan_id) {
            $saudaraKandung = Kambing::where('induk_id', $kambing->induk_id)
                ->where('pejantan_id', $kambing->pejantan_id)
                ->where('id', '!=', $kambing->id)
                ->get();
        }

        if ($kambing->induk_id) {
            $query = Kambing::where('induk_id', $kambing->induk_id)
                ->where('id', '!=', $kambing->id);
            if ($kambing->pejantan_id) {
                $query->where(function ($q) use ($kambing) {
                    $q->whereNull('pejantan_id')->orWhere('pejantan_id', '!=', $kambing->pejantan_id);
                });
            }
            $saudaraSeinduk = $query->get();
        }

        if ($kambing->pejantan_id) {
            $query = Kambing::where('pejantan_id', $kambing->pejantan_id)
                ->where('id', '!=', $kambing->id);
            if ($kambing->induk_id) {
                $query->where(function ($q) use ($kambing) {
                    $q->whereNull('induk_id')->orWhere('induk_id', '!=', $kambing->induk_id);
                });
            }
            $saudaraSebapak = $query->get();
        }

        $allSaudara = collect();
        foreach ($saudaraKandung as $s) {
            $node = $this->formatNode($s, 'saudara_kandung');
            $node['tipe_saudara'] = 'Saudara Kandung';
            $allSaudara->push($node);
        }
        foreach ($saudaraSeinduk as $s) {
            $node = $this->formatNode($s, 'saudara_seinduk');
            $node['tipe_saudara'] = 'Se-Induk (Beda Bapak)';
            $allSaudara->push($node);
        }
        foreach ($saudaraSebapak as $s) {
            $node = $this->formatNode($s, 'saudara_sebapak');
            $node['tipe_saudara'] = 'Se-Bapak (Beda Induk)';
            $allSaudara->push($node);
        }

        // Children and grandchildren (Big Family Unions: group by partner)
        $childrenQuery = $kambing->jenis_kelamin === 'betina'
            ? $kambing->anakSebagaiInduk()
            : $kambing->anakSebagaiPejantan();

        $children = $childrenQuery->with([
            'induk',
            'pejantan',
            'anakSebagaiInduk',
            'anakSebagaiPejantan',
        ])->get();

        $unions = [];
        $flatChildren = [];

        foreach ($children as $child) {
            $pasangan = $kambing->jenis_kelamin === 'betina' ? $child->pejantan : $child->induk;
            $partnerKey = $pasangan ? ('kambing_' . $pasangan->id) : 'tanpa_pasangan';

            if (!isset($unions[$partnerKey])) {
                $unions[$partnerKey] = [
                    'pasangan' => $pasangan ? $this->formatNode($pasangan, 'pasangan') : null,
                    'label_pasangan' => $pasangan ? $pasangan->nama : 'Tanpa Pasangan Tercatat',
                    'is_luar' => $pasangan && str_contains(strtolower($pasangan->catatan ?? ''), 'luar'),
                    'anak_list' => [],
                ];
            }

            $cucus = $child->jenis_kelamin === 'betina'
                ? $child->anakSebagaiInduk
                : $child->anakSebagaiPejantan;

            $childItem = [
                'anak' => $this->formatNode($child, 'anak'),
                'pasangan' => $this->formatNode($pasangan, 'pasangan'),
                'cucu' => $cucus->map(fn ($c) => $this->formatNode($c, 'cucu'))->values()->all(),
            ];

            $unions[$partnerKey]['anak_list'][] = $childItem;
            $flatChildren[] = $childItem;
        }

        return [
            'pusat' => $this->formatNode($kambing, 'pusat'),
            'orang_tua' => [
                'ayah' => $this->formatNode($kambing->pejantan, 'ayah'),
                'ibu' => $this->formatNode($kambing->induk, 'ibu'),
            ],
            'kakek_nenek' => [
                'kakek_dari_ayah' => $this->formatNode($kambing->pejantan?->pejantan, 'kakek_ayah'),
                'nenek_dari_ayah' => $this->formatNode($kambing->pejantan?->induk, 'nenek_ayah'),
                'kakek_dari_ibu' => $this->formatNode($kambing->induk?->pejantan, 'kakek_ibu'),
                'nenek_dari_ibu' => $this->formatNode($kambing->induk?->induk, 'nenek_ibu'),
            ],
            'saudara' => $allSaudara->values()->all(),
            'keturunan' => $flatChildren,
            'unions' => array_values($unions),
        ];
    }

    private function formatNode(?Kambing $kambing, string $role = ''): ?array
    {
        if (!$kambing) {
            return null;
        }

        $isLuar = str_contains(strtolower($kambing->catatan ?? ''), 'luar');

        return [
            'id' => $kambing->id,
            'nama' => $kambing->nama,
            'jenis_kelamin' => $kambing->jenis_kelamin,
            'ras' => $kambing->ras ?? 'Tidak dicatat',
            'tanggal_lahir' => $kambing->tanggal_lahir ? $kambing->tanggal_lahir->format('d M Y') : 'Tidak dicatat',
            'status' => $kambing->status,
            'foto_url' => $kambing->foto ? asset('storage/' . $kambing->foto) : null,
            'detail_url' => route('kambings.show', $kambing),
            'role' => $role,
            'is_luar' => $isLuar,
            'catatan' => $kambing->catatan,
        ];
    }
}
