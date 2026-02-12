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
        if (! Schema::hasColumn('achievement_levels', 'ar_description')) {
            Schema::table('achievement_levels', function (Blueprint $table) {
                $table->text('ar_description')->nullable();
                $table->text('en_description')->nullable();
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
        Schema::table('achievement_levels', function (Blueprint $table) {
            $table->dropColumn(['ar_description', 'en_description']);
        });
    }
};
