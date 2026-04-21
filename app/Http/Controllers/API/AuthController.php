<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $result = $this->authService->sendOtp(
            $request->phone,
            $request->ip()
        );

        return response()->json($result, $result['success'] ? 200 : 429);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $result = $this->authService->verifyOtp(
            $request->phone,
            $request->code,
            $request->ip(),
            $request->device_token
        );

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json([
            'token'  => $result['token'],
            'user'   => new UserResource($result['user']),
            'is_new' => $result['is_new'],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(new UserResource($request->user()));
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate(['name' => ['required', 'string', 'max:100']]);
        $user = $request->user();
        $user->update(['name' => $request->name]);

        return response()->json(new UserResource($user->fresh()));
    }

    public function updateFcmToken(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => ['required', 'string'],
            'platform'  => ['nullable', 'string', 'max:20'],
        ]);

        $user = auth('sanctum')->user();
        
        // Update user record if exists
        if ($user) {
            $user->update(['fcm_token' => $request->fcm_token]);
        }

        // Always update/create in device_tokens for multi-device support
        \App\Models\DeviceToken::updateOrCreate(
            ['token' => $request->fcm_token],
            [
                'user_id'      => $user?->id,
                'platform'     => $request->platform,
                'last_seen_at' => now(),
            ]
        );

        return response()->json(['message' => 'FCM token updated.']);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());
        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        $this->authService->deleteAccount($request->user());
        return response()->json(['message' => 'Account deleted successfully.']);
    }
}
