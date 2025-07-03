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
            $table->foreignId('kicked_by_app')->nullable()->constrained('users')->cascadeOnUpdate();
            $table->foreignId('kicked_by_admin')->nullable()->constrained('admin_users')->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_joined_agencies', function (Blueprint $table) {
            //
        });
    }
};
