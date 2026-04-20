<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpdateInteraction extends Model
{
    protected $fillable = [
        'user_id',
        'station_update_id',
        'type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stationUpdate(): BelongsTo
    {
        return $this->belongsTo(StationUpdate::class);
    }
}
