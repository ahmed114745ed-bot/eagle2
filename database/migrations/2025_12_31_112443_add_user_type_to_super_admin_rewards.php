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
        // 1️⃣ Modify table FIRST
        Schema::table('super_admin_rewards', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('user_type')->default('super_admin');
        });

        // 2️⃣ THEN rename it
        Schema::rename('super_admin_rewards', 'admin_rewards');
    }

    public function down(): void
    {
        // 1️⃣ Rename BACK first
        Schema::rename('admin_rewards', 'super_admin_rewards');

        // 2️⃣ THEN drop columns
        Schema::table('super_admin_rewards', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'user_type']);
        });
    }
};
