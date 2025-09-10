<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('user_code_invitations', function (Blueprint $table) {
            $table->integer('host_reward')->default(0)->comment('الكوينز التي حصل عليها الداعي');
            $table->integer('invitee_reward')->default(0)->comment('الكوينز التي حصل عليها المدعو');
            $table->boolean('host_received')->default(false)->comment('حالة استلام الداعي للمكافأة');
        });
    }
    
    public function down()
    {
        Schema::table('user_code_invitations', function (Blueprint $table) {
            $table->dropColumn(['host_reward', 'invitee_reward', 'host_received']);
        });
    }
    
};
