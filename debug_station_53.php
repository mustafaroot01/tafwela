<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Station;
use App\Models\StationUpdate;

$station = Station::find(53);
echo "Station: " . $station->name_ar . "\n";
echo "Current Petrol Normal: " . $station->status->petrol_normal . "\n";
echo "Source: " . $station->status->source . "\n";

echo "\nActive Updates:\n";
$updates = StationUpdate::where('station_id', 53)->active()->get();
foreach ($updates as $u) {
    echo "ID: {$u->id}, Normal: {$u->petrol_normal}, Gas: {$u->gas}, Kerosene: {$u->kerosene}, Confirms: {$u->confirmation_count}\n";
}
