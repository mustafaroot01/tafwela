<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = [
            [
                'key' => 'privacy_policy_content',
                'value' => "سياسة الخصوصية لتطبيق تفويلة...\n\nنحن نلتزم بحماية بياناتك الشخصية...",
                'type' => 'textarea',
                'group' => 'pages',
                'label' => 'محتوى سياسة الخصوصية',
                'description' => 'النص الكامل لسياسة الخصوصية الذي يظهر في التطبيق'
            ],
            [
                'key' => 'contact_content',
                'value' => 'نحن هنا لمساعدتك، يمكنك التواصل معنا عبر القنوات التالية:',
                'type' => 'text',
                'group' => 'pages',
                'label' => 'رسالة صفحة التواصل',
                'description' => 'النص التعريفي في صفحة تواصل معنا'
            ],
            [
                'key' => 'contact_phone',
                'value' => '9647500000000',
                'type' => 'text',
                'group' => 'pages',
                'label' => 'رقم هاتف التواصل',
                'description' => 'رقم الهاتف الذي سيتم الاتصال به عند الضغط على زر الاتصال'
            ],
            [
                'key' => 'contact_whatsapp',
                'value' => '9647500000000',
                'type' => 'text',
                'group' => 'pages',
                'label' => 'رقم واتساب التواصل',
                'description' => 'رقم الواتساب للمراسلة الفورية'
            ],
            [
                'key' => 'instagram_url',
                'value' => 'https://instagram.com/tafwela',
                'type' => 'text',
                'group' => 'pages',
                'label' => 'رابط إنستغرام',
                'description' => 'رابط صفحة الإنستغرام الرسمية'
            ],
            [
                'key' => 'facebook_url',
                'value' => 'https://facebook.com/tafwela',
                'type' => 'text',
                'group' => 'pages',
                'label' => 'رابط فيسبوك',
                'description' => 'رابط صفحة الفيسبوك الرسمية'
            ],
        ];

        foreach ($settings as $setting) {
            \App\Models\AppSetting::create($setting);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\AppSetting::where('group', 'pages')->delete();
    }
};
