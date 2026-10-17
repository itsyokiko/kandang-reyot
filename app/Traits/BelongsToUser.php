<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToUser
{
    /**
     * Boot the trait to automatically assign user_id and scope queries to the authenticated user.
     */
    protected static function bootBelongsToUser(): void
    {
        // 1. Otomatis isi user_id dari user yang sedang login saat create
        static::creating(function ($model) {
            if (empty($model->user_id) && Auth::check()) {
                $model->user_id = Auth::id();
            }
        });

        // 2. Global Scope: Isolasi data per akun user yang sedang login
        static::addGlobalScope('user_isolation', function (Builder $builder) {
            if (Auth::check()) {
                $builder->where($builder->getModel()->getTable() . '.user_id', Auth::id());
            }
        });
    }

    /**
     * Relasi ke model User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
