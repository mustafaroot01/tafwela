<?php

namespace App\Repositories\Contracts;

use App\Models\Station;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StationRepositoryInterface
{
    public function all(array $filters = []): mixed;
    public function findById(int $id): ?Station;
    public function findNearby(float $lat, float $lng, float $radius = 10, array $filters = []): Collection;
    public function create(array $data): Station;
    public function update(Station $station, array $data): Station;
    public function delete(Station $station): void;
    public function search(string $query): Collection;
}
