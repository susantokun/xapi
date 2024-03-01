<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasUserTrait
{
    protected static function bootHasUserTrait()
    {
        if (!app()->runningInConsole() && auth()->check()) {
            static::creating(function ($model) {
                $model->user_id = auth()->id();
            });

            static::updating(function ($model) {
                $model->user_id = auth()->id();
            });
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
