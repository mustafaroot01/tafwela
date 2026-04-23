<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('app_settings')->insert([
            [
                'key' => 'global_update_cooldown',
                'value' => '5',
                'type' => 'integer',
                'group' => 'stations',
                'label' => 'فترة الانتظار العالمية (دقيقة)',
                'description' => 'المدة التي يجب أن ينتظرها المستخدم بين إرسال تحديث لمحطة وأخرى (لمنع السبام).',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('app_settings')->where('key', 'global_update_cooldown')->delete();
    }
};
