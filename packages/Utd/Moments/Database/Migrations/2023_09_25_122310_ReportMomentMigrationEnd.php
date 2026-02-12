<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReportMomentMigrationEnd extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('report_moments')) {
            Schema::create('report_moments', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('moment_id');
                $table->bigInteger('Reporter_id');
                $table->bigInteger('Reported_id');
                $table->string('description');
                $table->string('type');
                $table->timestamps();
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
        Schema::dropIfExists('report_moments');

    }
}
