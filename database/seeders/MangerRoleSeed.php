<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MangerRoleSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin_roles = array(
            array('name' => 'Manger','slug' => 'manger',),
        );
        DB::table ('admin_roles')->insert ($admin_roles);
    }
}