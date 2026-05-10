<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bd_agency_host_sallaries')) {
            return;
        }

        Schema::create('bd_agency_host_sallaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bd_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('agency_id');
            $table->decimal('amount', 20, 4)->default(0);
            $table->decimal('salary', 20, 4)->default(0);
            $table->bigInteger('month');
            $table->bigInteger('year');
            $table->unsignedBigInteger('bd_user_id')->nullable();
            $table->timestamps();

            $table->index(['bd_id', 'agency_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bd_agency_host_sallaries');
    }
};
