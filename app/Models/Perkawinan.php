<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Perkawinan extends Model
{
    use HasFactory;

    protected $table = 'perkawinans';

    protected $fillable = [
        'betina_id',
        'pejantan_id',
        'tanggal_kawin',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_kawin' => 'date',
        ];
    }

    public function betina(): BelongsTo
    {
        return $this->belongsTo(Kambing::class, 'betina_id');
    }

    public function pejantan(): BelongsTo
    {
        return $this->belongsTo(Kambing::class, 'pejantan_id');
    }

    /**
     * Estimasi perkiraan tanggal kelahiran (±150 hari setelah tanggal kawin).
     */
    protected function perkiraanLahir(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->tanggal_kawin ? $this->tanggal_kawin->copy()->addDays(150) : null,
        );
    }

    public function kelahirans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Kelahiran::class, 'perkawinan_id');
    }
}
