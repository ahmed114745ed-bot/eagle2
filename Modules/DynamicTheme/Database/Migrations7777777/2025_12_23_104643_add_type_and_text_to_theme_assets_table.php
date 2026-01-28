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
        Schema::table('theme_assets', function (Blueprint $table) {
            $table->string('type', 50)->after('id')->default(''); 
            $table->text('text')->nullable()->after('type');    
            $table->text('max_size')->nullable();    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('theme_assets', function (Blueprint $table) {
            $table->dropColumn(['type', 'text','max_size']);
        });
    }
};
