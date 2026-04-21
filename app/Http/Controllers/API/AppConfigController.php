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

    public function pages(): JsonResponse
    {
        return response()->json([
            'privacy_policy' => [
                'title'   => 'سياسة الخصوصية',
                'content' => AppSetting::get('privacy_policy_content', 'سياسة الخصوصية الافتراضية للتطبيق...'),
            ],
            'contact' => [
                'title'    => 'تواصل معنا',
                'content'  => AppSetting::get('contact_content', 'يمكنك التواصل معنا عبر الوسائل التالية:'),
                'phone'    => AppSetting::get('contact_phone', ''),
                'whatsapp' => AppSetting::get('contact_whatsapp', ''),
            ],
            'socials' => [
                'instagram' => AppSetting::get('instagram_url', ''),
                'facebook'  => AppSetting::get('facebook_url', ''),
            ]
        ]);
    }
}
