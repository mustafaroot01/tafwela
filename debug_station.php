<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Station;
use App\Services\UpdateService;

$station = Station::find(54);
echo "Current Petrol Normal: " . $station->status->petrol_normal . "\n";

echo "Applying update...\n";
app(UpdateService::class)->applyBestAvailableStatus($station);

$station->refresh();
echo "New Petrol Normal: " . $station->status->petrol_normal . "\n";
echo "Source: " . $station->status->source . "\n";
