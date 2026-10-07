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

        // Siblings (from same birth if available, or both same parents)
        if ($kambing->kelahiran_id) {
            $saudara = Kambing::where('kelahiran_id', $kambing->kelahiran_id)
                ->where('id', '!=', $kambing->id)
                ->get();
        } elseif ($kambing->induk_id && $kambing->pejantan_id) {
            $saudara = Kambing::where('induk_id', $kambing->induk_id)
                ->where('pejantan_id', $kambing->pejantan_id)
                ->where('id', '!=', $kambing->id)
                ->get();
        } else {
            $saudara = collect();
        }

        // Children and grandchildren
        $childrenQuery = $kambing->jenis_kelamin === 'betina'
            ? $kambing->anakSebagaiInduk()
            : $kambing->anakSebagaiPejantan();

        $children = $childrenQuery->with([
            'induk',
            'pejantan',
            'anakSebagaiInduk',
            'anakSebagaiPejantan',
        ])->get();

        $childrenData = $children->map(function ($child) use ($kambing) {
            $pasangan = $kambing->jenis_kelamin === 'betina' ? $child->pejantan : $child->induk;
            $cucus = $child->jenis_kelamin === 'betina'
                ? $child->anakSebagaiInduk
                : $child->anakSebagaiPejantan;

            return [
                'anak' => $this->formatNode($child, 'anak'),
                'pasangan' => $this->formatNode($pasangan, 'pasangan'),
                'cucu' => $cucus->map(fn ($c) => $this->formatNode($c, 'cucu'))->values()->all(),
            ];
        })->values()->all();

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
            'saudara' => $saudara->map(fn ($s) => $this->formatNode($s, 'saudara'))->values()->all(),
            'keturunan' => $childrenData,
        ];
    }

    private function formatNode(?Kambing $kambing, string $role = ''): ?array
    {
        if (!$kambing) {
            return null;
        }

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
        ];
    }
}
