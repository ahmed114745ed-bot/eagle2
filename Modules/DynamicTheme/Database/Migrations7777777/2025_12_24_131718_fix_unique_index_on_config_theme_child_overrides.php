<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('config_theme_child_overrides', function (Blueprint $table) {

            // ❌ لا تضف العمود (موجود بالفعل)

            // 1️⃣ احذف الـ unique القديم
            // $table->dropUnique('config_theme_child_unique');

            // 2️⃣ أضف الـ unique الجديد
            // $table->unique(
            //     ['configuration_id', 'config_widget_override_id', 'theme_child_id'],
            //     'config_theme_child_widget_unique'
            // );
        });
    }

    public function down(): void
    {
        Schema::table('config_theme_child_overrides', function (Blueprint $table) {

            $table->dropUnique('config_theme_child_widget_unique');

            $table->unique(
                ['configuration_id', 'theme_child_id'],
                'config_theme_child_unique'
            );
        });
    }
};
