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
        Schema::table('gift_logs', function (Blueprint $table) {
            $table->unsignedDecimal('total', 12, 2)->nullable()->comment('سعر الهدية الاصلي المرسله');
        });
    }

    public function down(): void
    {
        Schema::table('gift_logs', function (Blueprint $table) {
            $table->dropColumn('total');
        });
    }
};
