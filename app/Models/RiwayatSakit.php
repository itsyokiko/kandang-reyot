<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatSakit extends Model
{
    use HasFactory;

    protected $table = 'riwayat_sakits';

    protected $fillable = [
        'kambing_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public function kambing(): BelongsTo
    {
        return $this->belongsTo(Kambing::class, 'kambing_id');
    }

    public function getDurasiHariAttribute(): int
    {
        if (!$this->tanggal_mulai) {
            return 0;
        }

        $start = Carbon::parse($this->tanggal_mulai)->startOfDay();

        if ($this->tanggal_selesai) {
            $end = Carbon::parse($this->tanggal_selesai)->startOfDay();
        } else {
            $end = Carbon::now()->startOfDay();
        }

        return (int) $start->diffInDays($end);
    }
}
