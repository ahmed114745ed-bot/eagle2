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
        if (!Schema::hasColumn('agencies', 'old_usd')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->double('old_usd')->nullable();
            });
        }
        
        if (!Schema::hasColumn('agencies', 'target_usd')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->double('target_usd')->nullable();
            });
        }
        
        if (!Schema::hasColumn('agencies', 'target_token_usd')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->double('target_token_usd')->nullable();
            });
        }
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
