<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OsmService
{
    private const OVERPASS_URL = 'https://overpass-api.de/api/interpreter';

    /**
     * Fetch fuel stations from OpenStreetMap near a location.
     */
    public function fetchNearbyStations(float $lat, float $lng, float $radiusKm = 5): array
    {
        $radiusMeters = $radiusKm * 1000;
        
        // Overpass QL query: find nodes, ways and relations with amenity=fuel
        $query = <<<EOT
        [out:json][timeout:90];
        (
          node["amenity"="fuel"](around:$radiusMeters,$lat,$lng);
          way["amenity"="fuel"](around:$radiusMeters,$lat,$lng);
          relation["amenity"="fuel"](around:$radiusMeters,$lat,$lng);
        );
        out body center;
        EOT;

        \Illuminate\Support\Facades\Log::info('OSM Query', ['query' => $query]);

        try {
            $response = Http::asForm()->timeout(60)->post(self::OVERPASS_URL, [
                'data' => $query
            ]);

            if ($response->failed()) {
                Log::error('OSM Overpass API failed', ['status' => $response->status(), 'body' => $response->body()]);
                return [];
            }

            $data = $response->json();
            $elements = $data['elements'] ?? [];
            Log::info('OSM Overpass API response received', ['elements_count' => count($elements)]);
            
            return $this->processElements($elements);
        } catch (\Exception $e) {
            Log::error('OSM Overpass API exception', ['message' => $e->getMessage()]);
            return [];
        }
    }

    private function processElements(array $elements): array
    {
        $stations = [];

        foreach ($elements as $element) {
            $tags = $element['tags'] ?? [];
            $lat = $element['lat'] ?? ($element['center']['lat'] ?? null);
            $lng = $element['lon'] ?? ($element['center']['lon'] ?? null);

            if (!$lat || !$lng) continue;

            $station = [
                'name'      => $tags['name'] ?? ($tags['operator'] ?? 'Fuel Station'),
                'name_ar'   => $tags['name:ar'] ?? ($tags['operator:ar'] ?? null),
                'name_ku'   => $tags['name:ku'] ?? null,
                'latitude'  => (float) $lat,
                'longitude' => (float) $lng,
                'address'   => $tags['addr:full'] ?? ($tags['addr:street'] ?? ($tags['addr:place'] ?? null)),
                'city'      => $tags['addr:city'] ?? ($tags['addr:province'] ?? ($tags['addr:state'] ?? null)),
                'district'  => $tags['addr:suburb'] ?? ($tags['addr:district'] ?? ($tags['addr:neighbourhood'] ?? null)),
                'brand'     => $tags['brand'] ?? ($tags['operator'] ?? null),
                'osm_id'    => $element['type'] . '/' . $element['id'],
            ];

            /* 
            // If address or city is still missing, use Reverse Geocoding
            if (empty($station['address']) || empty($station['city'])) {
                Log::info('Fetching missing address for station', ['name' => $station['name']]);
                $geoData = $this->reverseGeocode((float) $lat, (float) $lng);
                if ($geoData) {
                    $station['address']  = $station['address']  ?? $geoData['address'];
                    $station['city']     = $station['city']     ?? $geoData['city'];
                    $station['district'] = $station['district'] ?? $geoData['district'];
                }
                // Small sleep to avoid instant blocking, but keep it fast
                usleep(200000); // 0.2s
            }
            */

            $stations[] = $station;
        }

        return $stations;
    }

    private function reverseGeocode(float $lat, float $lng): ?array
    {
        try {
            $response = Http::withHeaders(['User-Agent' => 'TafwelaApp/1.0'])
                ->timeout(5)
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'format' => 'json',
                    'lat'    => $lat,
                    'lon'    => $lng,
                    'zoom'   => 18,
                    'addressdetails' => 1,
                    'accept-language' => 'ar,en',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $addr = $data['address'] ?? [];

                return [
                    'address'  => $data['display_name'] ?? null,
                    'city'     => $addr['city'] ?? ($addr['town'] ?? ($addr['village'] ?? ($addr['state'] ?? null))),
                    'district' => $addr['suburb'] ?? ($addr['neighbourhood'] ?? ($addr['road'] ?? null)),
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Nominatim reverse geocode failed', ['error' => $e->getMessage()]);
        }

        return null;
    }


}
