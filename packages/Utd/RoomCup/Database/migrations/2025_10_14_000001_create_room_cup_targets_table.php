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
        if (!Schema::hasTable('room_cup_targets')) {
            Schema::create('room_cup_targets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('total')->default(0);
                $table->bigInteger('number_of_visitors')->default(0);
                $table->bigInteger('number_of_admins')->default(0);
                $table->decimal('owner_profit', 8, 2)->default(0);
                $table->decimal('admin_profit', 8, 2)->default(0);
                $table->decimal('total_profit', 8, 2)->default(0);
                $table->integer('owner_percentage')->default(0);
                $table->integer('admin_percentage')->default(0);
                $table->timestamps();

                $table->index('number_of_visitors');
                $table->index('number_of_admins');
                $table->index('owner_profit');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_cup_targets');
    }
};
