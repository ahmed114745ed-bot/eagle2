<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Drop tables
Schema::dropIfExists('special_id_frams');
echo "dropped special_id_frams\n";

Schema::dropIfExists('user_wares');
echo "dropped user_wares\n";

Schema::dropIfExists('user_ware');
echo "dropped user_ware\n";

Schema::dropIfExists('special_id_histories');
echo "dropped special_id_histories\n";

// Drop columns
if (Schema::hasColumn('wares', 'disable')) {
    Schema::table('wares', function ($t) {
        $t->dropColumn('disable');
    });
    echo "dropped disable from wares\n";
}

if (Schema::hasColumn('wares', 'value')) {
    Schema::table('wares', function ($t) {
        $t->dropColumn('value');
    });
    echo "dropped value from wares\n";
}

if (Schema::hasColumn('users', 'special_id')) {
    Schema::table('users', function ($t) {
        $t->dropColumn('special_id');
    });
    echo "dropped special_id from users\n";
}

// Clean migrations table
DB::table('migrations')->whereIn('migration', [
    '2024_03_19_104628_add_special_id_to_users',
    '2024_03_19_104628_add_value_to_ware',
    '2024_03_19_165757_create_special_id_history_table',
    '2024_03_31_084838_create_user_ware_table',
    '2024_04_02_084838_add_disable_ware_table',
    '2024_04_02_093234_create_special_id_frams_table',
])->delete();

echo "cleaned migrations table\n";
echo "DONE - SpecialId migrations rolled back successfully!\n";
