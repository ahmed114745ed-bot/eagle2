<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * يعدل حقل description في جدول reals إن كان الجدول موجوداً
     */
    public function up(): void
    {
        if (Schema::hasTable('reals') && Schema::hasColumn('reals', 'description')) {
            Schema::table('reals', function (Blueprint $table) {
                $table->string('description', 500)->nullable()->default(null)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('reals') && Schema::hasColumn('reals', 'description')) {
            Schema::table('reals', function (Blueprint $table) {
                $table->string('description', 255)->change();
            });
        }
    }
};
