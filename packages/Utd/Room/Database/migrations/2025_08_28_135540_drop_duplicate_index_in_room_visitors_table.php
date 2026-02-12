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
        // Check if foreign key exists and drop it first
        try {
            Schema::table('room_visitors', function (Blueprint $table) {
                $table->dropForeign(['room_id']);
            });
        } catch (Exception $e) {
            // Foreign key may not exist
        }

        // Then drop the index if it exists
        try {
            Schema::table('room_visitors', function (Blueprint $table) {
                $table->dropIndex('room_visitors_room_id_index');
            });
        } catch (Exception $e) {
            // Index may not exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_visitors', function (Blueprint $table) {
            $table->index('room_id', 'room_visitors_room_id_index');
        });
    }
};
