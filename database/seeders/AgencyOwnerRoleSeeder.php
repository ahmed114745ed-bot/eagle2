<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AgencyOwnerRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin_roles = array(
            array('name' => 'Agency Owner','slug' => 'agency-owner',),
        );
        DB::table ('admin_roles')->insert ($admin_roles);
    }
}