<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = $this->whenLoaded('status');

        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'name_ar'   => $this->name_ar,
            'name_ku'   => $this->name_ku,
            'latitude'  => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'address'   => $this->address,
            'city'      => $this->city,
            'distance'  => isset($this->distance) ? round($this->distance, 2) : null,
            'status'    => $this->when($status, fn() => [
                'petrol'          => $status->petrol,
                'petrol_normal'   => $status->petrol_normal,
                'petrol_improved' => $status->petrol_improved,
                'petrol_super'    => $status->petrol_super,
                'diesel'          => $status->diesel,
                'kerosene'        => $status->kerosene,
                'gas'             => $status->gas,
                'congestion'      => $status->congestion,
                'source'          => $status->source,
                'is_trusted_user' => $status->user?->is_trusted ?? false,
                'last_updated_at' => $status->last_updated_at?->diffForHumans(),
                'overall'         => $status->overall_status,
            ]),
            'is_favorited' => $this->when(
                $request->user(),
                fn() => $this->favoritedBy->contains($request->user()?->id)
            ),
        ];
    }
}
