<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'name_ku',
        'latitude',
        'longitude',
        'address',
        'city',
        'district',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude'  => 'float',
            'longitude' => 'float',
            'is_active' => 'boolean',
        ];
    }

    public function status(): HasOne
    {
        return $this->hasOne(StationStatus::class);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(StationUpdate::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(User::class, 'station_id')->where('role', 'employee');
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(StationReport::class);
    }

    public function stationUpdates(): HasMany
    {
        return $this->hasMany(StationUpdate::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNearby($query, float $lat, float $lng, float $radiusKm = 10)
    {
        $lat = (float) $lat;
        $lng = (float) $lng;
        $radius = (float) $radiusKm;

        return $query->selectRaw(
            '*, ( 6371 * acos( cos( radians(?) ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(latitude) ) ) ) AS distance',
            [$lat, $lng, $lat]
        )->having('distance', '<=', $radius)->orderBy('distance');
    }
}
