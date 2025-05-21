<?php

namespace Database\Seeders;

use App\Models\RoleCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


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


        // Step 1: Category definitions with sort number
        $categories = [
            ['name' => 'Dashboard', 'sort' => 1, 'permissions' => ['all-statistic']],
            ['name' => 'Fast orders', 'sort' => 2, 'permissions' => ['bans', 'ban-rooms', 'special-id-request', 'user-levels', 'wares-dedicate', 'vips-dedicate', 'achievement_dedicate']],
            ['name' => 'Wallet', 'sort' => 3, 'permissions' => ['core-wallets']],
            ['name' => 'charge system', 'sort' => 4, 'permissions' => ['charge', 'charger-report']],
            ['name' => 'users', 'sort' => 5, 'permissions' => ['free-users', 'ticket', 'trashed-account-user']],
            ['name' => 'Advertisements', 'sort' => 7, 'permissions' => ['carousel', 'banners', 'official-messages', 'offers']],
            ['name' => 'Store', 'sort' => 8, 'permissions' => ['wares']],
            ['name' => 'Distinguished identifier', 'sort' => 9, 'permissions' => ['special-Ware', 'special-history', 'image-color']],
            ['name' => 'Vip', 'sort' => 10, 'permissions' => ['ovip', 'vip-privilege', 'ovip-settings']],
            ['name' => 'families', 'sort' => 11, 'permissions' => ['family', 'family-level']],
            ['name' => 'Agency System', 'sort' => 12, 'permissions' => ['agency-settings']],
            ['name' => 'Internal Sales System', 'sort' => 13, 'permissions' => ['charge-country', 'salary-request', 'agent-request-transaction', 'request-problem']],
            ['name' => 'Host Agencies', 'sort' => 14, 'permissions' => ['users-hunters', 'user-target', 'salary-history', 'Report_user', 'agencies', 'user-target-eg', 'report']],
            ['name' => 'Agency Settings', 'sort' => 15, 'permissions' => ['agency-join-requests', 'request-agencies', 'target']],
            ['name' => 'Charging Agencies', 'sort' => 16, 'permissions' => ['agency-manger-setting', 'appear-charger-agency', 'charge-agency', 'payment-gat-way']],
            ['name' => 'Agency Manager', 'sort' => 17, 'permissions' => ['managers']],
            ['name' => 'Room', 'sort' => 18, 'permissions' => ['rooms', 'categories', 'room-vip', 'room-background', 'emoji', 'gift', 'room-settings']],
            ['name' => 'Achievements', 'sort' => 19, 'permissions' => ['achievement', 'user_achievement_level']],
            ['name' => 'Group chat', 'sort' => 20, 'permissions' => ['group-chat', 'updates_group_chat']],
            ['name' => 'Lucky box', 'sort' => 21, 'permissions' => ['boxes', 'box-use', 'box-settings']],
            ['name' => 'Events', 'sort' => 22, 'permissions' => ['event-period', 'target-event', 'pk-event', 'pk-event-rewards', 'weekly_star_rewards', 'weekly-star', 'general-roles', 'event_report']],
            ['name' => 'Reels', 'sort' => 23, 'permissions' => ['Real', 'report-real']],
            ['name' => 'Moment', 'sort' => 24, 'permissions' => ['moment', 'report-moment']],
            ['name' => 'Employees and Permissions', 'sort' => 25, 'permissions' => [ 'auth-users', 'roles']],
            ['name' => 'Work Settings', 'sort' => 26, 'permissions' => ['delete-account-details', 'questions', 'country', 'page', 'payment-coin', 'exchange', 'sailer', 'salary-history']],
            ['name' => 'Sensitive Settings', 'sort' => 27, 'permissions' => ['updates', 'config']],
            ['name' => 'System Settings', 'sort' => 28, 'permissions' => ['settings', 'language', 'daily-prize']],
            ['name' => 'Level', 'sort' => 29, 'permissions' => ['level', 'level-interval']],
        ];

        RoleCategory::where('slug', 'user parent')->delete();

        // Step 2: Save categories to rolecategories table
        foreach ($categories as $category) {
            RoleCategory::updateOrCreate(
                ['name_en' => $category['name']],
                ['slug' => $category['name'], 'sort' => $category['sort']]
            );
        }

        $lastSort = RoleCategory::max('sort');
        RoleCategory::updateOrCreate(
            ['name_en' => 'general'],
            [
                'slug' => 'general',
                'sort' => $lastSort + 1,
            ]
        );

        // Step 3: Helper to get category name by permission
        function getCategory($permission, $categories)
        {
            foreach ($categories as $category) {
                if (in_array($permission, $category['permissions'])) {
                    return $category['name'];
                }
            }
            return 'general';
        }

        // Step 4: Loop through permissions and assign category
        foreach ($permissions as $permission) {
            foreach ($methods as $method) {
                $slug = $method . '-' . $permission;
                $name = $method . ' ' . str_replace('-', ' ', $permission);
                $category = getCategory($permission, $categories);

                $exists = DB::table('admin_permissions')->where('slug', $slug)->first();

                if ($exists) {
                    DB::table('admin_permissions')->where('slug', $slug)->update([
                        'category' => $category,
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('admin_permissions')->insert([
                        'name' => $name,
                        'slug' => $slug,
                        'http_method' => null,
                        'http_path' => null,
                        'category' => $category,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        DB::table('admin_permissions')->where('slug', 'like', 'update%')->orWhere('slug', 'like', 'show%')->delete();
        $actions = ['create', 'edit', 'delete', 'show', 'charge'];
        $targets = ['report-user', 'report', 'event_report', 'report-real', 'charger-report', 'wares-dedicate', 'vips-dedicate', 'achievement_dedicate', 'level-user-history', 'agent-request-history', 'salary-history', 'request-agency-history', 'updates_group_chat', 'users-family', 'uuid-dedicate', 'all-statistic'];

        DB::table('admin_permissions')->where(function ($query) use ($actions, $targets) {
            foreach ($actions as $action) {
                foreach ($targets as $target) {
                    $query->orWhere('slug', 'like', "{$action}-{$target}");
                }
            }
        })->delete();

        $actions = ['create', 'delete', 'show'];
        $targets = ['update_setting_button', 'agency-settings', 'agency-setting', 'settings', 'charge-settings', 'room-settings', 'ovip-settings', 'box-settings', 'moment-settings', 'reel-settings', 'chat-setting', 'user-status', 'updates_family-config', 'agora-zego','updates'];

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

        $actions = ['browse'];
        $targets = ['user-parent'];

        DB::table('admin_permissions')->where(function ($query) use ($actions, $targets) {
            foreach ($actions as $action) {
                foreach ($targets as $target) {
                    $query->orWhere('slug', 'like', "{$action}-{$target}");
                }
            }
        })->delete();
    }
}
