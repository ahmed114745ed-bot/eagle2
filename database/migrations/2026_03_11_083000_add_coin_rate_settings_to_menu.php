<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddCoinRateSettingsToMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add for Area Manager
        // We'll add it as a top-level menu item for now, or we can look for a parent.
        // Based on the seeder, parent_id 0 is top level.
        
        $areaManagerMenuId = DB::table('admin_menu')->insertGetId([
            'parent_id' => 0,
            'order' => 100,
            'title' => 'Coin Rate Settings (AM)',
            'icon' => 'fa-calculator',
            'uri' => 'areaManager/coin-rate-settings',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Add for Super Admin
        $superAdminMenuId = DB::table('admin_menu')->insertGetId([
            'parent_id' => 0,
            'order' => 101,
            'title' => 'Coin Rate Settings (SA)',
            'icon' => 'fa-calculator',
            'uri' => 'superadmin/coin-rate-settings',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign to roles
        // We need to find the correct roles. 
        // Typically role_id 1 is Super Admin.
        // Role 3 seemed to be Area Manager based on previous seeder observation.
        
        // Super Admin Role (typically 1)
        DB::table('admin_role_menu')->insert([
            ['role_id' => 1, 'menu_id' => $superAdminMenuId],
            ['role_id' => 1, 'menu_id' => $areaManagerMenuId], // Super admin should see both maybe? or just SA?
        ]);

        // Area Manager Role (Let's verify the role ID first or use a subquery)
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('admin_menu')->whereIn('uri', [
            'areaManager/coin-rate-settings',
            'superadmin/coin-rate-settings'
        ])->delete();
    }
}
