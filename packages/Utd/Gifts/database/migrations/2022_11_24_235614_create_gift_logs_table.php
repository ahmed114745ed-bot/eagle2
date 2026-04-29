<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('gift_logs')) {
            Schema::create('gift_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->uuid('batch_uuid')->nullable()->index();
                $table->unsignedTinyInteger('type')->default(2)->nullable();
                $table->integer('giftId')->index();
                $table->string('giftName')->nullable();
                $table->unsignedBigInteger('giftNum');
                $table->unsignedDecimal('giftPrice', 65, 2);
                $table->unsignedInteger('sender_id');
                $table->unsignedInteger('receiver_id')->index();
                $table->unsignedTinyInteger('is_play')->default(2)->nullable();
                $table->unsignedDecimal('platform_obtain', 12, 2)->nullable();
                $table->decimal('receiver_obtain', 65, 2)->nullable();
                $table->integer('union_id')->nullable();
                $table->boolean('Summited')->default(false);
                $table->string('source_type')->nullable();
                $table->boolean('is_finished')->default(0);
                $table->timestamps();

                $table->index(['sender_id', 'created_at', 'giftPrice']);
                $table->index(['receiver_id', 'created_at', 'giftPrice']);
            });
        }

        if (Schema::hasTable('rooms') && Schema::hasTable('gift_logs') && ! Schema::hasColumn('gift_logs', 'roomowner_id')) {
            Schema::table('gift_logs', function (Blueprint $table) {
                $table->integer('roomowner_id')->index();
                $table->unsignedBigInteger('roomowner_obtain')->nullable();
                $table->boolean('room_gift_status')->default(false);
                $table->index(['roomowner_id', 'created_at', 'giftPrice']);
            });
        }

        if (Schema::hasTable('room_booms') && Schema::hasTable('gift_logs') && ! Schema::hasColumn('gift_logs', 'room_boom_uuid')) {
            Schema::table('gift_logs', function (Blueprint $table) {
                $table->uuid('room_boom_uuid')->nullable()->index();
                $table->tinyInteger('room_boom_level')->nullable();
                $table->boolean('start_boom_ranking')->default(0);
            });
        }

        if (Schema::hasTable('families') && Schema::hasTable('gift_logs') && ! Schema::hasColumn('gift_logs', 'sender_family_id')) {
            Schema::table('gift_logs', function (Blueprint $table) {
                $table->unsignedInteger('sender_family_id')->nullable();
                $table->unsignedInteger('receiver_family_id')->nullable();
            });
        }

        if (Schema::hasTable('agencies') && Schema::hasTable('gift_logs') && ! Schema::hasColumn('gift_logs', 'agency_id')) {
            Schema::table('gift_logs', function (Blueprint $table) {
                $table->unsignedInteger('agency_id')->nullable()->index();
                $table->decimal('agency_obtain', 14, 2, true)->nullable()->default(0);
            });
        }

        if (Schema::hasTable('rooms') && Schema::hasTable('gift_logs') && ! Schema::hasColumn('gift_logs', 'room_id')) {
            Schema::table('gift_logs', function (Blueprint $table) {
                $table->unsignedInteger('room_id')->nullable();
            });
        }

        if (Schema::hasTable('reals') && Schema::hasTable('gift_logs') && ! Schema::hasColumn('gift_logs', 'real_id')) {
            Schema::table('gift_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('real_id')->nullable()->index();
            });
        }

        if (Schema::hasTable('moment') && Schema::hasTable('gift_logs') && ! Schema::hasColumn('gift_logs', 'moent_id')) {
            Schema::table('gift_logs', function (Blueprint $table) {
                $table->foreignId('moent_id')->nullable()->constrained('moment')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('gift_logs') && Schema::hasColumn('gift_logs', 'app_profit_coins') && ! collect(Schema::getIndexes('gift_logs'))->contains(fn ($i) => in_array('app_profit_coins', $i['columns']))) {
            Schema::table('gift_logs', function (Blueprint $table) {
                $table->index('app_profit_coins');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_logs');
    }
};
