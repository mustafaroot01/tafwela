<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UpdateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'station_id'         => $this->station_id,
            'petrol'             => $this->petrol,
            'petrol_normal'      => $this->petrol_normal,
            'petrol_improved'    => $this->petrol_improved,
            'petrol_super'       => $this->petrol_super,
            'diesel'             => $this->diesel,
            'kerosene'           => $this->kerosene,
            'gas'                => $this->gas,
            'congestion'         => $this->congestion,
            'is_admin_update'    => $this->is_admin_update,
            'is_verified'        => $this->is_verified,
            'is_trusted_user'    => $this->user?->is_trusted ?? false,
            'confirmation_count' => $this->confirmation_count,
            'expires_at'         => $this->expires_at?->toIso8601String(),
            'created_at'         => $this->created_at->diffForHumans(),
        ];
    }
}
