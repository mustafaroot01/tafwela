<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add dispute_count to station_updates
        Schema::table('station_updates', function (Blueprint $table) {
            $table->integer('dispute_count')->default(0)->after('confirmation_count');
        });

        // 2. Create interactions table to prevent duplicate votes
        Schema::create('update_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('station_update_id')->constrained('station_updates')->cascadeOnDelete();
            $table->enum('type', ['confirm', 'dispute']);
            $table->timestamps();

            $table->unique(['user_id', 'station_update_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('update_interactions');
        Schema::table('station_updates', function (Blueprint $table) {
            $table->dropColumn('dispute_count');
        });
    }
};
