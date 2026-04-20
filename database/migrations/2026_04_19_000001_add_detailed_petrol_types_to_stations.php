<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('station_statuses', function (Blueprint $table) {
            $table->enum('petrol_normal', ['available', 'limited', 'unavailable'])->default('unavailable')->after('petrol');
            $table->enum('petrol_improved', ['available', 'limited', 'unavailable'])->default('unavailable')->after('petrol_normal');
            $table->enum('petrol_super', ['available', 'limited', 'unavailable'])->default('unavailable')->after('petrol_improved');
        });

        Schema::table('station_updates', function (Blueprint $table) {
            $table->enum('petrol_normal', ['available', 'limited', 'unavailable'])->nullable()->after('petrol');
            $table->enum('petrol_improved', ['available', 'limited', 'unavailable'])->nullable()->after('petrol_normal');
            $table->enum('petrol_super', ['available', 'limited', 'unavailable'])->nullable()->after('petrol_improved');
        });
    }

    public function down(): void
    {
        Schema::table('station_statuses', function (Blueprint $table) {
            $table->dropColumn(['petrol_normal', 'petrol_improved', 'petrol_super']);
        });

        Schema::table('station_updates', function (Blueprint $table) {
            $table->dropColumn(['petrol_normal', 'petrol_improved', 'petrol_super']);
        });
    }
};
