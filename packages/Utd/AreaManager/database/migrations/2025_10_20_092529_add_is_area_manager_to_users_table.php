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
        if (!Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasColumn('users', 'is_area_manager')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_area_manager')
                  ->default(false)
                  ->comment('يحدد ما إذا كان المستخدم مدير مناطق أم لا');
        });
    }

  
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_area_manager');
        });
    }
};
