<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ── OTP Settings ────────────────────────────────────────
            [
                'key'         => 'otpiq_api_key',
                'value'       => '',
                'type'        => 'string',
                'group'       => 'otp',
                'label'       => 'مفتاح OTPIQ API',
                'description' => 'المفتاح السري لخدمة OTPIQ لإرسال رمز التحقق عبر واتساب',
            ],
            [
                'key'         => 'otpiq_channel',
                'value'       => 'whatsapp-sms',
                'type'        => 'string',
                'group'       => 'otp',
                'label'       => 'قناة إرسال OTP',
                'description' => 'القناة المستخدمة: whatsapp-sms (واتساب ثم SMS) أو whatsapp أو sms',
            ],
            [
                'key'         => 'otp_expiry_minutes',
                'value'       => '5',
                'type'        => 'integer',
                'group'       => 'otp',
                'label'       => 'مدة انتهاء OTP (دقيقة)',
                'description' => 'عدد الدقائق قبل انتهاء صلاحية رمز التحقق',
            ],
            [
                'key'         => 'otp_max_attempts',
                'value'       => '5',
                'type'        => 'integer',
                'group'       => 'otp',
                'label'       => 'أقصى محاولات في الساعة',
                'description' => 'عدد طلبات OTP المسموح بها لنفس الرقم في الساعة الواحدة',
            ],
            [
                'key'         => 'otp_enabled',
                'value'       => 'true',
                'type'        => 'boolean',
                'group'       => 'otp',
                'label'       => 'تفعيل إرسال OTP',
                'description' => 'عند التعطيل سيُسجَّل الكود فقط في السجلات (للتطوير)',
            ],

            // ── Station Settings ─────────────────────────────────────
            [
                'key'         => 'update_expiry_minutes',
                'value'       => '60',
                'type'        => 'integer',
                'group'       => 'stations',
                'label'       => 'مدة انتهاء التحديث (دقيقة)',
                'description' => 'المدة الزمنية التي يُعتبر فيها تحديث المستخدم صالحاً',
            ],
            [
                'key'         => 'verification_threshold',
                'value'       => '3',
                'type'        => 'integer',
                'group'       => 'stations',
                'label'       => 'عدد التأكيدات للتوثيق',
                'description' => 'عدد تأكيدات المستخدمين اللازمة لتوثيق التحديث تلقائياً',
            ],
            [
                'key'         => 'default_search_radius',
                'value'       => '10',
                'type'        => 'integer',
                'group'       => 'stations',
                'label'       => 'نطاق البحث الافتراضي (كم)',
                'description' => 'نطاق البحث عن المحطات القريبة بالكيلومتر',
            ],
            [
                'key'         => 'interaction_cooldown_minutes',
                'value'       => '60',
                'type'        => 'integer',
                'group'       => 'stations',
                'label'       => 'فترة التوقف بين التقييمات (دقيقة)',
                'description' => 'المدة التي يجب أن ينتظرها المستخدم قبل تقييم نفس المحطة مرة أخرى',
            ],
            [
                'key'         => 'osm_auto_import',
                'value'       => 'true',
                'type'        => 'boolean',
                'group'       => 'stations',
                'label'       => 'الاستيراد التلقائي من OSM',
                'description' => 'استيراد المحطات تلقائياً من OpenStreetMap عند قلة النتائج',
            ],
            [
                'key'         => 'osm_min_expected',
                'value'       => '8',
                'type'        => 'integer',
                'group'       => 'stations',
                'label'       => 'الحد الأدنى للمحطات قبل OSM',
                'description' => 'إذا كانت النتائج أقل من هذا الرقم يبدأ الاستيراد التلقائي',
            ],

            // ── App Settings ─────────────────────────────────────────
            [
                'key'         => 'app_maintenance',
                'value'       => 'false',
                'type'        => 'boolean',
                'group'       => 'app',
                'label'       => 'وضع الصيانة',
                'description' => 'عند التفعيل يُعيد API رسالة صيانة لجميع الطلبات',
            ],
            [
                'key'         => 'app_version',
                'value'       => '1.0.0',
                'type'        => 'string',
                'group'       => 'app',
                'label'       => 'إصدار التطبيق',
                'description' => 'رقم الإصدار الحالي للتطبيق',
            ],
            [
                'key'         => 'force_update_version',
                'value'       => '',
                'type'        => 'string',
                'group'       => 'app',
                'label'       => 'إصدار التحديث الإجباري',
                'description' => 'إذا كان إصدار المستخدم أقل من هذا يُجبر على التحديث',
            ],
            [
                'key'         => 'allow_guest_browse',
                'value'       => 'true',
                'type'        => 'boolean',
                'group'       => 'app',
                'label'       => 'السماح بالتصفح بدون تسجيل',
                'description' => 'السماح للزوار غير المسجلين بمشاهدة المحطات',
            ],

            // ── Notification Settings ────────────────────────────────
            [
                'key'         => 'notify_favorites_on_update',
                'value'       => 'true',
                'type'        => 'boolean',
                'group'       => 'notifications',
                'label'       => 'إشعار عند تحديث المفضلة',
                'description' => 'إرسال إشعار للمستخدمين عند تحديث حالة محطة في مفضلتهم',
            ],
            [
                'key'         => 'fcm_server_key',
                'value'       => '',
                'type'        => 'string',
                'group'       => 'notifications',
                'label'       => 'مفتاح Firebase FCM',
                'description' => 'مفتاح خادم Firebase لإرسال الإشعارات الفورية',
            ],
        ];

        foreach ($settings as $data) {
            AppSetting::updateOrCreate(['key' => $data['key']], $data);
        }
    }
}
