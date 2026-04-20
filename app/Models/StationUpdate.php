<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StationUpdate extends Model
{
    protected $fillable = [
        'station_id',
        'user_id',
        'petrol',
        'petrol_normal',
        'petrol_improved',
        'petrol_super',
        'diesel',
        'kerosene',
        'gas',
        'congestion',
        'is_admin_update',
        'is_verified',
        'confirmation_count',
        'dispute_count',
        'ip_address',
        'device_id',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_admin_update' => 'boolean',
            'is_verified'     => 'boolean',
            'expires_at'      => 'datetime',
        ];
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
