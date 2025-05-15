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
            'admin-profile',
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
            'uuid-dedicate',
            'agency-setting',
            'agency-settings',
            'free-users',
            'users-family',
            'updates_family-config',
            'ovip-gift',
            'charge-level',
            'ban-rooms',
            'settings',
            'notification',
            'charge-settings',
            'room-settings',
            'ovip-settings',
            'box-settings',
            'moment-settings',
            'reel-settings',
            'agency-manger-setting',
            'chat-setting',
            'achievement_dedicate',
            'charge',
            'ticket',
            'users-hunters',
            'action-trashed',
            'gift',
            'wares',
            'vip-privilege',
            'family',
            'family-level',
            'user-status',
            'salary-history',
            'agency-join-requests',
            'request-agency-history',
            'target',
            'update_setting_button',
            'rooms',
            'categories',
            'room-background',
            'emoji',
            'event-period',
            'gift-target-event',
            'report-real',
            'delete-account-details',
            'questions',
            'country',
            'page',
            'payment-coin',
            'coins',
            'gold-coins',
            'config',
            'level-user-history',
            'request-problem',
            'user-target-eg'





        ];

        $methods = [
            'browse',
            'create',
            'delete',
            'edit',
        ];

        $categories = [

            'Fast orders' => ['bans', 'ban-rooms', 'special-id-request', 'user-levels', 'wares-dedicate', 'vips-dedicate', 'achievement_dedicate'],
            'Wallet' => ['core-wallets'],
            'charge system' => ['charge', 'charger-report'],
            'users' => ['free-users', 'ticket', 'trashed-account-user'],
            'Advertisements' => ['carousel', 'banners', 'official-messages', 'offers'],
            'Store' => ['wares',],
            'Distinguished identifier' => ['special-Ware', 'special-history', 'image-color'],
            'Vip' => ['ovip', 'vip-privilege', 'ovip-settings'],
            'families' => ['family', 'family-level'],
            'Agency System' => ['agency-settings'],
            'Internal Sales System' => ['charge-country', 'salary-request', 'agent-request-transaction', 'request-problem'],
            'Host Agencies' => ['users-hunters', 'user-target', 'salary-history', 'Report_user', 'agencies', 'user-target-eg', 'report'],
            'Agency Settings' => ['agency-join-requests', 'request-agencies', 'target'],
            'Charging Agencies' => ['agency-manger-setting', 'appear-charger-agency', 'charge-agency', 'payment-gat-way'],
            'Agency Manager' => ['managers'],
            'Room' => ['rooms', 'categories', 'room-vip', 'room-background', 'emoji', 'gift', 'room-settings'],
            'Achievements' => ['achievement', 'user_achievement_level'],
            'user parent' => ['user-parent'],
            'Group chat'  => ['group-chat', 'updates_group_chat'],
            'Lucky box' => ['boxes', 'box-use', 'box-settings'],
            'Events' => ['event-period', 'target-event', 'pk-event', 'pk-event-rewards', 'weekly_star_rewards', 'weekly-star', 'general-roles', 'event_report'],
            'Reels' => ['Real', 'report-real'],
            'Moment' => ['moment', 'report-moment'],
            'Work Settings' => ['delete-account-details', 'questions', 'country', 'page', 'payment-coin', 'exchange', 'sailer', 'salary-history'],
            'Sensitive Settings' => ['updates', 'config'],
            'System Settings' => ['settings', 'language', 'daily-prize'],
            'Level' => ['level', 'level-interval',],
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
        DB::table('admin_permissions')->where('slug', 'like', 'update%')->orWhere('slug', 'like', 'show%')->delete();
        $actions = ['create', 'edit', 'delete', 'show','charge'];
        $targets = ['report-user', 'report', 'event_report', 'report-real', 'charger-report','wares-dedicate','vips-dedicate','achievement_dedicate','level-user-history','agent-request-history','salary-history','request-agency-history'];

        DB::table('admin_permissions')->where(function ($query) use ($actions, $targets) {
            foreach ($actions as $action) {
                foreach ($targets as $target) {
                    $query->orWhere('slug', 'like', "{$action}-{$target}");
                }
            }
        })->delete();

        $actions = ['create', 'delete', 'show'];
        $targets = ['update_setting_button','agency-settings','agency-setting','settings','charge-settings','room-settings','ovip-settings','box-settings','moment-settings','reel-settings','chat-setting','user-status'];

        DB::table('admin_permissions')->where(function ($query) use ($actions, $targets) {
            foreach ($actions as $action) {
                foreach ($targets as $target) {
                    $query->orWhere('slug', 'like', "{$action}-{$target}");
                }
            }
        })->delete();

        $actions = ['create', 'edit', 'show'];
        $targets = ['special-history'];

        DB::table('admin_permissions')->where(function ($query) use ($actions, $targets) {
            foreach ($actions as $action) {
                foreach ($targets as $target) {
                    $query->orWhere('slug', 'like', "{$action}-{$target}");
                }
            }
        })->delete();

        $actions = ['create'];
        $targets = ['box-use'];

        DB::table('admin_permissions')->where(function ($query) use ($actions, $targets) {
            foreach ($actions as $action) {
                foreach ($targets as $target) {
                    $query->orWhere('slug', 'like', "{$action}-{$target}");
                }
            }
        })->delete();
    }
}
