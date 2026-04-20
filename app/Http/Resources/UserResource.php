<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $station = $this->when($this->role === 'employee' && $this->station_id, function () {
            $s = $this->assignedStation;
            if (!$s) return null;
            return [
                'id'        => $s->id,
                'name'      => $s->name,
                'name_ar'   => $s->name_ar,
                'latitude'  => $s->latitude,
                'longitude' => $s->longitude,
            ];
        });

        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'phone'            => $this->phone,
            'role'             => $this->role ?? 'user',
            'station_id'       => $this->station_id,
            'assigned_station' => $station,
            'is_admin'         => $this->is_admin,
            'is_trusted'       => $this->is_trusted ?? false,
            'update_count'     => $this->update_count,
            'last_active_at'   => $this->last_active_at?->diffForHumans(),
            'created_at'       => $this->created_at->toDateString(),
        ];
    }
}
