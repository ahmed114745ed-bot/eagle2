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
        if (! Schema::hasTable('users_joined_agencies')) {
            return;
        }

        if (Schema::hasColumn('users_joined_agencies', 'status')) {
            Schema::table('users_joined_agencies', function (Blueprint $table) {
                // Check if column exists and change it to text if it's not already
                $table->text('status')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('users_joined_agencies')) {
            return;
        }

        Schema::table('users_joined_agencies', function (Blueprint $table) {
            $table->string('status')->nullable()->change();
        });
    }
};
