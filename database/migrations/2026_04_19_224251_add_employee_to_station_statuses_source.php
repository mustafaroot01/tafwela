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
        Schema::table('station_statuses', function (Blueprint $table) {
            $table->enum('source', ['admin', 'employee', 'verified_users', 'unverified_users'])->default('unverified_users')->change();
        });
    }

    public function down(): void
    {
        Schema::table('station_statuses', function (Blueprint $table) {
            $table->enum('source', ['admin', 'verified_users', 'unverified_users'])->default('unverified_users')->change();
        });
    }
};
