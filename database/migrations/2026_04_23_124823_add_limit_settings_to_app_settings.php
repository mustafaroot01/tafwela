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
                'key' => 'employee_hourly_limit',
                'value' => '10',
                'type' => 'integer',
                'group' => 'stations',
                'label' => 'حد تحديثات الموظف (بالساعة)',
                'description' => 'عدد المرات التي يمكن للموظف فيها تحديث حالة محطته خلال ساعة واحدة.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'user_hourly_limit',
                'value' => '3',
                'type' => 'integer',
                'group' => 'stations',
                'label' => 'حد تحديثات المستخدم (بالساعة)',
                'description' => 'عدد المرات التي يمكن للمستخدم العادي فيها تحديث حالة أي محطة خلال ساعة واحدة.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('app_settings')->whereIn('key', ['employee_hourly_limit', 'user_hourly_limit'])->delete();
    }
};
