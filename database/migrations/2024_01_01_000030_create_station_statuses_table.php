<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('station_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained()->cascadeOnDelete();
            $table->enum('petrol', ['available', 'limited', 'unavailable'])->default('unavailable');
            $table->enum('diesel', ['available', 'limited', 'unavailable'])->default('unavailable');
            $table->enum('kerosene', ['available', 'limited', 'unavailable'])->default('unavailable');
            $table->enum('gas', ['available', 'limited', 'unavailable'])->default('unavailable');
            $table->enum('congestion', ['low', 'medium', 'high'])->default('low');
            $table->enum('source', ['admin', 'verified_users', 'unverified_users'])->default('unverified_users');
            $table->timestamp('last_updated_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('station_statuses');
    }
};
