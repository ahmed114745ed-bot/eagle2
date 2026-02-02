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
        if (!Schema::hasColumn('targets', 'under_edit')) {
            Schema::table('targets', function (Blueprint $table) {
                $table->boolean('under_edit')->default(false);
            });
        }
        
        if (!Schema::hasColumn('targets', 'edit_id')) {
            Schema::table('targets', function (Blueprint $table) {
                $table->unsignedBigInteger('edit_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('targets', 'under_edit')) {
            Schema::table('targets', function (Blueprint $table) {
                $table->dropColumn('under_edit');
            });
        }
        
        if (Schema::hasColumn('targets', 'edit_id')) {
            Schema::table('targets', function (Blueprint $table) {
                $table->dropColumn('edit_id');
            });
        }
    }
};
