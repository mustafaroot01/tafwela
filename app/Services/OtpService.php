<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\OtpCode;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OtpService
{
    private const FALLBACK_EXPIRY_MINUTES      = 5;
    private const FALLBACK_MAX_ATTEMPTS        = 5;
    private const OTPIQ_ENDPOINT               = 'https://api.otpiq.com/api/sms';

    private function expiryMinutes(): int
    {
        return (int) AppSetting::get('otp_expiry_minutes', self::FALLBACK_EXPIRY_MINUTES);
    }

    private function maxAttempts(): int
    {
        return (int) AppSetting::get('otp_max_attempts', self::FALLBACK_MAX_ATTEMPTS);
    }

    public function generate(string $phone, string $ip): OtpCode
    {
        OtpCode::where('phone', $phone)->where('is_used', false)->delete();

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        return OtpCode::create([
            'phone'      => $phone,
            'code'       => $code,
            'ip_address' => $ip,
            'expires_at' => now()->addMinutes($this->expiryMinutes()),
        ]);
    }

    public function verify(string $phone, string $code): bool
    {
        $otp = OtpCode::where('phone', $phone)
            ->where('code', $code)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) return false;

        $otp->update(['is_used' => true]);
        return true;
    }

    public function isRateLimited(string $phone): bool
    {
        return OtpCode::where('phone', $phone)
            ->where('created_at', '>=', now()->subHour())
            ->count() >= $this->maxAttempts();
    }

    public function send(string $phone, string $code): void
    {
        $enabled = AppSetting::get('otp_enabled', true);

        if (!$enabled) {
            Log::info("[OTP-DEV] {$phone} → {$code}");
            return;
        }

        $apiKey  = AppSetting::get('otpiq_api_key', config('services.otpiq.key', ''));
        $channel = AppSetting::get('otpiq_channel', config('services.otpiq.provider', 'whatsapp-sms'));

        if (empty($apiKey)) {
            Log::warning("[OTP] مفتاح OTPIQ غير مضبوط. الكود: {$phone} → {$code}");
            return;
        }

        // OTPIQ يقبل الرقم بدون + (مثال: 9647501234567)
        $phoneNumber = ltrim($phone, '+');

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Accept'        => 'application/json',
            ])->post(self::OTPIQ_ENDPOINT, [
                'phoneNumber'      => $phoneNumber,
                'smsType'          => 'verification',
                'verificationCode' => $code,
                'provider'         => $channel,
            ]);

            if ($response->successful()) {
                Log::info('[OTP] تم الإرسال عبر OTPIQ', [
                    'phone'     => $phone,
                    'smsId'     => $response->json('smsId'),
                    'remaining' => $response->json('remainingCredit'),
                ]);
            } else {
                Log::error('[OTP] فشل OTPIQ', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                    'phone'  => $phone,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('[OTP] استثناء OTPIQ', ['error' => $e->getMessage(), 'phone' => $phone]);
        }
    }

    public function testSend(string $phone): array
    {
        $apiKey  = AppSetting::get('otpiq_api_key', '');
        $channel = AppSetting::get('otpiq_channel', 'whatsapp-sms');

        if (empty($apiKey)) {
            return ['success' => false, 'message' => 'مفتاح API غير مضبوط'];
        }

        $testCode    = '123456';
        $phoneNumber = ltrim($phone, '+');

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Accept'        => 'application/json',
            ])->post(self::OTPIQ_ENDPOINT, [
                'phoneNumber'      => $phoneNumber,
                'smsType'          => 'verification',
                'verificationCode' => $testCode,
                'provider'         => $channel,
            ]);

            if ($response->successful()) {
                return [
                    'success'   => true,
                    'message'   => 'تم إرسال رمز تجريبي بنجاح',
                    'smsId'     => $response->json('smsId'),
                    'remaining' => $response->json('remainingCredit'),
                ];
            }

            return ['success' => false, 'message' => 'فشل الإرسال: ' . $response->body()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'خطأ: ' . $e->getMessage()];
        }
    }
}
