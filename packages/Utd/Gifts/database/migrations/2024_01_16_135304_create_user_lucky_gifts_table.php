<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_lucky_gifts')) {
            Schema::create('user_lucky_gifts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->foreignId('gift_id')->nullable();
                $table->boolean('type')->default(0);
                $table->double('value')->default(0);
                $table->bigInteger('number')->default(0);
                $table->double('gift_price')->default(0);
                $table->bigInteger('total_win')->default(0);
                $table->bigInteger('total_num_win')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_lucky_gifts');
    }
};
