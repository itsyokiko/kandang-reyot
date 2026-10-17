<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengingatPerawatan extends Model
{
    use HasFactory, BelongsToUser;

    protected $table = 'pengingat_perawatans';

    protected $fillable = [
        'user_id',
        'kambing_id',
        'jenis_perawatan',
        'tanggal',
        'tanggal_pemberian',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'tanggal_pemberian' => 'date',
        ];
    }

    public function kambing(): BelongsTo
    {
        return $this->belongsTo(Kambing::class, 'kambing_id');
    }

    public function setJenisPerawatanAttribute($value): void
    {
        $this->attributes['jenis_perawatan'] = Kambing::normalizeTitle($value);
    }
}
