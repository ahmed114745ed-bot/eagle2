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
        if (!Schema::hasTable('admin_users') || Schema::hasColumn('admin_users', 'transfer_salary')) return;
        Schema::table('admin_users', function (Blueprint $table) {
            $table->boolean('transfer_salary')->default(false); 
        });
    }

    public function down(): void
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->dropColumn('transfer_salary');
        });
    }
};
