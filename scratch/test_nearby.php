<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Station;

$lat = 33.3152;
$lng = 44.3661;
$radius = 10;

$stations = Station::active()->nearby($lat, $lng, $radius)->get();

echo "Found " . $stations->count() . " stations within {$radius}km of ($lat, $lng)\n";
foreach ($stations as $s) {
    echo "- {$s->name_ar} (Distance: " . round($s->distance, 2) . " km)\n";
}
