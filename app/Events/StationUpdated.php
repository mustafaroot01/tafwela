<?php

namespace App\Events;

use App\Models\Station;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Station $station) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('stations'),
            new Channel("station.{$this->station->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'StationUpdated';
    }

    public function broadcastWith(): array
    {
        $status = $this->station->status;

        return [
            'id'        => $this->station->id,
            'name'      => $this->station->name,
            'latitude'  => $this->station->latitude,
            'longitude' => $this->station->longitude,
            'status'    => $status ? [
                'petrol'          => $status->petrol,
                'diesel'          => $status->diesel,
                'kerosene'        => $status->kerosene,
                'gas'             => $status->gas,
                'congestion'      => $status->congestion,
                'source'          => $status->source,
                'last_updated_at' => $status->last_updated_at?->toIso8601String(),
                'overall'         => $status->overall_status,
            ] : null,
        ];
    }
}
