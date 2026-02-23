<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('weekly_stars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('editor_id')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop FK from CP package's weekly_cp_gifts if it exists
        if (Schema::hasTable('weekly_cp_gifts')) {
            $fkExists = DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'weekly_cp_gifts' 
                AND CONSTRAINT_NAME = 'weekly_cp_gifts_weekly_cp_id_foreign'
            ");
            if (!empty($fkExists)) {
                Schema::table('weekly_cp_gifts', function (Blueprint $table) {
                    $table->dropForeign(['weekly_cp_id']);
                });
            }
        }
        Schema::dropIfExists('weekly_stars');
    }
};
