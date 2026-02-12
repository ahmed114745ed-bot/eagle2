<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgencyJoinRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('agency_join_requests')) {
            return;
        }
        Schema::create('agency_join_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('agency_id');
            $table->unsignedTinyInteger('status')->comment('0=pending 1=accepted 2=denid')->nullable()->default(0);
            $table->unsignedBigInteger('change_status_admin_id')->comment('admin that accept or denid')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agency_join_requests');
    }
}
