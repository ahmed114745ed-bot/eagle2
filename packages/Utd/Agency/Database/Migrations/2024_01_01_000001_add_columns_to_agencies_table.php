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
        // هذا الجدول موجود بالفعل في المشروع
        // هذا الـ migration فقط للتأكد من وجود الأعمدة المطلوبة
        
        if (Schema::hasTable('agencies')) {
            Schema::table('agencies', function (Blueprint $table) {
                if (!Schema::hasColumn('agencies', 'type')) {
                    $table->tinyInteger('type')->default(1)->comment('1: host, 2: shipping');
                }
                if (!Schema::hasColumn('agencies', 'is_frozen')) {
                    $table->boolean('is_frozen')->default(false);
                }
                if (!Schema::hasColumn('agencies', 'phone_code')) {
                    $table->string('phone_code')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // لا حاجة للتراجع عن هذه التعديلات
    }
};
