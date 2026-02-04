<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only change if column exists and is not already string type
        if (Schema::hasColumn('targets', 'reel')) {
            $columnType = DB::select("SHOW COLUMNS FROM targets WHERE Field = 'reel'");
            if (!empty($columnType) && strpos($columnType[0]->Type, 'varchar') === false) {
                Schema::table('targets', function (Blueprint $table) {
                    $table->string('reel')->nullable()->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // لا نعكس التغييرات للحفاظ على سلامة البيانات
    }
};
