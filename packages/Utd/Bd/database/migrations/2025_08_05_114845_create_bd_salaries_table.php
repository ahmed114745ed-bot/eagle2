<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bd_salaries')) {
            return;
        }

        Schema::create('bd_salaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bd_id');
            $table->decimal('salary', 20, 4)->default(0);
            $table->decimal('cut_amount', 20, 4)->default(0);
            $table->bigInteger('month');
            $table->bigInteger('year');
            $table->timestamps();

            $table->index('bd_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bd_salaries');
    }
};
