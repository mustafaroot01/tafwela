<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('app_settings')->insert([
            [
                'group'       => 'telegram',
                'key'         => 'telegram_enabled',
                'value'       => 'false',
                'type'        => 'boolean',
                'label'       => 'تفعيل إشعارات تليجرام',
                'description' => 'إرسال إشعارات فورية عند حدوث تبليغات أو تحديثات.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'group'       => 'telegram',
                'key'         => 'telegram_bot_token',
                'value'       => '',
                'type'        => 'string',
                'label'       => 'توكن البوت (Bot Token)',
                'description' => 'التوكن الذي حصلت عليه من BotFather.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'group'       => 'telegram',
                'key'         => 'telegram_chat_id',
                'value'       => '',
                'type'        => 'string',
                'label'       => 'معرف الدردشة (Chat ID)',
                'description' => 'المعرف الخاص بالمجموعة أو القناة التي سيصل إليها الإشعار.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'group'       => 'telegram',
                'key'         => 'telegram_notify_reports',
                'value'       => 'true',
                'type'        => 'boolean',
                'label'       => 'إشعارات التبليغات',
                'description' => 'إرسال إشعار عند تقديم بلاغ جديد عن محطة.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'group'       => 'telegram',
                'key'         => 'telegram_notify_updates',
                'value'       => 'true',
                'type'        => 'boolean',
                'label'       => 'إشعارات التحديثات',
                'description' => 'إرسال إشعار عند تقديم تحديث جديد لحالة الوقود.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('app_settings')->where('group', 'telegram')->delete();
    }
};
