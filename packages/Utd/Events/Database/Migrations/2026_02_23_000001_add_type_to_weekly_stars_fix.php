<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('weekly_stars', 'type')) {
            Schema::table('weekly_stars', function (Blueprint $table) {
                $table->string('type')->nullable()->default('weekly_star');
            });
        }
    }

    public function down(): void
    {
        // Only drop if this migration actually added the column
        // (column wouldn't exist from earlier migrations in a rollback scenario)
    }
};
