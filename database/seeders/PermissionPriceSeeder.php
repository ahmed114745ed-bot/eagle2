<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admin_permissions')->insert([
            ['name' => 'edit gift price', 'slug' => 'edit_gift_price'],
            ['name' => 'add gift price', 'slug' => 'add_gift_price'],
            ['name' => 'edit ware price', 'slug' => 'edit_ware_price'],
            ['name' => 'add ware price', 'slug' => 'add_ware_price'],
            ['name' => 'edit vip price', 'slug' => 'edit_vip_price'],
            ['name' => 'add vip price', 'slug' => 'add_vip_price'],

        ]);
    }
}
