<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;

class AppConfigController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'version'              => AppSetting::get('app_version', '1.0.0'),
            'force_update_version' => AppSetting::get('force_update_version', ''),
            'maintenance'          => AppSetting::get('app_maintenance', false),
            'allow_guest_browse'   => AppSetting::get('allow_guest_browse', true),
            'default_radius'       => (int) AppSetting::get('default_search_radius', 10),
        ]);
    }
}
