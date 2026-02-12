<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDefaultValuesForAgenciesColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('agencies', 'owner_id')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->unsignedBigInteger('owner_id')->default(0)->change();
            });
        }

        if (! Schema::hasColumn('agencies', 'Shipping_agency')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->unsignedBigInteger('Shipping_agency')->default(false);
            });
        }

        if (! Schema::hasColumn('agencies', 'Host_agency')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->unsignedBigInteger('Host_agency')->default(false);
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
            $table->dropColumn(['Shipping_agency', 'Host_agency', 'owner_id']);
        });
    }
}
