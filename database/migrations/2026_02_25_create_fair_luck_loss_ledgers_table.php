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
        Schema::create('fair_luck_loss_ledgers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->float('loss_score')->default(0)->comment('نقاط الخسارة');
            $table->integer('contribution_bank')->default(0)->comment('رصيد المساهمة المتراكم');
            $table->integer('jackpot_pity')->default(0)->comment('عداد الشفقة على الجاكبوت');
            $table->integer('loss_momentum')->default(0)->comment('زخم الخسارة المتتالية');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('loss_score');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fair_luck_loss_ledgers');
    }
};
