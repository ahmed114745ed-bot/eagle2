<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('gift_categories')) {
            Schema::create('gift_categories', function (Blueprint $table) {
                $table->id();
                $table->json('title');
                $table->string('type');
                $table->bigInteger('sort')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_categories');
    }
};
