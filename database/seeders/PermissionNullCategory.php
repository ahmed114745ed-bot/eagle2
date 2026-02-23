<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Config;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Utd\Events\Entities\Winner;
use Utd\Pk\Entities\PkEvent;
use Utd\Pk\Entities\PkWinner;
use Utd\Events\Entities\WeeklyStar;

class PermissionNullCategory extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $permissionExists = DB::table('admin_permissions')->where('category', null)->update(['category' => 'general', 'updated_at' => now(),]);
    }
}
