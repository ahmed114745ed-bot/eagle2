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
        Schema::table('extra_data_in_rooms', function (Blueprint $table) {
            $table->decimal('total', 65, 2)->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: Reverting decimal precision changes requires doctrine/dbal
        // Original column definition would need to be known
    }
};
