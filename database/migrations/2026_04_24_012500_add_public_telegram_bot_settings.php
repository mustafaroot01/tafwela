<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $settings = [
            [
                'group'       => 'telegram_public',
                'key'         => 'public_bot_enabled',
                'value'       => '0',
                'type'        => 'boolean',
                'label'       => 'تمكين البوت العام للمجموعات',
                'description' => 'تفعيل البوت الثاني الذي يتفاعل مع المستخدمين في المجموعات',
            ],
            [
                'group'       => 'telegram_public',
                'key'         => 'public_bot_token',
                'value'       => '',
                'type'        => 'string',
                'label'       => 'توكن البوت العام',
                'description' => 'التوكن الخاص بالبوت الثاني (Public Bot)',
            ],
            [
                'group'       => 'telegram_public',
                'key'         => 'public_bot_username',
                'value'       => '',
                'type'        => 'string',
                'label'       => 'يوزر البوت العام',
                'description' => 'اسم المستخدم الخاص بالبوت بدون @',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('app_settings')->updateOrInsert(
                ['group' => $setting['group'], 'key' => $setting['key']],
                $setting
            );
        }
    }

    public function down()
    {
        DB::table('app_settings')->where('group', 'telegram_public')->delete();
    }
};
