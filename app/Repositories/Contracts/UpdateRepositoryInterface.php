<?php

namespace App\Repositories\Contracts;

use App\Models\StationUpdate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UpdateRepositoryInterface
{
    public function forStation(int $stationId): LengthAwarePaginator;
    public function allPaginated(array $filters = []): LengthAwarePaginator;
    public function findById(int $id): ?StationUpdate;
    public function create(array $data): StationUpdate;
    public function incrementConfirmation(StationUpdate $update): StationUpdate;
    public function incrementDispute(StationUpdate $update): StationUpdate;
    public function delete(StationUpdate $update): void;
    public function hasRecentUpdateFromUser(int $stationId, int $userId): bool;
    public function hasRecentUpdateFromDevice(int $stationId, string $deviceId, string $ip): bool;
    public function hasAnyRecentUpdateFromUser(int $userId, int $minutes): bool;
}
