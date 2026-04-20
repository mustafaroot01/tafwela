<?php

namespace App\Listeners;

use App\Events\StationUpdated;
use App\Models\AppSetting;
use App\Models\PushNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotifyFavoriteUsers
{
    public function handle(StationUpdated $event): void
    {
        if (!AppSetting::get('notify_favorites_on_update', true)) {
            return;
        }

        $station = $event->station;
        $status  = $station->status;

        if (!$status) return;

        $overall = $status->overall_status ?? 'unavailable';

        $statusLabel = match($overall) {
            'available'   => 'متوفر ✅',
            'limited'     => 'محدود ⚠️',
            'unavailable' => 'غير متوفر ❌',
            default       => $overall,
        };

        $congestionLabel = match($status->congestion ?? '') {
            'low'    => 'منخفض',
            'medium' => 'متوسط',
            'high'   => 'مرتفع',
            default  => '',
        };

        $stationName = $station->name_ar ?? $station->name;
        $title       = "تحديث محطة: {$stationName}";
        $body        = "حالة الوقود: {$statusLabel}" . ($congestionLabel ? " — الازدحام: {$congestionLabel}" : '');

        $fans = $station->favoritedBy()
            ->where('is_banned', false)
            ->get(['users.id', 'fcm_token']);

        if ($fans->isEmpty()) return;

        $fcmKey = AppSetting::get('fcm_server_key', '');

        foreach ($fans as $user) {
            PushNotification::create([
                'user_id'    => $user->id,
                'station_id' => $station->id,
                'title'      => $title,
                'body'       => $body,
                'type'       => 'station_update',
                'data'       => [
                    'station_id' => $station->id,
                    'overall'    => $overall,
                ],
                'is_read' => false,
            ]);

            if ($fcmKey && $user->fcm_token) {
                $this->pushFcm($fcmKey, $user->fcm_token, $title, $body, $station->id);
            }
        }

        Log::info('[Notify] أُرسلت إشعارات المحطة', [
            'station' => $stationName,
            'fans'    => $fans->count(),
        ]);
    }

    private function pushFcm(string $key, string $token, string $title, string $body, int $stationId): void
    {
        try {
            Http::withHeaders([
                'Authorization' => "key={$key}",
                'Content-Type'  => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to'           => $token,
                'notification' => ['title' => $title, 'body' => $body, 'sound' => 'default'],
                'data'         => ['station_id' => $stationId, 'type' => 'station_update'],
            ]);
        } catch (\Exception $e) {
            Log::error('[FCM] فشل الإرسال', ['error' => $e->getMessage()]);
        }
    }
}
