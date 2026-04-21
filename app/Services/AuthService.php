<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly OtpService $otpService,
    ) {}

    public function sendOtp(string $phone, string $ip): array
    {
        $phone = User::normalizePhone($phone);
        if ($this->otpService->isRateLimited($phone)) {
            return ['success' => false, 'message' => 'Too many OTP requests. Please wait.'];
        }

        $otp = $this->otpService->generate($phone, $ip);
        $this->otpService->send($phone, $otp->code);

        return ['success' => true, 'message' => 'OTP sent successfully.'];
    }

    public function verifyOtp(string $phone, string $code, string $ip, ?string $deviceToken = null): array
    {
        $phone = User::normalizePhone($phone);
        if (!$this->otpService->verify($phone, $code)) {
            return ['success' => false, 'message' => 'Invalid or expired OTP.'];
        }

        $user = $this->userRepository->findByPhone($phone);

        if (!$user) {
            $user = $this->userRepository->create(['phone' => $phone]);
        }

        if ($user->is_banned) {
            return ['success' => false, 'message' => 'Your account has been suspended.'];
        }

        $this->userRepository->update($user, [
            'last_ip'        => $ip,
            'last_active_at' => now(),
            'device_token'   => $deviceToken ?? $user->device_token,
        ]);

        $token = $user->createToken('mobile_app')->plainTextToken;

        return [
            'success' => true,
            'token'   => $token,
            'user'    => $user->fresh(),
            'is_new'  => $user->wasRecentlyCreated,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function deleteAccount(User $user): void
    {
        // 1. Delete all tokens
        $user->tokens()->delete();

        // 2. Clear relationships
        $user->favorites()->detach();
        
        // 3. Delete notifications
        $user->pushNotifications()->delete();

        // 4. Delete the user
        $user->delete();
    }
}
