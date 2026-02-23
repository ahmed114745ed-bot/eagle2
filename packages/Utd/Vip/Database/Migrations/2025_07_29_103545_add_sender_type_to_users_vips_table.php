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
        Schema::table('users_vips', function (Blueprint $table) {
            if (! Schema::hasColumn('users_vips', 'sender_type')) {
                $table->string('sender_type')->nullable()->after('sender_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_vips', function (Blueprint $table) {
            if (Schema::hasColumn('users_vips', 'sender_type')) {
                $table->dropColumn('sender_type');
            }
        });
    }
};
