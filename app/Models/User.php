<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'phone',
        'role',
        'station_id',
        'is_admin',
        'is_banned',
        'device_token',
        'fcm_token',
        'last_ip',
        'last_active_at',
        'update_count',
        'is_trusted',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'is_admin'       => 'boolean',
            'is_banned'      => 'boolean',
            'is_trusted'     => 'boolean',
            'last_active_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->is_admin || $this->role === 'admin';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function assignedStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    public function stationUpdates(): HasMany
    {
        return $this->hasMany(StationUpdate::class);
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Station::class, 'favorites')->withTimestamps();
    }

    public function pushNotifications(): HasMany
    {
        return $this->hasMany(PushNotification::class);
    }

    /**
     * Normalize phone number to Iraqi format: 9647XXXXXXXX
     */
    public static function normalizePhone(string $phone): string
    {
        // Strip all non-digits
        $phone = preg_replace('/\D/', '', $phone);

        // Remove leading double zeros 00964 -> 964
        if (str_starts_with($phone, '00964')) {
            $phone = substr($phone, 2);
        }

        // Handle local 07... or 0... -> strip leading 0
        if (str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }

        // Handle local 7... -> 9647...
        if (str_starts_with($phone, '7') && strlen($phone) <= 10) {
            $phone = '964' . $phone;
        }

        // Prevent double 964964
        if (str_starts_with($phone, '964964')) {
            $phone = substr($phone, 3);
        }

        return $phone;
    }
}
