<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theme_children', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('theme_id'); 
            $table->string('child_key');
            $table->string('child_type');
            $table->string('label');
            $table->integer('order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_active')->default(false);
            $table->string('action')->nullable();
            $table->enum('position', ['left','right'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_children');
    }
};
