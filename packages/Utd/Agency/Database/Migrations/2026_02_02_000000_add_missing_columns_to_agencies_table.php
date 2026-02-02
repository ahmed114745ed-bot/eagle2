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
            
            if (Schema::hasColumn('agencies', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            
            if (Schema::hasColumn('agencies', 'pending_dollar')) {
                $table->dropColumn('pending_dollar');
            }
            
            if (Schema::hasColumn('agencies', 'coins')) {
                $table->dropColumn('coins');
            }
        });
    }
}
