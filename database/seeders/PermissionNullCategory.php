<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Config;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Events\Entities\Winner;
use Modules\Events\Entities\PkEvent;
use Modules\Events\Entities\PkWinner;
use Modules\Events\Entities\WeeklyStar;

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
