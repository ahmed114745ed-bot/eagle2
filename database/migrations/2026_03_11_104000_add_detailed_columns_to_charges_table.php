<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetailedColumnsToChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('charges', function (Blueprint $table) {
            $table->decimal('base_coin_rate', 18, 2)->nullable()->after('amount_type');
            $table->decimal('sent_coins', 18, 2)->nullable()->after('base_coin_rate');
            $table->decimal('extra_coins', 18, 2)->nullable()->after('sent_coins');
            $table->decimal('total_coins', 18, 2)->nullable()->after('extra_coins');
            $table->string('transaction_type')->nullable()->after('total_coins');
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
            $table->dropColumn(['base_coin_rate', 'sent_coins', 'extra_coins', 'total_coins', 'transaction_type']);
        });
    }
}
