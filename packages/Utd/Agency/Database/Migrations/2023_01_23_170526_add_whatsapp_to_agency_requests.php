<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWhatsappToAgencyRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('agency_join_requests', 'whatsapp')) {
            Schema::table('agency_join_requests', function (Blueprint $table) {
                $table->string('whatsapp')->nullable();
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
        Schema::table('agency_join_requests', function (Blueprint $table) {
            //
        });
    }
}
