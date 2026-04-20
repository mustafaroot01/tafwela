<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('station_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('petrol', ['available', 'limited', 'unavailable'])->nullable();
            $table->enum('diesel', ['available', 'limited', 'unavailable'])->nullable();
            $table->enum('kerosene', ['available', 'limited', 'unavailable'])->nullable();
            $table->enum('gas', ['available', 'limited', 'unavailable'])->nullable();
            $table->enum('congestion', ['low', 'medium', 'high'])->nullable();
            $table->boolean('is_admin_update')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->integer('confirmation_count')->default(0);
            $table->ipAddress('ip_address')->nullable();
            $table->string('device_id')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['station_id', 'created_at']);
            $table->index(['is_verified', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('station_updates');
    }
};
