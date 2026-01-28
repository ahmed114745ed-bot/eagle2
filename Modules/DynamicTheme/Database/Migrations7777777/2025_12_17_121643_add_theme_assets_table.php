<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('theme_assets', function (Blueprint $table) {
            $table->foreignId('child_id')->nullable()->after('id')->constrained('theme_children')->onDelete('cascade');

        });
    }

    public function down(): void
    {
        Schema::table('theme_assets', function (Blueprint $table) {
            $table->dropForeign(['child_id']);
            $table->dropColumn('child_id');
        });
    }
};
