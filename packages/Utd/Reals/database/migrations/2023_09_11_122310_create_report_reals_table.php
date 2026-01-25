<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('report_reals')) {
            Schema::create('report_reals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('real_id')->constrained('reals')->onDelete('cascade');
                $table->foreignId('Reporter_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('Reported_id')->constrained('users')->onDelete('cascade');
                $table->string('description')->nullable();
                $table->string('reason')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_reals');
    }
};
