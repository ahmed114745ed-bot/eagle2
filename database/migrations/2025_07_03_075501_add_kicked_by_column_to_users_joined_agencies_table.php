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
        Schema::table('users_joined_agencies', function (Blueprint $table) {
            $table->foreignId('kicked_by_app')->nullable();
            $table->foreignId('kicked_by_admin')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_joined_agencies', function (Blueprint $table) {
            $table->dropColumn(['kicked_by_app', 'kicked_by_admin']);
        });
    }
};
