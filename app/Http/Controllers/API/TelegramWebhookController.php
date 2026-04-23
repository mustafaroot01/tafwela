<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\TelegramPublicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __construct(
        protected TelegramPublicService $telegramPublicService
    ) {}

    /**
     * Handle incoming webhook from Telegram.
     */
    public function handle(Request $request)
    {
        // For security, you could check if the request comes from Telegram IP ranges
        // but for now, we'll just log and handle.
        
        $update = $request->all();
        
        if (empty($update)) {
            return response()->json(['status' => 'empty']);
        }

        try {
            $this->telegramPublicService->handleUpdate($update);
        } catch (\Exception $e) {
            Log::error("Telegram Webhook Error: " . $e->getMessage());
        }

        return response()->json(['status' => 'ok']);
    }
}
