<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Station;
use App\Models\StationUpdate;
use App\Models\AppSetting;
use App\Models\User;
use App\Services\UpdateService;

// 1. Setup
$station = Station::find(54);
$users = User::limit(2)->get();
if ($users->count() < 2) {
    echo "Need at least 2 users in DB to run simulation\n";
    exit;
}

$u1 = $users[0];
$u2 = $users[1];

$station->status()->update([
    'petrol_improved' => 'unavailable',
    'gas' => 'unavailable',
    'kerosene' => 'unavailable',
]);

AppSetting::where('key', 'verification_threshold')->update(['value' => '2']);

echo "Initial Status: Improved: {$station->status->petrol_improved}, Gas: {$station->status->gas}, Kerosene: {$station->status->kerosene}\n";

// 2. Simulate User 1 Report
echo "\n--- User 1 Reports: Improved: available, Gas: available ---\n";
StationUpdate::create([
    'station_id' => 54,
    'user_id' => $u1->id,
    'petrol_improved' => 'available',
    'gas' => 'available',
    'expires_at' => now()->addHours(2),
]);

// 3. Simulate User 2 Report (Different combination, but overlaps on Improved)
echo "--- User 2 Reports: Improved: available, Kerosene: available ---\n";
StationUpdate::create([
    'station_id' => 54,
    'user_id' => $u2->id,
    'petrol_improved' => 'available',
    'kerosene' => 'available',
    'expires_at' => now()->addHours(2),
]);

// 4. Apply Consensus
app(UpdateService::class)->applyBestAvailableStatus($station);
$station->refresh();

echo "\nFinal Status after Consensus (Threshold=2):\n";
echo "Petrol Improved: " . $station->status->petrol_improved . " (Verified by 2 reports!)\n";
echo "Gas: " . $station->status->gas . " (Only 1 report, stayed unavailable)\n";
echo "Kerosene: " . $station->status->kerosene . " (Only 1 report, stayed unavailable)\n";
