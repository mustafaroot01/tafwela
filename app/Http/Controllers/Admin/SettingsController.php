<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $groups = [
            'otp' => ['label' => 'إعدادات OTP والتحقق', 'settings' => AppSetting::getGroup('otp')],
            'stations' => ['label' => 'إعدادات المحطات', 'settings' => AppSetting::getGroup('stations')],
            'notifications' => ['label' => 'إعدادات الإشعارات', 'settings' => AppSetting::getGroup('notifications')],
            'telegram' => ['label' => 'إعدادات بوت تليجرام (إشعارات)', 'settings' => AppSetting::getGroup('telegram')],
            'telegram_public' => ['label' => 'بوت المجموعات التفاعلي', 'settings' => AppSetting::getGroup('telegram_public')],
            'app' => ['label' => 'إعدادات التطبيق', 'settings' => AppSetting::getGroup('app')],
            'pages' => ['label' => 'صفحات التطبيق', 'settings' => AppSetting::getGroup('pages')],
        ];

        return view('admin.settings.index', compact('groups'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->except(['_token', '_method']);

        foreach ($data as $key => $value) {
            $setting = AppSetting::where('key', $key)->first();
            if (!$setting)
                continue;

            if ($setting->type === 'boolean') {
                $value = $request->boolean($key) ? 'true' : 'false';
            }

            AppSetting::set($key, $value ?? '');
        }

        // الإعدادات Boolean غير المرسلة في POST تعني false
        $booleans = AppSetting::where('type', 'boolean')->pluck('key');
        foreach ($booleans as $boolKey) {
            if (!array_key_exists($boolKey, $data)) {
                AppSetting::set($boolKey, 'false');
            }
        }

        return back()->with([
            'success' => 'تم حفظ الإعدادات بنجاح',
            'active_tab' => $request->active_tab
        ]);
    }

    public function testTelegram(): \Illuminate\Http\JsonResponse
    {
        $telegramService = app(\App\Services\TelegramService::class);
        $success = $telegramService->sendMessage("🔔 <b>اختبار الاتصال</b>\nتم ربط البوت بنجاح مع لوحة تحكم تفويلة!");

        return response()->json([
            'success' => $success,
            'message' => $success ? 'تم إرسال رسالة تجريبية بنجاح' : 'فشل الإرسال، تأكد من التوكن و Chat ID'
        ]);
    }

    public function setTelegramWebhook(): \Illuminate\Http\JsonResponse
    {
        $token = AppSetting::get('public_bot_token');
        if (empty($token)) {
            return response()->json(['success' => false, 'message' => 'التوكن غير موجود']);
        }

        $url = url('/api/telegram/webhook');
        
        try {
            $response = \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot{$token}/setWebhook", [
                'url' => $url
            ]);

            if ($response->successful()) {
                return response()->json(['success' => true, 'message' => 'تم ضبط Webhook بنجاح على: ' . $url]);
            }

            return response()->json(['success' => false, 'message' => 'خطأ من تليجرام: ' . $response->json('description')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
        }
    }


    public function testFcm(): \Illuminate\Http\JsonResponse
    {
        try {
            $fcmService = app(\App\Services\FcmV1Service::class);
            $success = $fcmService->sendToTopic('all_users', '🔔 اختبار التنبيهات', 'إذا وصلك هذا التنبيه، فهذا يعني أن الربط مع Firebase يعمل بنجاح!');

            return response()->json([
                'success' => $success,
                'message' => $success ? 'تم إرسال تنبيه تجريبي لجميع المستخدمين (Topic: all_users)' : 'فشل الإرسال، تأكد من ملف Service Account'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ: ' . $e->getMessage()
            ]);
        }
    }

    public function testOtp(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['phone' => 'required|string|min:10']);

        $otpService = app(OtpService::class);
        $result = $otpService->testSend($request->phone);

        return response()->json($result);
    }
}
