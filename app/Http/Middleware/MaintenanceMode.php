<?php

namespace App\Http\Middleware;

use App\Models\AppSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (AppSetting::get('app_maintenance', false)) {
            return response()->json([
                'message'     => 'التطبيق في وضع الصيانة حالياً. يرجى المحاولة لاحقاً.',
                'maintenance' => true,
            ], 503);
        }

        return $next($request);
    }
}
