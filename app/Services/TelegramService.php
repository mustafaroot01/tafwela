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
    public function sendMessage(string $message, ?array $replyMarkup = null): bool
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
            $data = [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ];

            if ($replyMarkup) {
                $data['reply_markup'] = json_encode($replyMarkup);
            }

            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", $data);

            if (!$response->successful()) {
                Log::error('Telegram API error (' . $response->status() . '): ' . $response->body());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Telegram notification exception: ' . $e->getMessage(), [
                'token_length' => strlen($token),
                'chat_id' => $chatId
            ]);
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

        $message = "🚨 <b>تبليغ جديد عن مشكلة</b>\n\n";
        $message .= "📍 <b>المحطة:</b> {$report->station->name_ar}\n";
        $message .= "🚩 <b>السبب:</b> {$reason}\n";
        if ($report->comment) {
            $message .= "💬 <b>التعليق:</b> {$report->comment}\n";
        }
        $message .= "👤 <b>بواسطة:</b> {$report->user->name}\n";
        $message .= "📅 <b>التاريخ:</b> " . now()->format('Y-m-d H:i');

        $replyMarkup = [
            'inline_keyboard' => [[
                ['text' => '📍 فتح في الخرائط', 'url' => "https://www.google.com/maps/search/?api=1&query={$report->station->latitude},{$report->station->longitude}"]
            ]]
        ];

        $this->sendMessage($message, $replyMarkup);
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
        $user = $update->user;

        // Categorize Fuels
        $available = [];
        $unavailable = [];

        $fuels = [
            'petrol_normal'   => 'بنزين عادي',
            'petrol_improved' => 'بنزين محسن',
            'petrol_super'    => 'بنزين سوبر',
            'diesel'          => 'كاز (زيت الغاز)',
            'kerosene'        => 'نفط أبيض',
            'gas'             => 'غاز سائل',
        ];

        foreach ($fuels as $key => $label) {
            if ($update->{$key} === 'available') {
                $available[] = "✅ " . $label;
            } elseif ($update->{$key} === 'unavailable') {
                $unavailable[] = "❌ " . $label;
            }
        }

        // Congestion Mapping
        $congestion = match ($update->congestion) {
            'low'    => '🟢 خفيفة / خالية',
            'medium' => '🟡 متوسطة',
            'high'   => '🔴 مزدحمة جداً',
            default  => '⚪ غير محدد'
        };

        // User Label (Employee Check)
        $userLabel = $user->name;
        if ($user->role === 'employee' && (int) $user->station_id === (int) $station->id) {
            $userLabel .= " (👷 موظف المحطة)";
        }

        $message = "⛽ <b>تحديث جديد لحالة الوقود</b>\n\n";
        $message .= "📍 <b>المحطة:</b> {$station->name_ar}\n";
        $message .= "━━━━━━━━━━━━━━━\n";

        if (!empty($available)) {
            $message .= "<b>✨ المتوفر حالياً:</b>\n" . implode("\n", $available) . "\n\n";
        }

        if (!empty($unavailable)) {
            $message .= "<b>🚫 غير متوفر:</b>\n" . implode("\n", $unavailable) . "\n\n";
        }

        $message .= "🚦 <b>حالة الازدحام:</b> {$congestion}\n";
        $message .= "━━━━━━━━━━━━━━━\n";
        $message .= "👤 <b>بواسطة:</b> {$userLabel}\n";
        $message .= "📅 <b>التاريخ:</b> " . now()->format('Y-m-d H:i');

        $replyMarkup = [
            'inline_keyboard' => [[
                ['text' => '🗺️ الذهاب للموقع (Google Maps)', 'url' => "https://www.google.com/maps/search/?api=1&query={$station->latitude},{$station->longitude}"]
            ]]
        ];

        $this->sendMessage($message, $replyMarkup);
    }
}
