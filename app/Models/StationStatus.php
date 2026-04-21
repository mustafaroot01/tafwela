<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StationStatus extends Model
{
    protected $fillable = [
        'station_id',
        'petrol',
        'petrol_normal',
        'petrol_improved',
        'petrol_super',
        'diesel',
        'kerosene',
        'gas',
        'congestion',
        'source',
        'last_updated_at',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'last_updated_at' => 'datetime',
        ];
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getOverallStatusAttribute(): string
    {
        $statuses = [
            $this->petrol, // Include generic petrol for compatibility
            $this->petrol_normal, 
            $this->petrol_improved, 
            $this->petrol_super, 
            $this->diesel, 
            $this->kerosene, 
            $this->gas
        ];
        if (in_array('available', $statuses)) return 'available';
        if (in_array('limited', $statuses)) return 'limited';
        return 'unavailable';
    }
}
