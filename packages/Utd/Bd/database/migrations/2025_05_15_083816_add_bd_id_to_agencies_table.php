<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('agencies')) {
            return;
        }

        if (! Schema::hasColumn('agencies', 'bd_id')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->bigInteger('bd_id')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('agencies', 'bd_id')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->dropColumn('bd_id');
            });
        }
    }
};
