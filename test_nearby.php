<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\StationService;

$service = app(StationService::class);

$lat = 33.7505;
$lng = 44.6057;
$radius = 20; // 20km

echo "Fetching nearby stations for ($lat, $lng) with radius {$radius}km...\n";

$stations = $service->getNearby($lat, $lng, $radius);

echo "Found " . $stations->count() . " stations.\n";
foreach ($stations as $s) {
    echo "- ID: {$s->id} | Name: {$s->name} / {$s->name_ar} (Distance: " . round($s->distance, 2) . " km)\n";
}
