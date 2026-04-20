<?php

namespace App\Repositories;

use App\Models\Station;
use App\Repositories\Contracts\StationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StationRepository implements StationRepositoryInterface
{
    public function all(array $filters = []): mixed
    {
        $query = Station::with('status')->orderBy('name');

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('name_ar', 'like', "%$s%")
                  ->orWhere('city', 'like', "%$s%")
                  ->orWhere('district', 'like', "%$s%");
            });
        }

        if (!empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        
        if (isset($filters['all']) && $filters['all']) {
            return $query->get();
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?Station
    {
        return Station::with('status')->find($id);
    }

    public function findNearby(float $lat, float $lng, float $radius = 10, array $filters = []): Collection
    {
        $query = Station::with('status')
            ->active()
            ->nearby($lat, $lng, $radius);

        if (!empty($filters['fuel_type'])) {
            $fuelTypes = (array) $filters['fuel_type'];
            $query->whereHas('status', function($q) use ($fuelTypes) {
                $q->where(function($sq) use ($fuelTypes) {
                    foreach ($fuelTypes as $type) {
                        if ($type === 'petrol_normal') {
                            $sq->orWhere('petrol_normal', '!=', 'unavailable')
                               ->orWhere('petrol', '!=', 'unavailable');
                        } else {
                            $sq->orWhere($type, '!=', 'unavailable');
                        }
                    }
                });
            });
        }

        if (!empty($filters['congestion'])) {
            $query->whereHas('status', fn($q) => $q->where('congestion', $filters['congestion']));
        }

        return $query->get();
    }

    public function create(array $data): Station
    {
        return Station::create($data);
    }

    public function update(Station $station, array $data): Station
    {
        $station->update($data);
        return $station->fresh('status');
    }

    public function delete(Station $station): void
    {
        $station->delete();
    }

    public function search(string $query): Collection
    {
        return Station::with('status')
            ->active()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('name_ar', 'like', "%{$query}%")
                  ->orWhere('address', 'like', "%{$query}%")
                  ->orWhere('city', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get();
    }
}
