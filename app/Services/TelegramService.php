<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     * Send a message to the configured Telegram chat.
     */
    public function sendMessage(string $message): bool
    {
        if (!AppSetting::get('telegram_enabled', false)) {
            return false;
        }

        $token = AppSetting::get('telegram_bot_token');
        $chatId = AppSetting::get('telegram_chat_id');

        if (empty($token) || empty($chatId)) {
            Log::warning('Telegram notification failed: Bot token or Chat ID is missing.');
            return false;
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            if (!$response->successful()) {
                Log::error('Telegram API error: ' . $response->body());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Telegram notification exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notify about a new station report.
     */
    public function notifyReport($report): void
    {
        if (!AppSetting::get('telegram_notify_reports', true)) {
            return;
        }

        $reason = match ($report->reason) {
            'wrong_name' => 'اسم خاطئ',
            'not_existing' => 'المحطة غير موجودة',
            'wrong_location' => 'موقع خاطئ',
            'out_of_service' => 'خارج الخدمة',
            'other' => 'سبب آخر',
            default => $report->reason
        };

        $message = "🚨 <b>تبليغ جديد عن محطة</b>\n\n";
        $message .= "📍 <b>المحطة:</b> {$report->station->name_ar}\n";
        $message .= "🚩 <b>السبب:</b> {$reason}\n";
        if ($report->comment) {
            $message .= "💬 <b>التعليق:</b> {$report->comment}\n";
        }
        $message .= "👤 <b>بواسطة:</b> {$report->user->name} ({$report->user->phone})\n";
        $message .= "📅 <b>التاريخ:</b> " . now()->format('Y-m-d H:i');

        $this->sendMessage($message);
    }

    /**
     * Notify about a new fuel status update.
     */
    public function notifyUpdate($update): void
    {
        if (!AppSetting::get('telegram_notify_updates', true)) {
            return;
        }

        $station = $update->station;
        $status = $update->petrol_status === 'available' ? '✅ متوفر' : '❌ غير متوفر';
        $otherStatus = $update->other_fuel_status === 'available' ? '✅ متوفر' : '❌ غير متوفر';

        $congestion = match ($update->congestion_level) {
            'empty' => '🟢 خالية',
            'medium' => '🟡 متوسطة',
            'crowded' => '🔴 مزدحمة',
            default => $update->congestion_level
        };

        $message = "⛽ <b>تحديث جديد لحالة الوقود</b>\n\n";
        $message .= "📍 <b>المحطة:</b> {$station->name_ar}\n";
        $message .= "🔹 <b>البنزين:</b> {$status}\n";
        $message .= "🔸 <b>وقود آخر:</b> {$otherStatus}\n";
        $message .= "🚦 <b>الازدحام:</b> {$congestion}\n";
        $message .= "👤 <b>بواسطة:</b> {$update->user->name} ({$update->user->phone})\n";
        $message .= "📅 <b>التاريخ:</b> " . now()->format('Y-m-d H:i');

        $this->sendMessage($message);
    }
}
