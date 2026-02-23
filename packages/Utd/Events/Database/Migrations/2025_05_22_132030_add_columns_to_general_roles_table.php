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
        if (!Schema::hasColumn('general_roles', 'desc_tr')) {
            Schema::table('general_roles', function (Blueprint $table) {
                $table->text('desc_tr')->nullable();
            });
        }
        if (!Schema::hasColumn('general_roles', 'desc_hi')) {
            Schema::table('general_roles', function (Blueprint $table) {
                $table->text('desc_hi')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('general_roles', 'desc_tr')) {
            Schema::table('general_roles', function (Blueprint $table) {
                $table->dropColumn('desc_tr');
            });
        }
        if (Schema::hasColumn('general_roles', 'desc_hi')) {
            Schema::table('general_roles', function (Blueprint $table) {
                $table->dropColumn('desc_hi');
            });
        }
    }
};
