<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            if (Schema::hasColumn('user_history_rewards', 'is_deleted')) {
                return;
            }
            Schema::table('user_history_rewards', function (Blueprint $table) {
                $table->boolean('is_deleted')->default(false)->after('sub_type')->comment('Soft delete flag');
            });
        }
    
        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::table('user_history_rewards', function (Blueprint $table) {
                $table->dropColumn('is_deleted');
            });
        }
    
};
