<?php

namespace App\Repositories;

use App\Models\AppSetting;
use App\Models\StationUpdate;
use App\Repositories\Contracts\UpdateRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UpdateRepository implements UpdateRepositoryInterface
{
    public function forStation(int $stationId): LengthAwarePaginator
    {
        return StationUpdate::with('user')
            ->where('station_id', $stationId)
            ->active()
            ->orderByDesc('created_at')
            ->paginate(20);
    }

    public function allPaginated(array $filters = []): LengthAwarePaginator
    {
        $query = StationUpdate::with(['station', 'user'])->orderByDesc('created_at');

        if (!empty($filters['station_id'])) {
            $query->where('station_id', $filters['station_id']);
        }
        if (isset($filters['is_verified'])) {
            $query->where('is_verified', $filters['is_verified']);
        }
        if (isset($filters['is_admin_update'])) {
            $query->where('is_admin_update', $filters['is_admin_update']);
        }

        return $query->paginate($filters['per_page'] ?? 20);
    }

    public function findById(int $id): ?StationUpdate
    {
        return StationUpdate::with(['station', 'user'])->find($id);
    }

    public function create(array $data): StationUpdate
    {
        return StationUpdate::create($data);
    }

    public function incrementConfirmation(StationUpdate $update): StationUpdate
    {
        $update->increment('confirmation_count');
        $update->refresh();

        $threshold = (int) AppSetting::get('verification_threshold', 3);
        if ($update->confirmation_count >= $threshold && !$update->is_verified) {
            $update->update(['is_verified' => true]);
        }

        return $update;
    }

    public function incrementDispute(StationUpdate $update): StationUpdate
    {
        $update->increment('dispute_count');
        $update->refresh();

        $disputeThreshold = 5; // e.g., if it gets 5 disputes, expire it immediately
        if ($update->dispute_count >= $disputeThreshold) {
            $update->update(['expires_at' => now()]);
        }

        return $update;
    }


    public function delete(StationUpdate $update): void
    {
        $update->delete();
    }

    public function hasRecentUpdateFromUser(int $stationId, int $userId): bool
    {
        return StationUpdate::where('station_id', $stationId)
            ->where('user_id', $userId)
            ->where('created_at', '>=', now()->subMinutes(30))
            ->exists();
    }

    public function hasRecentUpdateFromDevice(int $stationId, string $deviceId, string $ip): bool
    {
        return StationUpdate::where('station_id', $stationId)
            ->where(function ($q) use ($deviceId, $ip) {
                $q->where('device_id', $deviceId)->orWhere('ip_address', $ip);
            })
            ->where('created_at', '>=', now()->subMinutes(30))
            ->exists();
    }

    public function hasAnyRecentUpdateFromUser(int $userId, int $minutes): bool
    {
        return StationUpdate::where('user_id', $userId)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->exists();
    }
}
