<?php

namespace Database\Seeders;

use App\Models\RoleCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class AdminPermissionRefact extends Seeder
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
            'request-backgrounds-image',
            'bans',
            'close-room',
            'special-uuid-requests',
            'edit-level',
            'gift-from-the-store',
            'gift-VIP',
            'gift-a-medal',
            'app-wallet',
            'coin-recharge',
            'charger-reports',
            'charge-settings',
            'users',
            'user-actions',

            'complaints',
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
            'salary-payment-countries',
            'salary-requests',
            'hosts-target',
            'users-Wallet',
            'host-agencies-report',
            'internal-sales-system-report',
            'agencies',
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
            'daily-prize',
            'daily-gift',
            'level',
            'level-interval',
            'reward-level-interval',
            'vip-privilege',
            'transaction-request-problem',
            'agency-manger-setting',
            'Payment-methods-for-shipping-agencies',
            'roles'

        ];

        $methods = [
            'browse',
            'create',
            'delete',
            'edit',
            'show'
        ];


        // Step 1: Category definitions with sort number
        $categories = [
            [
                'name' => 'Dashboard',
                'sort' => 1,
                'permissions' => [
                    [
                        'key' => 'dashboard',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => [],
                    ],
                ],
            ],
            [
                'name' => 'Games',
                'sort' => 2,
                'permissions' => [
                    [
                        'key' => 'games',
                        'except' => [],
                        'additional' => [],
                    ],
                ],
            ],
            [
                'name' => 'Fast orders',
                'sort' => 3,
                'permissions' => [
                    [
                        'key' => 'request-backgrounds-image',
                        'except' => [],
                        'additional' => [],
                    ],
                    [
                        'key' => 'bans',
                        'except' => ['show', 'edit'],
                        'additional' => [],
                    ],
                    [
                        'key' => 'close-room',
                        'except' => ['show', 'edit'],
                        'additional' => [],
                    ],
                    [
                        'key' => 'special-uuid-requests',
                        'except' => ['show'],
                        'additional' => [],
                    ],
                    [
                        'key' => 'edit-level',
                        'except' => ['create', 'delete', 'show'],
                        'additional' => [],
                    ],
                    [
                        'key' => 'gift-from-the-store',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => ['gift-switch'],
                    ],
                    [
                        'key' => 'gift-VIP',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => ['gift-switch'],
                    ],
                    [
                        'key' => 'gift-a-medal',
                        'except' => ['delete', 'show'],
                        'additional' => ['gift-switch'],
                    ],
                ],
            ],
            ['name' => 'Wallet', 'sort' => 4, 'permissions' =>  [
                [
                    'key' => 'app-wallet',
                    'except' => ['create', 'edit', 'delete', 'show'],
                    'additional' => [],
                ],
            ],],
            [
                'name' => 'charge system',
                'sort' => 5,
                'permissions' => [
                    [
                        'key' => 'charger-reports',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => [],
                    ],
                    [
                        'key' => 'charge-settings',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => [],
                    ],
                    [
                        'key' => 'coin-recharge',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => ['add-switch', 'charge-report-switch'],
                    ],
                ],
            ],
            [
                'name' => 'users',
                'sort' => 6,
                'permissions' => [
                    ['key' => 'deleted-accounts', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => ['delete-user-account-switch', 'restore-user-account-switch']],
                    ['key' => 'users', 'except' => [], 'additional' => ['actions-switch', 'chang-agency-switch', 'charge-switch', 'invite-switch', 'can-Play-switch', 'kick-family-switch', 'kick-agency-switch',]],
                    ['key' => 'complaints', 'except' => [], 'additional' => []],

                ],
            ],
            [
                'name' => 'Advertisements',
                'sort' => 7,
                'permissions' => [
                    ['key' => 'banner', 'except' => [], 'additional' => []],
                    ['key' => 'splash', 'except' => [], 'additional' => []],
                    ['key' => 'official-messages', 'except' => [], 'additional' => []],
                    ['key' => 'advertising-space', 'except' => [], 'additional' => []],
                ],
            ],
            [
                'name' => 'Store',
                'sort' => 8,
                'permissions' => [
                    ['key' => 'store', 'except' => [], 'additional' => []],
                ],
            ],
            [
                'name' => 'Distinguished identifier',
                'sort' => 9,
                'permissions' => [
                    ['key' => 'featured-ids', 'except' => [], 'additional' => []],
                    ['key' => 'details-of-unique-identifiers', 'except' => ['create', 'edit', 'show'], 'additional' => []],
                    ['key' => 'id-color', 'except' => [], 'additional' => []],
                ],
            ],
            [
                'name' => 'Vip',
                'sort' => 10,
                'permissions' => [
                    ['key' => 'VIPs', 'except' => [], 'additional' => []],
                    ['key' => 'vip-gift', 'except' => ['show',], 'additional' => []],
                    ['key' => 'vip-privilege', 'except' => [], 'additional' => []],
                ],
            ],
            [
                'name' => 'families',
                'sort' => 11,
                'permissions' => [
                    ['key' => 'families', 'except' => [], 'additional' => []],
                    ['key' => 'families-level', 'except' => [], 'additional' => []],
                ],
            ],
            [
                'name' => 'Agency System',
                'sort' => 12,
                'permissions' => [
                    ['key' => 'agency-settings', 'except' => ['create', 'delete', 'show'], 'additional' => []],
                ],
            ],
            [
                'name' => 'Internal Sales System',
                'sort' => 13,
                'permissions' => [
                    ['key' => 'salary-payment-countries', 'except' => [], 'additional' => []],
                    ['key' => 'salary-requests', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => []],
                    ['key' => 'internal-sales-system-report', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => ['rejected-request-switch', 'accept-request-switch']],
                    ['key' => 'transaction-request-problem', 'except' => [], 'additional' => []],
                ],
            ],
            [
                'name' => 'Host Agencies',
                'sort' => 14,
                'permissions' => [
                    ['key' => 'reports', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => []],
                    ['key' => 'achieved-Target', 'except' => [], 'additional' => []],
                    ['key' => 'agencies', 'except' => ['delete'], 'additional' => ['delete-switch', 'change-users-agency-switch']],
                    ['key' => 'host-agencies-report', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => []],
                    ['key' => 'users-Wallet', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => []],
                    ['key' => 'hosts-target', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => []],
                    ['key' => 'hosts', 'except' => ['create'], 'additional' => ['charge-switch', 'chang-agency-switch', 'invite-switch', 'can-Play-host-switch', 'kick-agency-host-switch', 'kick-family-host-switch',],],

                ],
            ],
            [
                'name' => 'Agency Settings',
                'sort' => 15,
                'permissions' => [
                    ['key' => 'agencies-join-requests', 'except' => ['create'], 'additional' => []],
                    ['key' => 'target', 'except' => [], 'additional' => []],
                    ['key' => 'request-agencies', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => ['accept-agency', 'refuse-agency']],
                    ['key' => 'request-agency-history', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => []],
                ],
            ],
            [
                'name' => 'Charging Agencies',
                'sort' => 16,
                'permissions' => [
                    ['key' => 'appear-charger-agency', 'except' => ['delete', 'show'], 'additional' => ['delete-switch']],
                    ['key' => 'charge-agency', 'except' => [], 'additional' => []],
                    ['key' => 'payment-gat-way', 'except' => [], 'additional' => []],
                    ['key' => 'agency-manger-setting', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => []],
                    ['key' => 'Payment-methods-for-shipping-agencies', 'except' => ['browse', 'show'], 'additional' => []],
                ],
            ],
            [
                'name' => 'Agency Manager',
                'sort' => 17,
                'permissions' => [
                    ['key' => 'managers', 'except' => [], 'additional' => []],
                    [
                        'key' => 'BD',
                        'except' => [],
                        'additional' => ['delete-switch', 'choose-switch'],
                    ],
                ],
            ],
            [
                'name' => 'Room',
                'sort' => 18,
                'permissions' => [
                    ['key' => 'rooms', 'except' => ['create'], 'additional' => ['actions-switch', 'pin-switch', 'close-switch']],
                    ['key' => 'categories', 'except' => [], 'additional' => []],
                    ['key' => 'room-vip', 'except' => [], 'additional' => []],
                    ['key' => 'room-background', 'except' => [], 'additional' => []],
                    ['key' => 'emoji', 'except' => [], 'additional' => []],
                    ['key' => 'gift', 'except' => [], 'additional' => []],
                    ['key' => 'room-settings', 'except' => ['create', 'delete', 'show'], 'additional' => []],
                ],
            ],
            [
                'name' => 'Achievements',
                'sort' => 19,
                'permissions' => [
                    ['key' => 'achievement', 'except' => ['create', 'delete'], 'additional' => []],
                    ['key' => 'achievement_level', 'except' => [], 'additional' => []],
                    ['key' => 'user_achievement_level', 'except' => [], 'additional' => []],
                ],
            ],
            [
                'name' => 'Group chat',
                'sort' => 20,
                'permissions' => [
                    ['key' => 'group-chat', 'except' => [], 'additional' => []],
                    ['key' => 'updates_group_chat', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => []],
                ],
            ],
            [
                'name' => 'Lucky box',
                'sort' => 21,
                'permissions' => [
                    ['key' => 'boxes', 'except' => [], 'additional' => []],
                    ['key' => 'box-use', 'except' => ['create'], 'additional' => []],
                ],
            ],
            [
                'name' => 'Events',
                'sort' => 22,
                'permissions' => [
                    ['key' => 'event-period', 'except' => [], 'additional' => []],
                    ['key' => 'weekly-star', 'except' => [], 'additional' => []],
                    ['key' => 'weekly_star_rewards', 'except' => [], 'additional' => []],
                    ['key' => 'target-event', 'except' => [], 'additional' => []],
                    ['key' => 'gift-target-event', 'except' => [], 'additional' => []],
                    ['key' => 'pk-event', 'except' => [], 'additional' => []],
                    ['key' => 'pk-event-rewards', 'except' => [], 'additional' => []],
                    ['key' => 'general-roles', 'except' => [], 'additional' => []],
                    ['key' => 'event_report', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => ['return-switch']],
                ],
            ],
            [
                'name' => 'Reels',
                'sort' => 23,
                'permissions' => [
                    ['key' => 'Real', 'except' => [], 'additional' => []],
                    ['key' => 'report-real', 'except' => ['create', 'edit', 'show'], 'additional' => []],
                ],
            ],
            [
                'name' => 'Moment',
                'sort' => 24,
                'permissions' => [
                    ['key' => 'Moment', 'except' => [], 'additional' => []],
                    ['key' => 'report-moment', 'except' => ['edit', 'delete', 'show'], 'additional' => []],
                ],
            ],
            [
                'name' => 'Employees and Permissions',
                'sort' => 25,
                'permissions' => [
                    ['key' => 'auth-users', 'except' => [], 'additional' => []],
                    ['key' => 'roles', 'except' => [], 'additional' => []],
                ],
            ],
            [
                'name' => 'Work Settings',
                'sort' => 26,
                'permissions' => [
                    ['key' => 'delete-account-details', 'except' => [], 'additional' => []],
                    ['key' => 'questions', 'except' => [], 'additional' => []],
                    ['key' => 'country', 'except' => [], 'additional' => []],
                    ['key' => 'page', 'except' => [], 'additional' => []],
                    ['key' => 'payment-coin', 'except' => [], 'additional' => []],
                    ['key' => 'coins', 'except' => [], 'additional' => []],
                    ['key' => 'exchange', 'except' => [], 'additional' => []],
                    ['key' => 'gold-coins', 'except' => [], 'additional' => []],
                ],
            ],
            [
                'name' => 'Sensitive Settings',
                'sort' => 27,
                'permissions' => [
                    ['key' => 'updates', 'except' => [], 'additional' => []],
                    ['key' => 'config', 'except' => [], 'additional' => []],
                ],
            ],
            [
                'name' => 'System Settings',
                'sort' => 28,
                'permissions' => [
                    ['key' => 'settings', 'except' => [], 'additional' => []],
                    ['key' => 'language', 'except' => [], 'additional' => []],
                    ['key' => 'daily-prize', 'except' => [], 'additional' => []],
                    ['key' => 'daily-gift', 'except' => [], 'additional' => []],
                ],
            ],
            [
                'name' => 'Level',
                'sort' => 29,
                'permissions' => [
                    ['key' => 'level', 'except' => [], 'additional' => []],
                    ['key' => 'level-interval', 'except' => [], 'additional' => []],
                    ['key' => 'reward-level-interval', 'except' => [], 'additional' => []],
                ],
            ],
            [
                'name' => 'user parent',
                'sort' => 30,
                'permissions' => [
                    ['key' => 'user-parent', 'except' => ['edit', 'delete', 'show'], 'additional' => []],
                ],
            ],


        ];




        foreach ($categories as $cat) {
            RoleCategory::updateOrCreate(
                ['name_en' => $cat['name']],
                ['slug' => $cat['name'], 'sort' => $cat['sort']]
            );
        }

        $lastSort = RoleCategory::max('sort');
        RoleCategory::updateOrCreate(
            ['name_en' => 'general'],
            ['slug' => 'general', 'sort' => $lastSort + 1]
        );

        // Helper to retrieve permission config
        function getPermissionConfig($permissionKey, $categories)
        {
            foreach ($categories as $cat) {
                foreach ($cat['permissions'] as $perm) {
                    if ($perm['key'] === $permissionKey) {
                        return [
                            'category_slug' => $cat['name'],
                            'except' => $perm['except'] ?? [],
                            'additional' => $perm['additional'] ?? [],
                        ];
                    }
                }
            }
            return [
                'category_slug' => 'general',
                'except' => [],
                'additional' => [],
            ];
        }

        // Helper to format additional slugs
        function formatAdditionalSlug($raw, $mainKey)
        {
            $parts = explode('-', $raw);
            if (count($parts) < 2) {
                return $raw;
            }

            $action = $parts[0];
            // $type = strtolower($parts[1]);
            $type = strtolower(substr($raw, strlen($action) + 1));

            return "{$action}-{$type}-{$mainKey}";
        }

        $allSlugs = [];

        foreach ($categories as $cat) {
            foreach ($cat['permissions'] as $permConfig) {
                $permissionKey = $permConfig['key'];
                $config = getPermissionConfig($permissionKey, $categories);

                // CRUD-like permissions
                foreach ($methods as $method) {
                    if (in_array($method, $config['except'])) {
                        continue;
                    }

                    $slug = "{$method}-{$permissionKey}";
                    $name = ucfirst($method) . ' ' . str_replace('-', ' ', $permissionKey);

                    DB::table('admin_permissions')->updateOrInsert(
                        ['slug' => $slug],
                        [
                            'name' => $name,
                            'slug' => $slug,
                            'http_method' => null,
                            'http_path' => null,
                            'category' => $config['category_slug'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                    $allSlugs[] = $slug;
                }

                // Additional permissions
                foreach ($config['additional'] as $extraRaw) {
                    $slug = formatAdditionalSlug($extraRaw, $permissionKey);
                    $name = ucfirst(str_replace('-', ' ', $slug));

                    DB::table('admin_permissions')->updateOrInsert(
                        ['slug' => $slug],
                        [
                            'name' => $name,
                            'slug' => $slug,
                            'http_method' => null,
                            'http_path' => null,
                            'category' => $config['category_slug'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                    $allSlugs[] = $slug;
                }
            }
        }

        // Delete unused permissions
        DB::table('admin_permissions')
            ->whereNotIn('slug', $allSlugs)
            ->where('slug', '!=', '*')
            ->delete();
    }
}
