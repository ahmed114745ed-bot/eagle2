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
        Schema::create('library_assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_key')->nullable();
            $table->string('asset_label')->nullable();
            $table->string('asset_type')->default('image'); // image, svga, vap, alpha, file
            $table->string('file_path')->nullable();
            $table->string('default_url')->nullable();
            $table->string('original_filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->bigInteger('size')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_assets');
    }
};
