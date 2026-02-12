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
        // Ensure all columns exist in targets table
        Schema::table('targets', function (Blueprint $table) {
            if (! Schema::hasColumn('targets', 'level')) {
                $table->unsignedInteger('level')->nullable();
            }
            if (! Schema::hasColumn('targets', 'diamonds')) {
                $table->unsignedBigInteger('diamonds')->nullable();
            }
            if (! Schema::hasColumn('targets', 'minuts')) {
                $table->unsignedBigInteger('minuts')->nullable();
            }
            if (! Schema::hasColumn('targets', 'hours')) {
                $table->unsignedBigInteger('hours')->nullable();
            }
            if (! Schema::hasColumn('targets', 'days')) {
                $table->unsignedBigInteger('days')->nullable();
            }
            if (! Schema::hasColumn('targets', 'img')) {
                $table->string('img')->nullable();
            }
            if (! Schema::hasColumn('targets', 'usd')) {
                $table->decimal('usd', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('targets', 'coin')) {
                $table->decimal('coin', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('targets', 'gold')) {
                $table->decimal('gold', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('targets', 'agency_share')) {
                $table->double('agency_share')->nullable()->default(0);
            }
            if (! Schema::hasColumn('targets', 'moment')) {
                $table->unsignedBigInteger('moment')->nullable();
            }
            if (! Schema::hasColumn('targets', 'reel')) {
                $table->unsignedBigInteger('reel')->nullable();
            }
            if (! Schema::hasColumn('targets', 'app_profit_percentage')) {
                $table->decimal('app_profit_percentage', 5, 2)->default(0);
            }
            if (! Schema::hasColumn('targets', 'db_percentage')) {
                $table->decimal('db_percentage', 5, 2)->default(0);
            }
            if (! Schema::hasColumn('targets', 'under_edit')) {
                $table->boolean('under_edit')->default(false);
            }
            if (! Schema::hasColumn('targets', 'edit_id')) {
                $table->unsignedBigInteger('edit_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // لا نحذف الأعمدة في down لأنها قد تكون موجودة مسبقاً
    }
};
