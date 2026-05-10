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
        if (! Schema::hasTable('agencies')) {
            Schema::create('agencies', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('app_owner_id')->comment('owner user id');
                $table->unsignedBigInteger('owner_id')->nullable()->comment('dashboard admin owner id');
                $table->unsignedBigInteger('agency_manger_id')->nullable()->comment('agency manager user id');
                $table->string('name');
                $table->string('notice')->nullable();
                $table->string('phone')->nullable();
                $table->string('phone_code')->nullable();
                $table->string('img')->nullable();
                $table->tinyInteger('type')->default(1)->comment('1: host, 2: shipping');
                $table->decimal('target_usd', 15, 2)->default(0);
                $table->boolean('is_frozen')->default(false);
                $table->integer('bd_id')->nullable();
                $table->unsignedBigInteger('country_id')->nullable();
                $table->unsignedBigInteger('region_id')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('app_owner_id');
                $table->index('type');
                $table->index('bd_id');
            });
        }

        // 2. جدول رواتب المستخدمين
        if (! Schema::hasTable('user_sallaries')) {
            Schema::create('user_sallaries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->default(0);
                $table->unsignedBigInteger('user_agency_id')->default(0);
                $table->string('hours')->default('0/0');
                $table->string('days')->default('0/0');
                $table->float('sallary', 20, 2)->default(0);
                $table->float('agency_sallary', 20, 2)->default(0);
                $table->float('cut_amount', 20, 2)->default(0);
                $table->integer('month')->default(0);
                $table->integer('year')->default(0);
                $table->boolean('is_paid')->default(0);
                $table->timestamps();

                $table->index('user_id');
                $table->index('user_agency_id');
            });
        }

        // 3. جدول رواتب الوكالات
        if (! Schema::hasTable('agency_sallaries')) {
            Schema::create('agency_sallaries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('agency_id')->default(0);
                $table->decimal('agency_sallary', 15, 6)->default(0);
                $table->float('sallary', 20, 2)->default(0);
                $table->float('salary', 15, 2)->default(0);
                $table->float('cut_amount', 20, 2)->default(0);
                $table->integer('add_month')->default(0);
                $table->integer('add_year')->default(0);
                $table->integer('month')->default(0);
                $table->integer('year')->default(0);
                $table->boolean('is_paid')->default(0);
                $table->timestamps();

                $table->index('agency_id');
            });
        }

        if (! Schema::hasTable('agency_join_requests')) {
            Schema::create('agency_join_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('agency_id');
                $table->unsignedTinyInteger('status')->default(0)->comment('0=pending 1=accepted 2=denied');
                $table->unsignedBigInteger('change_status_admin_id')->nullable()->default(0);
                $table->string('whatsapp')->nullable();
                $table->timestamps();

                $table->index('user_id');
                $table->index('agency_id');
                $table->index('status');
            });
        }

        if (! Schema::hasTable('targets')) {
            Schema::create('targets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('agency_id')->nullable();
                $table->decimal('target_usd', 15, 2)->default(0);
                $table->decimal('target_hours', 10, 2)->default(0);
                $table->integer('target_days')->default(0);
                $table->decimal('target_agency_share', 5, 2)->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('agency_manger_deleteds')) {
            Schema::create('agency_manger_deleteds', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('agency_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('deleted_by_admin_id')->nullable();
                $table->text('reason')->nullable();
                $table->timestamps();

                $table->index('agency_id');
            });
        }

        if (! Schema::hasTable('agency_manger_pulling_out')) {
            Schema::create('agency_manger_pulling_out', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('agency_id');
                $table->unsignedBigInteger('user_id');
                $table->decimal('amount', 15, 2)->default(0);
                $table->string('status')->default('pending');
                $table->timestamps();

                $table->index('agency_id');
            });
        }

        if (! Schema::hasTable('percentage_agency_manger')) {
            Schema::create('percentage_agency_manger', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('agency_id');
                $table->decimal('percentage', 5, 2)->default(0);
                $table->timestamps();

                $table->index('agency_id');
            });
        }

        if (! Schema::hasTable('additional_infos')) {
            Schema::create('additional_infos', function (Blueprint $table) {
                $table->id();
                $table->morphs('infoable');
                $table->string('key');
                $table->text('value')->nullable();
                $table->integer('status')->default(1);
                $table->unsignedBigInteger('owner_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('agency_host_invites')) {
            Schema::create('agency_host_invites', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('agency_id');
                $table->unsignedBigInteger('user_invite_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->integer('status')->default(0);
                $table->timestamps();

                $table->index('user_invite_id');
                $table->index('user_id');
                $table->index('agency_id');
            });
        }

        if (! Schema::hasTable('leave_agency_requests')) {
            Schema::create('leave_agency_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('agency_id');
                $table->unsignedInteger('user_id');
                $table->unsignedInteger('admin_id');
                $table->unsignedInteger('status')->nullable();
                $table->timestamps();

                $table->index('agency_id');
                $table->index('user_id');
            });
        }

        if (! Schema::hasTable('agency_user_jobs')) {
            Schema::create('agency_user_jobs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('agency_id');
                $table->unsignedBigInteger('user_id');
                $table->string('job_type')->comment('admin, moderator, etc');
                $table->timestamps();

                $table->index(['agency_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('users_joined_agencies')) {
            Schema::create('users_joined_agencies', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('agency_id');
                $table->unsignedBigInteger('user_id');
                $table->integer('type')->comment('1:owner, 2:host');
                $table->datetime('join_date');
                $table->datetime('leave_date')->nullable();
                $table->unsignedBigInteger('kicked_by_app_id')->nullable();
                $table->unsignedBigInteger('kicked_by_admin_id')->nullable();
                $table->tinyInteger('status')->default(1)->comment('1:active, 0:inactive');
                $table->timestamps();

                $table->index('agency_id');
                $table->index('user_id');
                $table->index(['agency_id', 'user_id', 'leave_date']);
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'agency_id')) {
                    $table->unsignedInteger('agency_id')->nullable()->default(0);
                }
                if (! Schema::hasColumn('users', 'type_user')) {
                    $table->integer('type_user')->default(0)->comment('0:normal, 1:owner, 2:admin, 3:host, 4:professional');
                }
                if (! Schema::hasColumn('users', 'is_manger')) {
                    $table->boolean('is_manger')->default(false);
                }
                if (! Schema::hasColumn('users', 'is_host')) {
                    $table->unsignedTinyInteger('is_host')->nullable()->default(0);
                }
            });
        }

        // 18. إضافة agency_id في جدول gift_logs
        if (Schema::hasTable('gift_logs')) {
            Schema::table('gift_logs', function (Blueprint $table) {
                if (! Schema::hasColumn('gift_logs', 'agency_id')) {
                    $table->unsignedInteger('agency_id')->nullable();
                }
            });
        }

        // 19. إضافة agency_id في جدول charges
        if (Schema::hasTable('charges')) {
            Schema::table('charges', function (Blueprint $table) {
                if (! Schema::hasColumn('charges', 'agency_id')) {
                    $table->integer('agency_id')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_joined_agencies');
        Schema::dropIfExists('agency_user_jobs');
        Schema::dropIfExists('leave_agency_requests');
        Schema::dropIfExists('agency_host_invites');
        Schema::dropIfExists('additional_infos');
        Schema::dropIfExists('percentage_agency_manger');
        Schema::dropIfExists('agency_manger_pulling_out');
        Schema::dropIfExists('agency_manger_deleteds');
        Schema::dropIfExists('targets');
        Schema::dropIfExists('agency_join_requests');
        Schema::dropIfExists('agency_sallaries');
        Schema::dropIfExists('user_sallaries');
        Schema::dropIfExists('agencies');
    }
};
