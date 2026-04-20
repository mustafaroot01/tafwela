<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Station;
use App\Models\StationStatus;
use App\Repositories\Contracts\StationRepositoryInterface;

class StationService
{
    public function __construct(
        private readonly StationRepositoryInterface $stationRepository,
        private readonly OsmService $osmService,
    ) {
    }

    public function getNearby(float $lat, float $lng, float $radius = 10, array $filters = [])
    {
        // 1. Fetch existing stations from DB
        $stations = $this->stationRepository->findNearby($lat, $lng, $radius, $filters);
        \Illuminate\Support\Facades\Log::info('Checking nearby stations', ['db_count' => $stations->count(), 'radius' => $radius]);

        // 2. Dynamic Auto-import logic (controlled from admin settings)
        $osmEnabled  = AppSetting::get('osm_auto_import', true);
        $minExpected = (int) AppSetting::get('osm_min_expected', 8);
        
        if ($osmEnabled && $stations->count() < $minExpected && empty($filters)) {
            \Illuminate\Support\Facades\Log::info('Triggering OSM auto-import', ['lat' => $lat, 'lng' => $lng, 'radius' => $radius]);
            $osmStations = $this->osmService->fetchNearbyStations($lat, $lng, $radius);
            \Illuminate\Support\Facades\Log::info('OSM stations fetched', ['count' => count($osmStations)]);

            foreach ($osmStations as $osmData) {
                // Check if station already exists by coordinates (small epsilon)
                $exists = Station::whereBetween('latitude', [$osmData['latitude'] - 0.0001, $osmData['latitude'] + 0.0001])
                    ->whereBetween('longitude', [$osmData['longitude'] - 0.0001, $osmData['longitude'] + 0.0001])
                    ->exists();

                if (!$exists) {
                    \Illuminate\Support\Facades\Log::info('Saving new OSM station', ['name' => $osmData['name']]);
                    $this->create($osmData);
                }
            }

            // Refresh stations from DB after import
            $stations = $this->stationRepository->findNearby($lat, $lng, $radius, $filters);
        }

        return $stations;
    }

    public function getAll(array $filters = [])
    {
        return $this->stationRepository->all($filters);
    }

    public function find(int $id): ?Station
    {
        return $this->stationRepository->findById($id);
    }

    public function create(array $data): Station
    {
        $station = $this->stationRepository->create($data);

        StationStatus::create([
            'station_id' => $station->id,
            'petrol' => 'unavailable',
            'diesel' => 'unavailable',
            'kerosene' => 'unavailable',
            'gas' => 'unavailable',
            'congestion' => 'low',
            'source' => 'admin',
            'last_updated_at' => now(),
        ]);

        return $station->load('status');
    }

    public function update(Station $station, array $data): Station
    {
        return $this->stationRepository->update($station, $data);
    }

    public function delete(Station $station): void
    {
        $this->stationRepository->delete($station);
    }

    public function importAlongRoute(array $points): void
    {
        foreach ($points as $point) {
            $lat = $point['lat'];
            $lng = $point['lng'];
            
            // Fetch and save (this reuse existing logic in getNearby)
            // Use 12km radius to ensure overlap and full path coverage if points are ~10km apart
            $this->getNearby($lat, $lng, 12); 
        }
    }

    public function search(string $query)
    {
        return $this->stationRepository->search($query);
    }
}
