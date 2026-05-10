<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bd_sallaries')) {
            return;
        }

        Schema::create('bd_sallaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bd_id')->default(0);
            $table->unsignedBigInteger('agency_id')->default(0);
            $table->decimal('sallary', 20, 4)->default(0);
            $table->decimal('cut_amount', 20, 4)->default(0);
            $table->integer('month')->default(0);
            $table->integer('year')->default(0);
            $table->boolean('is_paid')->default(0);
            $table->decimal('total_agency_sallary', 20, 4)->default(0);
            $table->decimal('total_users_sallary', 20, 4)->default(0);
            $table->decimal('total_diamond', 15, 2)->default(0);
            $table->timestamps();

            $table->index('bd_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bd_sallaries');
    }
};
