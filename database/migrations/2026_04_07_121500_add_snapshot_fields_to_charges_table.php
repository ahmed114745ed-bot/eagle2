<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('charges', function (Blueprint $table) {
            if (!Schema::hasColumn('charges', 'applied_coin_rate')) {
                $table->decimal('applied_coin_rate', 15, 2)->nullable()->after('amount_type')->comment('The rate used to calculate coins for this specific row');
                $table->decimal('base_usd', 15, 2)->nullable()->after('applied_coin_rate')->comment('The base USD value of the charge');
                $table->decimal('base_coins', 15, 2)->nullable()->after('base_usd')->comment('The base coins equivalent');
                $table->decimal('bonus_coins', 15, 2)->nullable()->after('base_coins')->comment('Bonus coins applied (e.g. from commission/profit difference)');
                $table->decimal('profit_usd', 15, 2)->nullable()->after('bonus_coins')->comment('System profit in USD');
                $table->decimal('profit_coins', 15, 2)->nullable()->after('profit_usd')->comment('System profit in Coins');
                $table->decimal('total_coins', 15, 2)->nullable()->after('profit_coins')->comment('Final total coins transferred/charged');
                $table->string('rate_source', 50)->nullable()->after('total_coins')->comment('Source of the rate: unified, agent, admin_override, etc.');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('charges', function (Blueprint $table) {
            $table->dropColumn([
                'applied_coin_rate',
                'base_usd',
                'base_coins',
                'bonus_coins',
                'profit_usd',
                'profit_coins',
                'total_coins',
                'rate_source'
            ]);
        });
    }
};
