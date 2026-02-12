<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('charges')) {
            return;
        }

        if (Schema::hasColumn('charges', 'user_id')) {
            Schema::table('charges', function (Blueprint $table) {
                $table->unsignedInteger('user_id')->nullable()->change();
            });
        }

        if (! Schema::hasColumn('charges', 'agency_id')) {
            Schema::table('charges', function (Blueprint $table) {
                $table->integer('agency_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('charges', function (Blueprint $table) {
            $table->dropColumn('agency_id');
        });
    }
};
