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
        foreach (['salary_requests', 'agent_salary_requests'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->double('applied_coin_rate', 15, 2)->nullable();
                $table->double('base_usd', 15, 2)->nullable();
                $table->double('base_coins', 15, 2)->nullable();
                $table->double('bonus_coins', 15, 2)->nullable();
                $table->double('profit_usd', 15, 2)->nullable();
                $table->double('profit_coins', 15, 2)->nullable();
                $table->double('total_coins', 15, 2)->nullable();
                $table->string('rate_source')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['salary_requests', 'agent_salary_requests'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn([
                    'applied_coin_rate',
                    'base_usd',
                    'base_coins',
                    'bonus_coins',
                    'profit_usd',
                    'profit_coins',
                    'total_coins',
                    'rate_source',
                ]);
            });
        }
    }
};
