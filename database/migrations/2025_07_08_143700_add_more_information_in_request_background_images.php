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
        Schema::table('request_background_images', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('id');
            $table->string('created_by_type')->nullable()->after('created_by');
            $table->unsignedBigInteger('room_id')->nullable()->after('owner_room_id');

            $table->foreign('room_id')
                ->references('id')
                ->on('rooms')
                ->onDelete('set null');

            $table->index(['created_by', 'created_by_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_background_images', function (Blueprint $table) {
            $table->dropIndex(['created_by', 'created_by_type']);
            $table->dropColumn(['created_by', 'created_by_type']);

            $table->dropForeign(['room_id']);
            $table->dropColumn('room_id');
        });
    }
};
