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
        Schema::rename(
            'dedicate_super_admin_rewards',
            'dedicate_admin_rewards'
        );
        Schema::table('dedicate_admin_rewards', function (Blueprint $table) {
            $table->renameColumn('super_admin_id', 'admin_id');
        });
    }

    public function down(): void
    {
        Schema::rename(
            'dedicate_admin_rewards',
            'dedicate_super_admin_rewards'
        );
    }
};
