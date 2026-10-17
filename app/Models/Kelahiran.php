<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelahiran extends Model
{
    use HasFactory, BelongsToUser;

    protected $table = 'kelahirans';

    protected $fillable = [
        'user_id',
        'perkawinan_id',
        'induk_id',
        'pejantan_id',
        'tanggal_kelahiran',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_kelahiran' => 'date',
        ];
    }

    public function perkawinan(): BelongsTo
    {
        return $this->belongsTo(Perkawinan::class, 'perkawinan_id');
    }

    public function induk(): BelongsTo
    {
        return $this->belongsTo(Kambing::class, 'induk_id');
    }

    public function pejantan(): BelongsTo
    {
        return $this->belongsTo(Kambing::class, 'pejantan_id');
    }

    public function anaks(): HasMany
    {
        return $this->hasMany(Kambing::class, 'kelahiran_id');
    }
}
