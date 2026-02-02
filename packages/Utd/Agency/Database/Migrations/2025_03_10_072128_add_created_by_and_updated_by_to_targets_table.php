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
        if (!Schema::hasColumn('targets', 'created_by')) {
            Schema::table('targets', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->nullable();
            });
        }
        
        if (!Schema::hasColumn('targets', 'updated_by')) {
            Schema::table('targets', function (Blueprint $table) {
                $table->unsignedBigInteger('updated_by')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('targets', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });
    }
};
