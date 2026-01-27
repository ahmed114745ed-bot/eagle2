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
        if (!Schema::hasColumn('targets', 'reel')) {
            Schema::table('targets', function (Blueprint $table) {
                $table->string('reel')->nullable()->default('0,0,0');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('targets', 'reel')) {
            Schema::table('targets', function (Blueprint $table) {
                $table->dropColumn('reel');
            });
        }
    }
};
