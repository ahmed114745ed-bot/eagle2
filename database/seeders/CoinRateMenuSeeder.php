<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoinRateMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Roles fetch
        $superAdminRole = DB::table('admin_roles')->where('slug', 'super-admin')->first();
        $areaManagerRole = DB::table('admin_roles')->where('slug', 'area-manager')->first();

        if (!$superAdminRole || !$areaManagerRole) {
            $this->command->error('Roles super-admin or area-manager not found. Please check your roles table.');
            return;
        }

        // 2. Create Permissions
        $permissionSlug = 'coin-rate-settings';
        $permissionId = DB::table('admin_permissions')->where('slug', $permissionSlug)->value('id');

        if (!$permissionId) {
            $permissionId = DB::table('admin_permissions')->insertGetId([
                'name'        => 'Coin Rate Settings',
                'slug'        => $permissionSlug,
                'http_method' => '',
                'http_path'   => "/areaManager/coin-rate-settings*\r\n/superadmin/coin-rate-settings*",
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // Attach Permissions to Roles
        foreach ([$superAdminRole->id, $areaManagerRole->id] as $roleId) {
            DB::table('admin_role_permissions')->updateOrInsert([
                'role_id'       => $roleId,
                'permission_id' => $permissionId,
            ]);
        }

        // 3. Create Menus
        $menuTitle = 'اعدادت الكوينز';
        
        // Area Manager Menu
        $areaMenuUri = 'areaManager/coin-rate-settings';
        $areaMenuId = DB::table('admin_menu')->where('uri', $areaMenuUri)->value('id');
        if (!$areaMenuId) {
            $areaMenuId = DB::table('admin_menu')->insertGetId([
                'parent_id'  => 0,
                'order'      => 99,
                'title'      => $menuTitle,
                'icon'       => 'fa-money',
                'uri'        => $areaMenuUri,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // Super Admin Menu
        $superMenuUri = 'superadmin/coin-rate-settings';
        $superMenuId = DB::table('admin_menu')->where('uri', $superMenuUri)->value('id');
        if (!$superMenuId) {
            $superMenuId = DB::table('admin_menu')->insertGetId([
                'parent_id'  => 0,
                'order'      => 99,
                'title'      => $menuTitle,
                'icon'       => 'fa-money',
                'uri'        => $superMenuUri,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4. Attach Roles to Menus
        DB::table('admin_role_menu')->updateOrInsert([
            'role_id' => $areaManagerRole->id,
            'menu_id' => $areaMenuId,
        ]);

        DB::table('admin_role_menu')->updateOrInsert([
            'role_id' => $superAdminRole->id,
            'menu_id' => $superMenuId,
        ]);

        $this->command->info('Coin Rate Menu and Permissions Seeded Successfully!');
    }
}
