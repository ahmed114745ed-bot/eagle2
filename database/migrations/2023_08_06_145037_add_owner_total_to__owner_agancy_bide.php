<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOwnerTotalToOwnerAgancyBide extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('OwnerAgancyBide')) {
            Schema::table('OwnerAgancyBide', function (Blueprint $table) {
                if (!Schema::hasColumn('OwnerAgancyBide', 'total')) {
                    $table->unsignedBigInteger('total')->default(0);
                }
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
        if (Schema::hasTable('OwnerAgancyBide')) {
            Schema::table('OwnerAgancyBide', function (Blueprint $table) {
                if (Schema::hasColumn('OwnerAgancyBide', 'total')) {
                    $table->dropColumn('total');
                }
            });
        }
    }
}
