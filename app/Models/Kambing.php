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
}
