<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class AdminPermission extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [

            'moment',
            'wares-dedicate',
            'vips-dedicate',
            'users',
            'version',
            'bans',
            'auth-users',
            'user-levels',
            'agency-manager',
            'manger-agency-manager',
            'carousel',
            'manger-type',
            'official-messages',
            'banners',
            'wares-vips',
            'achievement',
            'user_achievement_level',
            'level',
            'achievement_level',
            'offers',
            'trashed-account-user',
            'special-history',
            'special-Ware',
            'special-frame',
            'event',
            'ovip',
            'room-vip',
            'withdraw-type',
            'weekly-star',
            'general-roles',
            'pk-event',
            'pk-event-rewards',
            'target-event',
            'weekly_star_rewards',
            'weekly-star',
            'get-salary-requests',
            'request-agencies',
            'color',
            "agencies-tareget-manger",
            'managers',
            'report',
            'special-id-request',
            'background-image-request',
            'report-moment',
            'admin-users',
            'appear-charger-agency',
            'charger-report',
            'report-user',
            'target-percentage',
            'core-wallets',
            'auth-user',
            'all-statistic',
            'level-interval',
            'user-parent',
            'Real',
            'sailer',
            'group-chat',
            'image-color',
            'users-devices',
            'user-target',
            'room-target',
            'exchange',
            'boxes',
            'box-use',
            'level-cp',
            'event_report',
            'payment-gat-way',
            'agent-target',
            'agent-request',
            'agent-user',
            'roles',
            'updates',
            'Permissions',
            'daily-prize',
            'weekly_cp',
            "request-problem",
            'agent-request-transaction',
            "charge-country",
            'charge-agency',
            'salary-request',
            "agent-request-history",
            "agencies",
            'user-agent-target',
            'charges-agency',
            'agent-home',
            'updates_group_chat',
            'agora-zego',
            'uuid-dedicate'




        ];

        $methods = [
            'browse',
            'create',
            'delete',
            'edit',
        ];

        $categories = [

            'event-related' => ['event', 'pk-event', 'target-event', 'pk-event-rewards', 'weekly_star_rewards', 'weekly-star', 'event_report', 'general-roles','weekly_cp'],
            'special_id' => ['special-history', 'special-Ware', 'special-frame', 'special-id-request'],
            'daily-prize' => ['daily-prize'],
            'roles' => ['roles', 'Permissions',],
            'cp' => ['level-cp'],
            'report' => ['report', 'report-moment', 'charger-report', 'report-user',],
            'user' => ['users', 'trashed-account-user', 'user-target',],
            'achievement' => ['achievement', 'user_achievement_level', 'achievement_level',],
            'level' => ['level', 'ovip','wares-vips'],
            'moment' => ['moment',],
            'dedicate' => ['wares-dedicate', 'vips-dedicate', 'users-devices','uuid-dedicate'],
            'version' => ['version'],
            'auth-users' => ['auth-users','admin-users'],
            'agency' => ['agency-manager', 'manger-agency-manager', 'manger-type', 'request-agencies', "agencies-tareget-manger",'managers','agencies'],
            'agent' => ['agent-user', 'agent-request', 'agent-target','user-agent-target','charges-agency','agent-home'],
            'payment-gat-way' => ['payment-gat-way',],
            'box' => ['boxes', 'box-use',],
            'exchange' => ['exchange'],
            'rooms' => ['room-vip', 'room-target',],
            'image-color' => ['image-color'],
            'sailer' => ['sailer'],
            'real' => ['Real',],
            'user-parent' => ['user-parent',],
            'level-interval' => ['level-interval'],
            'all-statistic' => ['all-statistic'],
            'core-wallets' => ['core-wallets'],
            'target-percentage' => ['target-percentage',],
            'background-image' => ['background-image-request'],
            'color' => ['color'],
            'get-salary-requests' => ['get-salary-requests'],
            'withdraw-type' => ['withdraw-type'],
            'offers' => ['offers'],
            'banners' =>['banners'],
            'official-messages' => ['official-messages'],
            'carousel' => ['carousel'],
            'user-levels' => ['user-levels'],
            'salary-transaction' => ["request-problem",'agent-request-transaction',"charge-country",'charge-agency','salary-request',"agent-request-history"],
            'bans' => ['bans'],
            'update-group-chat' => ['updates_group_chat'],
            'agora-zego' => ['agora-zego']
          

        ];

        // Function to get category for each permission
        function getCategory($permission, $categories)
        {
            foreach ($categories as $category => $permissionsInCategory) {
                if (in_array($permission, $permissionsInCategory)) {
                    return $category;
                }
            }
            return 'general'; 
        }

        foreach ($permissions as $permission) {
            foreach ($methods as $method) {
                $slug = $method . '-' . $permission;
                $name = $method . ' ' . str_replace('-', ' ', $permission);
        
                // Check if the permission already exists
                $permissionExists = DB::table('admin_permissions')->where('slug', $slug)->first();
        
                // Get the category for the permission
                $category = getCategory($permission, $categories);
        
                if ($permissionExists) {
                    // If the permission exists, update the category
                    DB::table('admin_permissions')->where('slug', $slug)->update([
                        'category'   => $category,
                        'updated_at' => now(),
                    ]);
                    
                } else {
                    // Insert the new permission with its category
                    DB::table('admin_permissions')->insert([
                        'name'        => $name,
                        'slug'        => $slug,
                        'http_method' => null,
                        'http_path'   => null,
                        'category'    => $category,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            }
        }
       

    }
}
