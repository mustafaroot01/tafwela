<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\OsmService;

$service = app(OsmService::class);

$lat = 33.7525; // Station 43 in user's list
$lng = 44.6113;

echo "Reverse Geocoding for ($lat, $lng)...\n";

$osmStations = $service->fetchNearbyStations($lat, $lng, 1);

foreach ($osmStations as $s) {
    echo "Name: {$s['name']}\n";
    echo "Address: {$s['address']}\n";
    echo "City: {$s['city']}\n";
    echo "District: {$s['district']}\n";
    echo "---------------------------\n";
}
