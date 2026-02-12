<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * الجداول الإضافية للوكالات التي لم يتم تغطيتها في الـ migrations الأصلية
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. جدول دول الوكالات
        if (! Schema::hasTable('agency_countries')) {
            Schema::create('agency_countries', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('agency_id');
                $table->unsignedInteger('country_id')->default(0);
                $table->timestamps();

                $table->index('agency_id');
                $table->index('country_id');
            });
        }

        // 2. جدول مديري الوكالة من التطبيق
        if (! Schema::hasTable('agency_manger_app_dash')) {
            Schema::create('agency_manger_app_dash', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('agency_id');
                $table->unsignedBigInteger('user_id');
                $table->string('permission')->nullable();
                $table->timestamps();

                $table->index(['agency_id', 'user_id']);
            });
        }

        // 3. جدول باقات الوكالة
        if (! Schema::hasTable('agency_packs')) {
            Schema::create('agency_packs', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('agency_id');
                $table->bigInteger('user_id');
                $table->bigInteger('ware_id');
                $table->integer('count')->default(1);
                $table->timestamps();

                $table->index('agency_id');
                $table->index('user_id');
            });
        }

        // 4. جدول مكافآت الوكالة
        if (! Schema::hasTable('agency_rewards')) {
            Schema::create('agency_rewards', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('agency_id');
                $table->enum('type', ['agency_reward', 'share_rewards']);
                $table->enum('target_type', ['vip', 'ware', 'achievement']);
                $table->string('target');
                $table->integer('quantity')->default(0);
                $table->integer('available_quantity')->default(0);
                $table->integer('expire_days');
                $table->datetime('expire_at')->nullable();
                $table->timestamps();

                $table->index('agency_id');
            });
        }

        if (! Schema::hasTable('agency_transfer_salaries')) {
            Schema::create('agency_transfer_salaries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('agency_id')->default(0);
                $table->double('salary', 8, 2)->default(0);
                $table->double('cut_amount', 8, 2)->default(0);
                $table->integer('month')->default(0);
                $table->integer('year')->default(0);
                $table->integer('pending_usd')->default(0);
                $table->timestamps();

                $table->index('agency_id');
            });
        }

        // 6. جدول تغييرات مديري الوكالة
        if (! Schema::hasTable('change_agency_mangers')) {
            Schema::create('change_agency_mangers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('agency_id');
                $table->unsignedBigInteger('from_user_id');
                $table->unsignedBigInteger('to_user_id');
                $table->unsignedBigInteger('changed_by_admin_id')->nullable();
                $table->text('reason')->nullable();
                $table->timestamps();

                $table->index('agency_id');
            });
        }

        // 7. جدول شحن الوكالات
        if (! Schema::hasTable('charge_agencies')) {
            Schema::create('charge_agencies', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('agency_id')->default(0);
                $table->timestamps();

                $table->index('agency_id');
            });
        }

        // 8. جدول أهداف المستخدمين (user_target)
        if (! Schema::hasTable('user_target')) {
            Schema::create('user_target', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedInteger('agency_id')->nullable();
                $table->integer('add_month');
                $table->integer('add_year');
                $table->decimal('target_usd', 15, 2)->default(0);
                $table->decimal('target_hours', 10, 2)->default(0);
                $table->integer('target_days')->default(0);
                $table->decimal('target_agency_share', 5, 2)->default(0);
                $table->decimal('user_diamonds', 15, 2)->default(0);
                $table->decimal('user_hours', 10, 2)->default(0);
                $table->integer('user_days')->default(0);
                $table->decimal('user_obtain', 15, 2)->default(0);
                $table->decimal('agency_obtain', 15, 2)->default(0);
                $table->decimal('next_diamond', 15, 2)->default(0);
                $table->timestamps();

                $table->index('user_id');
                $table->index('agency_id');
                $table->index(['add_month', 'add_year']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_target');
        Schema::dropIfExists('charge_agencies');
        Schema::dropIfExists('change_agency_mangers');
        Schema::dropIfExists('agency_transfer_salaries');
        Schema::dropIfExists('agency_rewards');
        Schema::dropIfExists('agency_packs');
        Schema::dropIfExists('agency_manger_app_dash');
        Schema::dropIfExists('agency_countries');
    }
};
