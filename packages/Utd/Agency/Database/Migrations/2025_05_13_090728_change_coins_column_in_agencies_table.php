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
        // First check if column exists, if not create it
        if (! Schema::hasColumn('agencies', 'coins')) {
            Schema::table('agencies', function (Blueprint $table) {
                // Add after pending_dollar if it exists, otherwise just add it
                if (Schema::hasColumn('agencies', 'pending_dollar')) {
                    $table->unsignedBigInteger('coins')->default(0)->after('pending_dollar');
                } else {
                    $table->unsignedBigInteger('coins')->default(0);
                }
            });
        } else {
            // Only change type if column exists
            Schema::table('agencies', function (Blueprint $table) {
                $table->unsignedBigInteger('coins')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            //
        });
    }
};
