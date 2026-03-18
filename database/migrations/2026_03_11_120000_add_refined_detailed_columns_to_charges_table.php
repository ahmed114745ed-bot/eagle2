<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRefinedDetailedColumnsToChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('charges', function (Blueprint $table) {
            $table->string('rate_source')->nullable()->after('transaction_type');
            $table->decimal('applied_coin_rate', 18, 2)->nullable()->after('rate_source');
            $table->decimal('base_usd', 18, 2)->nullable()->after('applied_coin_rate');
            $table->decimal('base_coins', 18, 2)->nullable()->after('base_usd');
            $table->decimal('bonus_coins', 18, 2)->nullable()->after('base_coins');
            $table->decimal('profit_usd', 18, 2)->nullable()->after('bonus_coins');
            $table->decimal('profit_coins', 18, 2)->nullable()->after('profit_usd');
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
                'rate_source',
                'applied_coin_rate',
                'base_usd',
                'base_coins',
                'bonus_coins',
                'profit_usd',
                'profit_coins'
            ]);
        });
    }
}
