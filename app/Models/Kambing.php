<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kambing extends Model
{
    use HasFactory;

    protected $table = 'kambings';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'jenis_kelamin',
        'ras',
        'tanggal_lahir',
        'foto',
        'status',
        'catatan',
        'kelahiran_id',
        'induk_id',
        'pejantan_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    public function perkawinanBetina(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Perkawinan::class, 'betina_id');
    }

    public function perkawinanPejantan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Perkawinan::class, 'pejantan_id');
    }

    public function kelahiran(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Kelahiran::class, 'kelahiran_id');
    }

    public function induk(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Kambing::class, 'induk_id');
    }

    public function ibu(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->induk();
    }

    public function pejantan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Kambing::class, 'pejantan_id');
    }

    public function ayah(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->pejantan();
    }

    public function kelahiranSebagaiInduk(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Kelahiran::class, 'induk_id');
    }

    public function kelahiranSebagaiPejantan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Kelahiran::class, 'pejantan_id');
    }

    public function anakSebagaiInduk(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Kambing::class, 'induk_id');
    }

    public function anakSebagaiPejantan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Kambing::class, 'pejantan_id');
    }

    public function riwayatSakit(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RiwayatSakit::class, 'kambing_id')->latest('tanggal_mulai');
    }

    public function pengingatPerawatan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PengingatPerawatan::class, 'kambing_id')->latest('tanggal');
    }

    public function getKesiapanKawinInfoAttribute(): ?array
    {
        if ($this->jenis_kelamin !== 'betina' || $this->status !== 'hidup') {
            return null;
        }

        $kelahiranTerakhir = $this->kelahiranSebagaiInduk()
            ->latest('tanggal_kelahiran')
            ->first();

        if (!$kelahiranTerakhir) {
            return [
                'status' => 'belum_ada_riwayat',
                'label' => 'Belum Ada Riwayat Kelahiran',
                'keterangan' => 'Belum pernah melahirkan di kandang',
            ];
        }

        $intervalHari = (int) config('kandang.masa_pemulihan_hari', 60);
        $tanggalLahir = \Carbon\Carbon::parse($kelahiranTerakhir->tanggal_kelahiran)->startOfDay();
        $tanggalMulaiSiap = $tanggalLahir->copy()->addDays($intervalHari);
        $today = \Carbon\Carbon::now()->startOfDay();

        if ($today->greaterThanOrEqualTo($tanggalMulaiSiap)) {
            return [
                'status' => 'siap',
                'label' => 'Siap Dikawinkan',
                'tanggal_kelahiran_terakhir' => $tanggalLahir,
                'tanggal_mulai_siap' => $tanggalMulaiSiap,
                'lama_sejak_melahirkan' => (int) $tanggalLahir->diffInDays($today),
            ];
        }

        return [
            'status' => 'pemulihan',
            'label' => 'Masa Pemulihan',
            'tanggal_kelahiran_terakhir' => $tanggalLahir,
            'estimasi_tanggal_siap' => $tanggalMulaiSiap,
            'sisa_hari' => (int) $today->diffInDays($tanggalMulaiSiap),
        ];
    }

    /**
     * Memeriksa apakah terjadi perkawinan sedarah (inbreeding) antara betina dan pejantan.
     *
     * @param int|Kambing|null $betina
     * @param int|Kambing|null $pejantan
     * @return array|null Null jika aman, array dengan detail peringatan jika terdeteksi inbreeding.
     */
    public static function checkInbreeding($betina, $pejantan): ?array
    {
        $betina = $betina instanceof Kambing ? $betina : Kambing::find($betina);
        $pejantan = $pejantan instanceof Kambing ? $pejantan : Kambing::find($pejantan);

        if (!$betina || !$pejantan) {
            return null;
        }

        // 1. Hubungan Induk & Anak: Pejantan adalah anak dari Betina
        if ($pejantan->induk_id && $pejantan->induk_id == $betina->id) {
            return [
                'is_inbreeding' => true,
                'type' => 'anak_induk',
                'level' => 'bahaya',
                'title' => 'Perkawinan Sedarah: Induk & Anak Kandung',
                'message' => "Pejantan '{$pejantan->nama}' merupakan anak kandung dari Betina '{$betina->nama}'. Perkawinan ini berisiko tinggi cacat genetik.",
            ];
        }

        // 2. Hubungan Anak & Pejantan: Betina adalah anak dari Pejantan
        if ($betina->pejantan_id && $betina->pejantan_id == $pejantan->id) {
            return [
                'is_inbreeding' => true,
                'type' => 'anak_pejantan',
                'level' => 'bahaya',
                'title' => 'Perkawinan Sedarah: Bapak & Anak Kandung',
                'message' => "Betina '{$betina->nama}' merupakan anak kandung dari Pejantan '{$pejantan->nama}'. Perkawinan ini berisiko tinggi cacat genetik.",
            ];
        }

        // 3. Saudara Kandung (Induk & Pejantan sama)
        if (
            $betina->induk_id && $pejantan->induk_id && $betina->induk_id == $pejantan->induk_id &&
            $betina->pejantan_id && $pejantan->pejantan_id && $betina->pejantan_id == $pejantan->pejantan_id
        ) {
            return [
                'is_inbreeding' => true,
                'type' => 'saudara_kandung',
                'level' => 'bahaya',
                'title' => 'Perkawinan Sedarah: Saudara Kandung',
                'message' => "'{$betina->nama}' dan '{$pejantan->nama}' memiliki bapak dan induk yang sama persis (saudara kandung).",
            ];
        }

        // 4. Saudara Seinduk (Ibu sama)
        if ($betina->induk_id && $pejantan->induk_id && $betina->induk_id == $pejantan->induk_id) {
            $indukNama = $betina->relationLoaded('induk') && $betina->induk ? $betina->induk->nama : null;
            if (!$indukNama) {
                try {
                    $indukNama = self::where('id', $betina->induk_id)->value('nama');
                } catch (\Throwable $e) {
                    $indukNama = null;
                }
            }
            $indukDesc = $indukNama ? " ({$indukNama})" : "";
            return [
                'is_inbreeding' => true,
                'type' => 'saudara_seinduk',
                'level' => 'peringatan',
                'title' => 'Perkawinan Sedarah: Saudara Seinduk',
                'message' => "'{$betina->nama}' dan '{$pejantan->nama}' berasal dari induk yang sama{$indukDesc}.",
            ];
        }

        // 5. Saudara Sebapak (Bapak sama)
        if ($betina->pejantan_id && $pejantan->pejantan_id && $betina->pejantan_id == $pejantan->pejantan_id) {
            $pejantanNama = $betina->relationLoaded('pejantan') && $betina->pejantan ? $betina->pejantan->nama : null;
            if (!$pejantanNama) {
                try {
                    $pejantanNama = self::where('id', $betina->pejantan_id)->value('nama');
                } catch (\Throwable $e) {
                    $pejantanNama = null;
                }
            }
            $pejantanDesc = $pejantanNama ? " ({$pejantanNama})" : "";
            return [
                'is_inbreeding' => true,
                'type' => 'saudara_sebapak',
                'level' => 'peringatan',
                'title' => 'Perkawinan Sedarah: Saudara Sebapak',
                'message' => "'{$betina->nama}' dan '{$pejantan->nama}' memiliki pejantan (bapak) yang sama{$pejantanDesc}.",
            ];
        }

        return null;
    }
}
