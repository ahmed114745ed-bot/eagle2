<?php

namespace Database\Seeders;

use App\Models\RoleCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class AdminNewPermission extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [

            'dashboard',
            'games',
            'BD',
            'delete-bd-Switch',
            'choose-bd-Switch',
            'request-backgrounds-image',
            'bans',
            'close-room',
            'special-uuid-requests',
            'edit-level',
            'gift-store-Switch',
            'gift-from-the-store',
            'gift-VIP',
            'gift-VIP-Switch',
            'gift-a-medal',
            'app-wallet',
            'charge-report-Switch',
            'add-coins-Switch',
            'coin-recharge',
            'charger-reports',
            'charge-settings',
            'users',
            'user-actions',
            'charge-switch',
            'invite-switch',
            'can-Play-Switch',
            'kick-agency-Switch',
            'kick-family-Switch',
            'chang-agency-Switch',
            'complaints',
            'restore-user-account-Switch',
            'delete-user-account-Switch',
            'banner',
            'splash',
            'official-messages',
            'advertising-space',
            'featured-ids',
            'details-of-unique-identifiers',
            'id-color',
            'store',
            'VIPs',
            'vip-gift',
            'families',
            'families-level',
            'countries-where-salary-payments-are-available',
            'salary-requests',
            'rejected-agent-request-switch',
            'accept-agent-request-switch',
            'hosts',
            'host-charge-switch',
            'host-invite-switch',
            'host-can-Play-Switch',
            'host-kick-agency-Switch',
            'host-kick-family-Switch',
            'host-chang-agency-Switch',
            'hosts-target',
            'users-Wallet',
            'host-agencies-report',
            'internal-sales-system-report',
            'agencies',
            'delete-agency-Switch',
            'change-users-agency-Switch',
            'achieved-Target',
            'reports',
            'agencies-join-requests',
            'target',
            'agencies-request',
            'deleted-accounts',
            'accept-agency',
            'refuse-agency',
            'request-agencies',
            'request-agency-history',
            'appear-charger-agency',
            'charge-agency',
            'payment-gat-way',
            'managers',
            'agency-settings',
            'rooms',
            'room-actions',
            'room-pin-switch',
            'close-room-switch',
            'categories',
            'room-vip',
            'room-background',
            'emoji',
            'gift',
            'room-settings',
            'achievement',
            'achievement_level',
            'user_achievement_level',
            'user-parent',
            'group-chat',
            'updates_group_chat',
            'update_setting_button',
            'boxes',
            'box-use',
            'event-period',
            'weekly-star',
            'weekly_star_rewards',
            'target-event',
            'gift-target-event',
            'pk-event',
            'pk-event-rewards',
            'general-roles',
            'event_report',
            'Real',
            'report-real',
            'Moment',
            'report-moment',
            'auth-users',
            'delete-account-details',
            'questions',
            'country',
            'page',
            'payment-coin',
            'coins',
            'exchange',
            'gold-coins',
            'updates',
            'config',
            'settings',
            'language',
            'language-switch',
            'daily-prize',
            'daily-gift',
            'level',
            'level-interval',
            'reward-level-interval'
        ];

        $methods = [
            'browse',
            'create',
            'delete',
            'edit',
        ];


        // Step 1: Category definitions with sort number
        $categories = [
            ['name' => 'Dashboard', 'sort' => 1, 'permissions' => ['dashboard']],
            ['name' => 'Games', 'sort' => 1, 'permissions' => ['games']],
            ['name' => 'BD Management', 'sort' => 1, 'permissions' => ['BD', 'delete-bd-Switch', 'choose-bd-Switch']],
            ['name' => 'Fast orders', 'sort' => 1, 'permissions' => ['request-backgrounds-image', 'gift-VIP-Switch','bans', 'close-room', 'special-uuid-requests','edit-level','gift-store-Switch','gift-from-the-store','gift-VIP']],
            ['name' => 'Wallet', 'sort' => 2, 'permissions' => ['app-wallet']],
            ['name' => 'charge system', 'sort' => 3, 'permissions' => ['charge-report-Switch','add-coins-Switch','coin-recharge','charge-settings','charger-reports']],
            ['name' => 'users', 'sort' => 4, 'permissions' => ['deleted-accounts','users','user-actions','chang-agency-Switch','charge-switch','invite-switch','can-Play-Switch','kick-family-Switch','kick-agency-Switch','complaints','delete-user-account-Switch','restore-user-account-Switch']],
            ['name' => 'Advertisements', 'sort' => 6, 'permissions' => ['banner','splash','official-messages','advertising-space']],
            ['name' => 'Store', 'sort' => 7, 'permissions' => ['store']],
            ['name' => 'Distinguished identifier', 'sort' => 8, 'permissions' => ['featured-ids','details-of-unique-identifiers','id-color']],
            ['name' => 'Vip', 'sort' => 9, 'permissions' => ['VIPs','vip-gift']],
            ['name' => 'families', 'sort' => 10, 'permissions' => ['families','families-level']],
            ['name' => 'Agency System', 'sort' => 11, 'permissions' => ['agency-settings']],
            ['name' => 'Internal Sales System', 'sort' => 12, 'permissions' => ['countries-where-salary-payments-are-available','accept-agent-request-switch','salary-requests','internal-sales-system-report','rejected-agent-request-switch']],
            ['name' => 'Host Agencies', 'sort' => 13, 'permissions' => ['reports','achieved-Target','change-users-agency-Switch','delete-agency-Switch','agencies','host-agencies-report','users-Wallet','hosts-target','hosts','host-charge-switch','host-chang-agency-Switch','host-invite-switch','host-can-Play-Switch','host-kick-agency-Switch','host-kick-family-Switch']],
            ['name' => 'Agency Settings', 'sort' => 14, 'permissions' => ['agencies-join-requests','target', 'request-agencies', 'accept-agency', 'refuse-agency', 'request-agency-history']],
            ['name' => 'Charging Agencies', 'sort' => 15, 'permissions' => ['appear-charger-agency', 'charge-agency', 'payment-gat-way']],
            ['name' => 'Agency Manager', 'sort' => 16, 'permissions' => ['managers']],
            ['name' => 'Room', 'sort' => 17, 'permissions' => ['rooms', 'room-actions', 'room-pin-switch', 'close-room-switch', 'categories', 'room-vip', 'room-background', 'emoji', 'gift', 'room-settings']],
            ['name' => 'Achievements', 'sort' => 18, 'permissions' => ['achievement', 'achievement_level', 'user_achievement_level']],
            ['name' => 'Group chat', 'sort' => 19, 'permissions' => ['group-chat',  'updates_group_chat', 'update_setting_button']],
            ['name' => 'Lucky box', 'sort' => 20, 'permissions' => ['boxes', 'box-use']],
            ['name' => 'Events', 'sort' => 21, 'permissions' => ['event-period', 'weekly-star', 'weekly_star_rewards', 'target-event', 'gift-target-event', 'pk-event', 'pk-event-rewards', 'general-roles', 'event_report']],
            ['name' => 'Reels', 'sort' => 22, 'permissions' => ['Real', 'report-real']],
            ['name' => 'Moment', 'sort' => 23, 'permissions' => ['Moment', 'report-moment']],
            ['name' => 'Employees and Permissions', 'sort' => 25, 'permissions' => ['auth-users', 'roles']],
            ['name' => 'Work Settings', 'sort' => 24, 'permissions' => ['delete-account-details', 'questions', 'country', 'page', 'payment-coin', 'coins', 'exchange', 'gold-coins']],
            ['name' => 'Sensitive Settings', 'sort' => 25, 'permissions' => ['updates', 'config']],
            ['name' => 'System Settings', 'sort' => 26, 'permissions' => ['settings', 'language', 'language-switch', 'daily-prize', 'daily-gift']],
            ['name' => 'Level', 'sort' => 27, 'permissions' => ['level', 'level-interval', 'reward-level-interval']],
            ['name' => 'user parent', 'sort' => 28, 'permissions' => ['user-parent']],
        ];

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
        $actions = ['create', 'edit', 'delete', 'show'];
        $targets = ['dashboard', 'delete-agency-Switch','deleted-accounts','reports','change-users-agency-Switch','achieved-Target','internal-sales-system-report','delete-bd-Switch','host-agencies-report','salary-requests','users-Wallet','hosts-target','host-chang-agency-Switch','host-kick-family-Switch','host-charge-switch','host-kick-agency-Switch','host-can-Play-Switch','host-invite-switch','accept-agent-request-switch','rejected-agent-request-switch','charger-reports','restore-user-account-Switch','delete-user-account-Switch','charge-switch','kick-agency-Switch','kick-family-Switch','invite-switch','can-Play-Switch','user-actions','charge-settings','charge-report-Switch','coin-recharge','add-coins-Switch','app-wallet' ,'gift-VIP-Switch','gift-VIP','choose-bd-Switch','gift-store-Switch','gift-from-the-store', 'accept-agency', 'refuse-agency', 'request-agency-history', 'request-agencies', 'room-actions', 'room-pin-switch', 'close-room-switch', 'updates_group_chat', 'language', 'language-switch'];

        DB::table('admin_permissions')->where(function ($query) use ($actions, $targets) {
            foreach ($actions as $action) {
                foreach ($targets as $target) {
                    $query->orWhere('slug', 'like', "{$action}-{$target}");
                }
            }
        })->delete();

        $actions = ['create', 'browse', 'delete', 'show'];
        $targets = ['update_setting_button'];

        DB::table('admin_permissions')->where(function ($query) use ($actions, $targets) {
            foreach ($actions as $action) {
                foreach ($targets as $target) {
                    $query->orWhere('slug', 'like', "{$action}-{$target}");
                }
            }
        })->delete();

        $actions = ['create', 'delete', 'show'];
        $targets = ['edit-level', 'agency-settings', 'room-settings', 'settings'];

        DB::table('admin_permissions')->where(function ($query) use ($actions, $targets) {
            foreach ($actions as $action) {
                foreach ($targets as $target) {
                    $query->orWhere('slug', 'like', "{$action}-{$target}");
                }
            }
        })->delete();

        $actions = ['create', 'edit', 'show'];
        $targets = ['details-of-unique-identifiers', 'Real', 'report-real'];

        DB::table('admin_permissions')->where(function ($query) use ($actions, $targets) {
            foreach ($actions as $action) {
                foreach ($targets as $target) {
                    $query->orWhere('slug', 'like', "{$action}-{$target}");
                }
            }
        })->delete();

        $actions = ['create', 'delete', 'show'];
        $targets = [];

        DB::table('admin_permissions')->where(function ($query) use ($actions, $targets) {
            foreach ($actions as $action) {
                foreach ($targets as $target) {
                    $query->orWhere('slug', 'like', "{$action}-{$target}");
                }
            }
        })->delete();

        $actions = ['edit', 'delete', 'show'];
        $targets = ['gift-a-medal', 'user-parent', 'general-roles', 'Moment', 'report-moment'];

        DB::table('admin_permissions')->where(function ($query) use ($actions, $targets) {
            foreach ($actions as $action) {
                foreach ($targets as $target) {
                    $query->orWhere('slug', 'like', "{$action}-{$target}");
                }
            }
        })->delete();


        $actions = ['create'];
        $targets = ['hosts','agencies-join-requests', 'rooms', 'box-use', 'payment-coin'];

        DB::table('admin_permissions')->where(function ($query) use ($actions, $targets) {
            foreach ($actions as $action) {
                foreach ($targets as $target) {
                    $query->orWhere('slug', 'like', "{$action}-{$target}");
                }
            }
        })->delete();
        $actions = ['show', 'edit'];
        $targets = ['bans', 'close-room','vip-gift',];

        DB::table('admin_permissions')->where(function ($query) use ($actions, $targets) {
            foreach ($actions as $action) {
                foreach ($targets as $target) {
                    $query->orWhere('slug', 'like', "{$action}-{$target}");
                }
            }
        })->delete();

        $actions = ['create', 'delete'];
        $targets = ['achievement'];

        DB::table('admin_permissions')->where(function ($query) use ($actions, $targets) {
            foreach ($actions as $action) {
                foreach ($targets as $target) {
                    $query->orWhere('slug', 'like', "{$action}-{$target}");
                }
            }
        })->delete();

        $actions = ['delete'];
        $targets = ['BD',];

        DB::table('admin_permissions')->where(function ($query) use ($actions, $targets) {
            foreach ($actions as $action) {
                foreach ($targets as $target) {
                    $query->orWhere('slug', 'like', "{$action}-{$target}");
                }
            }
        })->delete();


        $actions = ['show'];
        $targets = ['special-uuid-requests'];

        DB::table('admin_permissions')->where(function ($query) use ($actions, $targets) {
            foreach ($actions as $action) {
                foreach ($targets as $target) {
                    $query->orWhere('slug', 'like', "{$action}-{$target}");
                }
            }
        })->delete();
    }
}
