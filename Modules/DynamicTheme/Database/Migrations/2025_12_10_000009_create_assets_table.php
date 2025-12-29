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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Asset name');
            $table->string('original_name')->comment('Original filename');
            $table->string('file_path')->comment('Storage path');
            $table->string('file_url')->comment('Public URL');
            $table->enum('asset_type', ['image', 'svga', 'vap', 'alpha'])->default('image');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0)->comment('File size in bytes');
            $table->json('metadata')->nullable()->comment('Additional metadata: dimensions, duration, etc.');
            $table->timestamps();

            $table->index('asset_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
