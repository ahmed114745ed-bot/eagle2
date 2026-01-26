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
        if (!Schema::hasColumn('users', 'reel_following_type')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string("reel_following_type")->nullable()->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'reel_following_type')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('reel_following_type');
            });
        }
    }
};
