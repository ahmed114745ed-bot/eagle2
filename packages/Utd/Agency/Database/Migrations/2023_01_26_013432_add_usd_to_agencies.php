<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsdToAgencies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agencies', function (Blueprint $table) {
            if (!Schema::hasColumn('agencies', 'old_usd')) {
                $table->double('old_usd')->nullable();
            }
            if (!Schema::hasColumn('agencies', 'target_usd')) {
                $table->double('target_usd')->nullable();
            }
            if (!Schema::hasColumn('agencies', 'target_token_usd')) {
                $table->double('target_token_usd')->nullable();
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
        Schema::table('agencies', function (Blueprint $table) {
            //
        });
    }
}
