<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Station;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramPublicService
{
    protected string $token;

    public function __construct()
    {
        $this->token = AppSetting::get('public_bot_token', '');
    }

    /**
     * Handle incoming webhook updates.
     */
    public function handleUpdate(array $update): void
    {
        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        } elseif (isset($update['callback_query'])) {
            $this->handleCallback($update['callback_query']);
        }
    }

    protected function handleMessage(array $message): void
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';

        if (str_starts_with($text, '/start')) {
            $this->sendWelcome($chatId);
        } elseif (str_starts_with($text, '/verified')) {
            $this->showProvinces($chatId);
        } elseif (str_contains(strtolower($text), 'تفويلة') || str_contains($text, '@' . AppSetting::get('public_bot_username'))) {
            $this->sendWelcome($chatId, "أهلاً بك! كيف يمكنني مساعدتك في العثور على محطة وقود موثوقة؟");
        }
    }

    protected function handleCallback(array $callback): void
    {
        $chatId = $callback['message']['chat']['id'];
        $messageId = $callback['message']['message_id'];
        $data = $callback['data'];

        if ($data === 'show_provinces') {
            $this->showProvinces($chatId, $messageId);
        } elseif (str_starts_with($data, 'prov_')) {
            $province = str_replace('prov_', '', $data);
            $this->showFuelTypes($chatId, $messageId, $province);
        } elseif (str_starts_with($data, 'fuel_')) {
            // Format: fuel_{province}_{type}
            $parts = explode('_', $data);
            $province = $parts[1];
            $fuelType = $parts[2];
            $this->showResults($chatId, $messageId, $province, $fuelType);
        } elseif ($data === 'back_to_main') {
            $this->sendWelcome($chatId, null, $messageId);
        }
    }

    protected function sendWelcome(int $chatId, ?string $text = null, ?int $editMessageId = null): void
    {
        $welcomeText = $text ?? "💡 <b>مرحباً بك في بوت تفويلة!</b>\n\nهذا البوت مخصص لعرض المحطات الموثوقة (الموثقة إدارياً أو من قبل المستخدمين الموثوقين) والبحث عن توفر الوقود حالياً.";
        
        $keyboard = [
            'inline_keyboard' => [
                [['text' => '🔍 البحث حسب المحافظة', 'callback_data' => 'show_provinces']],
                [['text' => '📱 تحميل التطبيق', 'url' => 'https://tafwela.com']],
            ]
        ];

        if ($editMessageId) {
            $this->api('editMessageText', [
                'chat_id' => $chatId,
                'message_id' => $editMessageId,
                'text' => $welcomeText,
                'parse_mode' => 'HTML',
                'reply_markup' => $keyboard
            ]);
        } else {
            $this->api('sendMessage', [
                'chat_id' => $chatId,
                'text' => $welcomeText,
                'parse_mode' => 'HTML',
                'reply_markup' => $keyboard
            ]);
        }
    }

    protected function showProvinces(int $chatId, ?int $messageId = null): void
    {
        $provinces = [
            'بغداد', 'البصرة', 'نينوى', 'أربيل', 
            'النجف', 'كربلاء', 'السليمانية', 'كركوك', 
            'الأنبار', 'ذي قار', 'ميسان', 'القادسية', 
            'المثنى', 'بابل', 'واسط', 'ديالى', 
            'صلاح الدين', 'دهوك'
        ];

        $buttons = [];
        foreach (array_chunk($provinces, 2) as $chunk) {
            $row = [];
            foreach ($chunk as $prov) {
                $row[] = ['text' => $prov, 'callback_data' => 'prov_' . $prov];
            }
            $buttons[] = $row;
        }
        $buttons[] = [['text' => '⬅️ رجوع', 'callback_data' => 'back_to_main']];

        $payload = [
            'chat_id' => $chatId,
            'text' => "📍 <b>اختر المحافظة:</b>",
            'parse_mode' => 'HTML',
            'reply_markup' => ['inline_keyboard' => $buttons]
        ];

        if ($messageId) {
            $payload['message_id'] = $messageId;
            $this->api('editMessageText', $payload);
        } else {
            $this->api('sendMessage', $payload);
        }
    }

    protected function showFuelTypes(int $chatId, int $messageId, string $province): void
    {
        $fuels = [
            'petrol_normal'   => '⛽ بنزين عادي',
            'petrol_improved' => '✨ بنزين محسن',
            'petrol_super'    => '💎 بنزين سوبر',
            'diesel'          => '🚛 كاز',
        ];

        $buttons = [];
        foreach ($fuels as $key => $label) {
            $buttons[] = [['text' => $label, 'callback_data' => "fuel_{$province}_{$key}"]];
        }
        $buttons[] = [['text' => '⬅️ رجوع للمحافظات', 'callback_data' => 'show_provinces']];

        $this->api('editMessageText', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => "⛽ <b>اختر نوع الوقود المطلوب في {$province}:</b>",
            'parse_mode' => 'HTML',
            'reply_markup' => ['inline_keyboard' => $buttons]
        ]);
    }

    protected function showResults(int $chatId, int $messageId, string $province, string $fuelType): void
    {
        // Filter stations in province that have this fuel available and are verified
        // "Verified" means admin source OR trusted user source
        // Filter stations in province that have this fuel available and are verified
        $stations = Station::where(function($q) use ($province) {
                $q->where('city', 'like', "%$province%")
                  ->orWhere('district', 'like', "%$province%")
                  ->orWhere('address', 'like', "%$province%");
            })
            ->whereHas('status', function($q) use ($fuelType) {
                $q->where($fuelType, 'available')
                  ->whereIn('source', ['admin', 'verified_users']);
            })
            ->with('status')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        if ($stations->isEmpty()) {
            $this->api('editMessageText', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => "😔 عذراً، لا توجد حالياً محطات <b>موثقة</b> يتوفر فيها هذا النوع من الوقود في {$province}.",
                'parse_mode' => 'HTML',
                'reply_markup' => ['inline_keyboard' => [[['text' => '⬅️ رجوع', 'callback_data' => "prov_{$province}"]]]]
            ]);
            return;
        }

        $text = "✅ <b>المحطات الموثقة المتوفر فيها الوقود حالياً:</b>\n\n";
        foreach ($stations as $s) {
            $congestion = match ($s->status->congestion) {
                'low'    => '🟢 خالية',
                'medium' => '🟡 متوسطة',
                'high'   => '🔴 مزدحمة',
                default  => '⚪ غير محدد'
            };
            $text .= "📍 <b>{$s->name_ar}</b>\n";
            $text .= "🚦 الازدحام: {$congestion}\n";
            $text .= "🕒 تحديث: {$s->status->updated_at->diffForHumans()}\n";
            $text .= "🔗 <a href='https://www.google.com/maps/search/?api=1&query={$s->latitude},{$s->longitude}'>الموقع على الخارطة</a>\n\n";
        }

        $text .= "💡 <i>لمشاهدة كافة المحطات والتبليغ عن الحالة، حمل تطبيق تفويلة.</i>";

        $this->api('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => ['inline_keyboard' => [[['text' => '🔍 بحث جديد', 'callback_data' => 'show_provinces']]]]
        ]);
    }

    protected function api(string $method, array $data): bool
    {
        if (empty($this->token)) return false;

        try {
            $response = Http::post("https://api.telegram.org/bot{$this->token}/{$method}", $data);
            if (!$response->successful()) {
                Log::error("Telegram Public Bot Error ({$method}): " . $response->body());
                return false;
            }
            return true;
        } catch (\Exception $e) {
            Log::error("Telegram Public Bot Exception: " . $e->getMessage());
            return false;
        }
    }
}
