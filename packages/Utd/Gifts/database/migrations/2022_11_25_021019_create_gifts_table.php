<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('gifts')) {
            Schema::create('gifts', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->string('e_name')->nullable();
                $table->unsignedTinyInteger('type')->default(1)->nullable();
                $table->unsignedInteger('hot')->nullable();
                $table->unsignedTinyInteger('is_play')->nullable();
                $table->integer('price')->default(0)->nullable();
                $table->string('img')->nullable();
                $table->string('show_img')->nullable();
                $table->string('show_img2')->nullable();
                $table->bigInteger('sort')->nullable();
                $table->unsignedTinyInteger('enable')->default(1)->nullable();
                $table->boolean('music_gift')->default(false);
                $table->boolean('international_gift')->default(false);
                $table->string('image_type')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->unsignedInteger('gift_category_id')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('o_vips') && Schema::hasTable('gifts') && ! Schema::hasColumn('gifts', 'vip_level')) {
            Schema::table('gifts', function (Blueprint $table) {
                $table->unsignedTinyInteger('vip_level')->default(0)->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gifts');
    }
};
