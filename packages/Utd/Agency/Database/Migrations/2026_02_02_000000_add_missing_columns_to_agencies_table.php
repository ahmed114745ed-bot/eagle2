<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMissingColumnsToAgenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agencies', function (Blueprint $table) {
            // Add status column (in case it's missing)
            if (!Schema::hasColumn('agencies', 'status')) {
                $table->unsignedTinyInteger('status')->default(1)->after('notice')->comment('1=active, 0=inactive');
            }
            
            // Add created_by column
            if (!Schema::hasColumn('agencies', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('status')->comment('Admin user who created this agency');
            }
            
            // Add deleted_at for soft deletes
            if (!Schema::hasColumn('agencies', 'deleted_at')) {
                $table->softDeletes();
            }
            
            // Add pending_dollar column
            if (!Schema::hasColumn('agencies', 'pending_dollar')) {
                $table->decimal('pending_dollar', 15, 2)->default(0)->after('status')->comment('المبلغ المعلق بالدولار');
            }
            
            // Add coins column
            if (!Schema::hasColumn('agencies', 'coins')) {
                $table->decimal('coins', 15, 2)->default(0)->after('pending_dollar')->comment('الكوينز/العملات');
            }
            
            // Add phone_code column
            if (!Schema::hasColumn('agencies', 'phone_code')) {
                $table->string('phone_code', 10)->nullable()->after('phone')->comment('Phone country code');
            }
            
            // Add country_id column
            if (!Schema::hasColumn('agencies', 'country_id')) {
                $table->unsignedBigInteger('country_id')->nullable()->after('phone_code')->comment('Country ID');
            }
            
            // Add is_frozen column
            if (!Schema::hasColumn('agencies', 'is_frozen')) {
                $table->boolean('is_frozen')->default(0)->after('status')->comment('Is agency frozen/blocked');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agencies', function (Blueprint $table) {
            if (Schema::hasColumn('agencies', 'status')) {
                $table->dropColumn('status');
            }
            
            if (Schema::hasColumn('agencies', 'created_by')) {
                $table->dropColumn('created_by');
            }
            
            if (Schema::hasColumn('agencies', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            
            if (Schema::hasColumn('agencies', 'pending_dollar')) {
                $table->dropColumn('pending_dollar');
            }
            
            if (Schema::hasColumn('agencies', 'coins')) {
                $table->dropColumn('coins');
            }
            
            if (Schema::hasColumn('agencies', 'phone_code')) {
                $table->dropColumn('phone_code');
            }
            
            if (Schema::hasColumn('agencies', 'country_id')) {
                $table->dropColumn('country_id');
            }
            
            if (Schema::hasColumn('agencies', 'is_frozen')) {
                $table->dropColumn('is_frozen');
            }
        });
    }
}
