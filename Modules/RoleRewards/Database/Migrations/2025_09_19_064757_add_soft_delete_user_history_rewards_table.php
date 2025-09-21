<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('user_history_rewards', function (Blueprint $table) {
            if (Schema::hasColumn('user_history_rewards', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        
            $sm = DB::select("SHOW INDEX FROM user_history_rewards WHERE Key_name = 'uniq_user_rewards'");
            if ($sm) {
                $table->dropUnique('uniq_user_rewards');
            }
        
            $table->softDeletes();
        
            $sm_new = DB::select("SHOW INDEX FROM user_history_rewards WHERE Key_name = 'uniq_user_rewards_new'");
            if (!$sm_new) {
                $table->unique(['user_id','receive_type','rewardable_id','rewardable_type','deleted_at'], 'uniq_user_rewards_new');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_history_rewards', function (Blueprint $table) {
            $table->dropUnique('uniq_user_rewards_new');

            $table->dropSoftDeletes();
            $table->unique([
                'user_id',
                'receive_type',
                'rewardable_id',
                'rewardable_type'
            ], 'uniq_user_rewards');
        });
    }
};
