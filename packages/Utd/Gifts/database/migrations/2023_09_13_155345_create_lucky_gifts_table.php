<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lucky_gifts')) {
            Schema::create('lucky_gifts', function (Blueprint $table) {
                $table->id();
                $table->integer('gift_id')->unsigned()->index()->nullable();
                $table->foreign('gift_id')->references('id')->on('gifts')->onUpdate('cascade')->onDelete('cascade');
                $table->integer('win_probability')->default(10);
                $table->text('min_percentage')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lucky_gifts');
    }
};
