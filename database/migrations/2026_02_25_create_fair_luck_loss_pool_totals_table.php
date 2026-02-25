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
        Schema::create('fair_luck_loss_pool_totals', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('balance')->default(0)->comment('رصيد المجموع الكلي للخسائر');
            $table->bigInteger('lifetime_contributed')->default(0)->comment('إجمالي المساهمات في العمر الكامل');
            $table->bigInteger('lifetime_withdrawn')->default(0)->comment('إجمالي السحب في العمر الكامل');
            $table->timestamps();

            $table->index('balance');
        });

        // إدراج صف افتراضي واحد فقط
        DB::table('fair_luck_loss_pool_totals')->insert([
            'balance' => 0,
            'lifetime_contributed' => 0,
            'lifetime_withdrawn' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fair_luck_loss_pool_totals');
    }
};
