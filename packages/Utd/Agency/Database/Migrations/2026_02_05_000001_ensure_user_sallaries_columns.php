<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        if (!Schema::hasTable('user_sallaries')) {
            return;
        }

        Schema::table('user_sallaries', function (Blueprint $table) {
            if (!Schema::hasColumn('user_sallaries', 'extras')) {
                $table->text('extras')->nullable();
            }
            if (!Schema::hasColumn('user_sallaries', 'type')) {
                $table->string('type', 50)->nullable();
            }
            if (!Schema::hasColumn('user_sallaries', 'is_saved')) {
                $table->tinyInteger('is_saved')->default(0);
            }
            if (!Schema::hasColumn('user_sallaries', 'owner_pide')) {
                $table->decimal('owner_pide', 8, 2)->default(0)->nullable();
            }
            if (!Schema::hasColumn('user_sallaries', 'dB')) {
                $table->decimal('dB', 20, 4)->nullable();
            }
            if (!Schema::hasColumn('user_sallaries', 'app_profit')) {
                $table->decimal('app_profit', 20, 4)->nullable();
            }
            if (!Schema::hasColumn('user_sallaries', 'diamond')) {
                $table->string('diamond')->default('0 / 0')->nullable();
            }
            if (!Schema::hasColumn('user_sallaries', 'achieved_diamond')) {
                $table->unsignedBigInteger('achieved_diamond')->default(0);
            }
            if (!Schema::hasColumn('user_sallaries', 'achieved_days')) {
                $table->integer('achieved_days')->default(0);
            }
            if (!Schema::hasColumn('user_sallaries', 'achieved_hours')) {
                $table->integer('achieved_hours')->default(0);
            }
            if (!Schema::hasColumn('user_sallaries', 'pending_dollar')) {
                $table->double('pending_dollar')->default(0);
            }
            if (!Schema::hasColumn('user_sallaries', 'remaining_diamond')) {
                $table->unsignedBigInteger('remaining_diamond')->default(0);
            }
            if (!Schema::hasColumn('user_sallaries', 'target_id')) {
                $table->unsignedBigInteger('target_id')->nullable();
            }
            if (!Schema::hasColumn('user_sallaries', 'target_diamonds')) {
                $table->double('target_diamonds')->default(0);
            }
            if (!Schema::hasColumn('user_sallaries', 'is_finished')) {
                $table->tinyInteger('is_finished')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     * 
     * Do not drop any columns to protect data
     */
    public function down(): void
    {
        return;
    }
};
