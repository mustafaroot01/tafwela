<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Firebase Cloud Messaging v1 API sender.
 * Uses a Service Account JSON to obtain a short-lived OAuth2 access token,
 * then sends notifications via the FCM HTTP v1 API.
 * No external packages required – uses PHP's built-in openssl.
 */
class FcmV1Service
{
    private const TOKEN_CACHE_KEY = 'fcm_v1_access_token';
    private const TOKEN_CACHE_TTL = 55; // minutes (tokens expire in 60 min)
    private const GOOGLE_TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const FCM_SEND_URL     = 'https://fcm.googleapis.com/v1/projects/%s/messages:send';

    private ?array $sa = null;

    private function serviceAccount(): array
    {
        if ($this->sa !== null) {
            return $this->sa;
        }

        $path = base_path(env('FIREBASE_SA_PATH', 'storage/app/firebase/service-account.json'));

        if (!file_exists($path)) {
            throw new \RuntimeException("Firebase service account not found at: {$path}");
        }

        $this->sa = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        return $this->sa;
    }

    /**
     * Return a cached (or freshly generated) OAuth2 Bearer token.
     */
    public function accessToken(): string
    {
        return Cache::remember(self::TOKEN_CACHE_KEY, now()->addMinutes(self::TOKEN_CACHE_TTL), function () {
            return $this->fetchNewAccessToken();
        });
    }

    private function fetchNewAccessToken(): string
    {
        $sa  = $this->serviceAccount();
        $now = time();

        $header  = $this->base64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->base64url(json_encode([
            'iss'   => $sa['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => self::GOOGLE_TOKEN_URL,
            'exp'   => $now + 3600,
            'iat'   => $now,
        ]));

        $unsigned   = "{$header}.{$payload}";
        $privateKey = openssl_pkey_get_private($sa['private_key']);

        openssl_sign($unsigned, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        $jwt = $unsigned . '.' . $this->base64url($signature);

        $response = Http::asForm()->post(self::GOOGLE_TOKEN_URL, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('[FCM] Token exchange failed: ' . $response->body());
        }

        return $response->json('access_token');
    }

    /**
     * Send a push notification to a single device token.
     */
    public function send(string $deviceToken, string $title, string $body, ?string $imageUrl = null, array $data = []): bool
    {
        try {
            $sa  = $this->serviceAccount();
            $url = sprintf(self::FCM_SEND_URL, $sa['project_id']);

            $notification = ['title' => $title, 'body' => $body];

            $message = [
                'token'        => $deviceToken,
                'notification' => $notification,
                'data'         => array_merge(['type' => 'admin_broadcast'], $data),
                'android'      => [
                    'priority' => 'high',
                    'notification' => array_merge(
                        [
                            'sound' => 'default',
                            'channel_id' => 'tafwela_alerts_v1',
                        ],
                        $imageUrl ? ['image' => $imageUrl] : []
                    ),
                ],
                'apns' => [
                    'payload'     => ['aps' => ['sound' => 'default', 'mutable-content' => 1]],
                    'fcm_options' => $imageUrl ? (object)['image' => $imageUrl] : (object)[],
                ],
            ];

            $token    = $this->accessToken();
            $response = Http::withToken($token)
                ->post($url, ['message' => $message]);

            if (!$response->successful()) {
                Log::warning('[FCM v1] Send failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('[FCM v1] Exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send a push notification to an FCM topic (e.g. 'all_users').
     * Reaches all subscribers regardless of login status.
     */
    public function sendToTopic(string $topic, string $title, string $body, ?string $imageUrl = null, array $data = []): bool
    {
        try {
            $sa  = $this->serviceAccount();
            $url = sprintf(self::FCM_SEND_URL, $sa['project_id']);

            $notification = ['title' => $title, 'body' => $body];

            $message = [
                'topic'        => $topic,
                'notification' => $notification,
                'data'         => array_merge(['type' => 'admin_broadcast'], $data),
                'android'      => [
                    'priority' => 'high',
                    'notification' => array_merge(
                        [
                            'sound' => 'default',
                            'channel_id' => 'tafwela_alerts_v1',
                        ],
                        $imageUrl ? ['image' => $imageUrl] : []
                    ),
                ],
                'apns' => [
                    'payload'     => ['aps' => ['sound' => 'default', 'mutable-content' => 1]],
                    'fcm_options' => $imageUrl ? (object)['image' => $imageUrl] : (object)[],
                ],
            ];

            $token    = $this->accessToken();
            $response = Http::withToken($token)
                ->post($url, ['message' => $message]);

            if (!$response->successful()) {
                Log::warning('[FCM v1 Topic] Send failed', [
                    'topic'  => $topic,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('[FCM v1 Topic] Exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
