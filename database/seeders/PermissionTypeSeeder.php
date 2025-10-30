<?php

namespace Database\Seeders;

use App\Models\RoleCategory;
use App\Enums\PermissionType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class PermissionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('permission_types')->truncate();
        DB::table('role_categories')->truncate();
        $defaultMethods = ['browse', 'create', 'delete', 'edit', 'show'];

        // Define your base categories
        $categories = [
            [
                'name' => 'Area manager',
                'sort' => 1,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 1],
                ],
                'permissions' => [
                    [
                        'key' => 'area-manager',
                        'except' => [],
                        'additional' => [],
                        'types' => [
                            PermissionType::ADMIN->value => $defaultMethods,

                        ],
                    ],
                    [
                        'key' => 'charge-to-area-manager',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => [],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse'],

                        ],
                    ],
                ],
            ],
            [
                'name' => 'Dashboard',
                'sort' => 2,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 2],
                    PermissionType::SUPER_ADMIN->value => ['sort' => 1],
                    PermissionType::AREA_MANAGER->value => ['sort' => 1],
                ],
                'permissions' => [
                    [
                        'key' => 'dashboard',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => [],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse'],
                            PermissionType::SUPER_ADMIN->value => ['browse'],
                            PermissionType::AREA_MANAGER->value => ['browse'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Games',
                'sort' => 3,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 3],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    [
                        'key' => 'games',
                        'except' => [],
                        'additional' => [],
                        'types' => [
                            PermissionType::ADMIN->value => $defaultMethods,
                            //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                        ],
                    ],
                    [
                        'key' => 'coin-game-users-report',
                        'except' => ['show', 'edit', 'create', 'delete'],
                        'additional' => ['details-switch'],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse', 'details-switch'],
                            //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'badge',
                'sort' => 4,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 4],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'badges', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'dedicate-badges', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => ['dedicate-switch'], 'types' => [
                        PermissionType::ADMIN->value => ['browse', 'dedicate-switch'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],

                ],
            ],
            [
                'name' => 'Fast orders',
                'sort' => 5,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 5],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    [
                        'key' => 'request-backgrounds-image',
                        'except' => [],
                        'additional' => [],
                        'types' => [
                            PermissionType::ADMIN->value => $defaultMethods,
                            //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                        ],
                    ],
                    [
                        'key' => 'bans',
                        'except' => ['show', 'edit'],
                        'additional' => [],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse', 'create', 'delete'],
                            //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                        ],
                    ],
                    [
                        'key' => 'close-room',
                        'except' => ['show', 'edit'],
                        'additional' => [],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse', 'create', 'delete',],
                            //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                        ],
                    ],
                    [
                        'key' => 'special-uuid-requests',
                        'except' => ['show'],
                        'additional' => [],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse', 'create', 'delete', 'edit'],
                            //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                        ],
                    ],
                    [
                        'key' => 'edit-level',
                        'except' => ['create', 'delete', 'show'],
                        'additional' => [],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse',  'edit'],
                            //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                        ],
                    ],
                    [
                        'key' => 'gift-from-the-store',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => ['gift-switch'],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse', 'gift-switch'],
                            //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                        ],
                    ],
                    [
                        'key' => 'gift-VIP',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => ['gift-switch'],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse', 'gift-switch'],
                            //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                        ],
                    ],
                    [
                        'key' => 'gift-a-medal',
                        'except' => ['show'],
                        'additional' => ['gift-switch'],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse', 'create', 'delete', 'edit', 'gift-switch'],
                            //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Wallet',
                'sort' => 6,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 6],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' =>  [
                    [
                        'key' => 'app-wallet',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => ['transfer-switch'],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse', 'transfer-switch'],
                            //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                        ],
                    ],
                    [
                        'key' => 'core-wallet-transactions',
                        'except' => ['create'],
                        'additional' => [],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse', 'edit', 'delete', 'show'],
                            //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'charge system',
                'sort' => 7,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 7],
                    PermissionType::SUPER_ADMIN->value => ['sort' => 3],
                    PermissionType::AREA_MANAGER->value => ['sort' => 3],
                ],
                'permissions' => [
                    [
                        'key' => 'charger-reports',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => [],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse'],
                            //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                        ],

                    ],
                    [
                        'key' => 'charge-settings',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => [],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse'],
                            //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                        ],
                    ],
                    [
                        'key' => 'coin-recharge',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => ['add-switch', 'charge-report-switch'],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse', 'add-switch', 'charge-report-switch'],
                            PermissionType::SUPER_ADMIN->value => ['browse', 'add-switch'],
                            PermissionType::AREA_MANAGER->value => ['browse', 'add-switch'],
                        ],
                    ],
                    [
                        'key' => 'charge-to-user',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => ['add-switch', 'history-switch'],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse', 'add-switch', 'history-switch'],
                            //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                        ],
                    ],
                    [
                        'key' => 'host-diamond',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => [],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse'],
                            //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'users',
                'sort' => 8,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 8],
                    PermissionType::SUPER_ADMIN->value => ['sort' => 2],
                    PermissionType::AREA_MANAGER->value => ['sort' => 5],
                ],
                'permissions' => [
                    ['key' => 'deleted-accounts', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => ['delete-user-account-switch', 'restore-user-account-switch'], 'types' => [
                        PermissionType::ADMIN->value => ['browse', 'delete-user-account-switch', 'restore-user-account-switch'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'users', 'except' => [], 'additional' => ['level-switch', 'chang-agency-switch', 'charge-switch', 'invite-switch', 'can-Play-switch', 'kick-family-switch', 'kick-agency-switch', 'salary-switch', 'delete-profile-switch'], 'types' => [
                        PermissionType::ADMIN->value => ['browse', 'create', 'edit', 'delete', 'show', 'level-switch', 'chang-agency-switch', 'charge-switch', 'invite-switch', 'can-Play-switch', 'kick-family-switch', 'kick-agency-switch', 'salary-switch', 'delete-profile-switch'],
                        PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                        PermissionType::AREA_MANAGER->value => ['browse', 'show'],
                    ],],
                    ['key' => 'complaints', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'user-setting', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'user-coin-report', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],

                ],
            ],
            [
                'name' => 'Advertisements',
                'sort' => 9,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 9],
                    PermissionType::SUPER_ADMIN->value => ['sort' => 7],
                    PermissionType::AREA_MANAGER->value => ['sort' => 8],
                ],
                'permissions' => [
                    ['key' => 'banner', 'except' => [], 'additional' => ['action-switch'], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        PermissionType::SUPER_ADMIN->value => ['browse', 'action-switch', 'create'],
                    ],],
                    ['key' => 'banner-setting', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse'],
                    ],],
                    ['key' => 'splash', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                    ],],
                    ['key' => 'official-messages', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        PermissionType::SUPER_ADMIN->value => $defaultMethods,
                        PermissionType::AREA_MANAGER->value => $defaultMethods,
                    ],],
                    ['key' => 'advertising-space', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                    ],],
                ],
            ],
            [
                'name' => 'Store',
                'sort' => 10,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 10],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'store', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'Distinguished identifier',
                'sort' => 11,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 11],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'featured-ids', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'details-of-unique-identifiers', 'except' => ['create', 'edit', 'show'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['delete', 'browse'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'id-color', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'Vip',
                'sort' => 12,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 12],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'VIPs', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'vip-gift', 'except' => ['show',], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'vip-privilege', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'families',
                'sort' => 13,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 13],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'families', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'families-level', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'Agency System',
                'sort' => 14,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 14],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'agency-settings', 'except' => ['create', 'delete', 'show'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse', 'edit'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'Agency System',
                'sort' => 15,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 15],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [

                    ['key' => 'salary', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],

                ],
            ],
            [
                'name' => 'Internal Sales System',
                'sort' => 16,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 16],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'salary-payment-countries', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'salary-requests', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    [
                        'key' => 'internal-sales-system-report',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => ['rejected-request-switch', 'accept-request-switch'],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse', 'rejected-request-switch', 'accept-request-switch'],
                            //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                        ],
                    ],
                    ['key' => 'transaction-request-problem', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'Host Agencies',
                'sort' => 17,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 17],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'reports', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'achieved-Target', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'agencies', 'except' => ['delete'], 'additional' => ['delete-switch', 'change-users-agency-switch', 'kick-switch', 'make-admin-switch', 'remove-admin-switch', 'member-switch', 'charge-history-switch', 'salary-switch', 'join-switch', 'target-switch'], 'types' => [
                        PermissionType::ADMIN->value => ['browse', 'create', 'edit', 'show', 'delete-switch', 'change-users-agency-switch', 'kick-switch', 'make-admin-switch', 'remove-admin-switch', 'member-switch', 'charge-history-switch', 'salary-switch', 'join-switch', 'target-switch'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'host-agencies-report', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'users-Wallet', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'hosts-target', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'hosts', 'except' => ['create'], 'additional' => ['charge-switch', 'chang-agency-switch', 'invite-switch', 'can-Play-host-switch', 'kick-agency-host-switch', 'kick-family-host-switch',], 'types' => [
                        PermissionType::ADMIN->value => ['browse', 'edit', 'delete', 'show', 'charge-switch', 'chang-agency-switch', 'invite-switch', 'can-Play-host-switch', 'kick-agency-host-switch', 'kick-family-host-switch'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],

                ],
            ],
            [
                'name' => 'Agency Settings',
                'sort' => 18,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 18],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'agencies-join-requests', 'except' => ['create'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse', 'edit', 'delete', 'show'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'target', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'request-agencies', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => ['accept-agency', 'refuse-agency'], 'types' => [
                        PermissionType::ADMIN->value => ['browse', 'accept-agency', 'refuse-agency'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'request-agency-history', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'Charging Agencies',
                'sort' => 19,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 19],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'appear-charger-agency', 'except' => ['delete', 'show'], 'additional' => ['delete-switch'], 'types' => [
                        PermissionType::ADMIN->value => ['browse', 'create', 'edit', 'delete-switch'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'charge-agency', 'except' => [], 'additional' => ['actions-switch'], 'types' => [
                        PermissionType::ADMIN->value => ['browse', 'create', 'edit', 'delete', 'show', 'actions-switch'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'payment-gat-way', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'agency-manger-setting', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'Payment-methods-for-shipping-agencies', 'except' => ['browse', 'show'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['create', 'edit', 'delete'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'Agency Manager',
                'sort' => 20,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 20],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    [
                        'key' => 'BD',
                        'except' => [],
                        'additional' => ['delete-switch', 'choose-switch', 'stop-salary-switch'],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse', 'delete-switch', 'choose-switch', 'stop-salary-switch', 'create', 'edit', 'delete', 'show'],
                            //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                        ],
                    ],

                ],
            ],
            [
                'name' => 'Room',
                'sort' => 21,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 21],
                    PermissionType::SUPER_ADMIN->value => ['sort' => 6],
                    PermissionType::AREA_MANAGER->value => ['sort' => 7],
                ],
                'permissions' => [
                    ['key' => 'rooms', 'except' => ['create'], 'additional' => ['actions-switch', 'pin-switch', 'close-switch'], 'types' => [
                        PermissionType::ADMIN->value => ['browse', 'show', 'edit', 'delete', 'actions-switch', 'pin-switch', 'close-switch'],
                        PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                        PermissionType::AREA_MANAGER->value => ['browse', 'show'],
                    ],],
                    ['key' => 'live-rooms', 'except' => ['create'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse', 'show', 'edit', 'delete',],
                        PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                        PermissionType::AREA_MANAGER->value => ['browse', 'show'],
                    ],],
                    ['key' => 'categories', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'room-vip', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'room-background', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'emoji', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'room-settings', 'except' => ['create', 'delete', 'show'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse', 'edit'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'Gift',
                'sort' => 22,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 22],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [

                    ['key' => 'gift', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'gift-logs', 'except' => ['create', 'delete', 'show', 'edit'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'lucky-gift-setting', 'except' => ['create', 'delete', 'show', 'edit'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'Cp',
                'sort' => 23,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 23],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [

                    ['key' => 'cp-report', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => ['cancel-cp-switch'], 'types' => [
                        PermissionType::ADMIN->value => ['browse', 'cancel-cp-switch'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'cp-relation', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'weekly-cp', 'except' => ['show'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse', 'create', 'edit', 'delete',],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'Achievements',
                'sort' => 24,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 24],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'achievement', 'except' => ['create', 'delete'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse', 'edit',],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'achievement_level', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'user_achievement_level', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'Group chat',
                'sort' => 25,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 25],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'group-chat', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'updates_group_chat', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'Lucky box',
                'sort' => 26,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 26],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'boxes', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'box-use', 'except' => ['create'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse', 'edit', 'delete', 'show'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'box-settings', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'Events',
                'sort' => 27,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 27],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'event-period', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'weekly-star', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'weekly_star_rewards', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'target-event', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'gift-target-event', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'pk-event', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'pk-event-rewards', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'general-roles', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'event_report', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => ['return-switch'], 'types' => [
                        PermissionType::ADMIN->value => ['browse', 'return-switch'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'Reels',
                'sort' => 28,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 28],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'Real', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'report-real', 'except' => ['create', 'edit', 'show'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse', 'delete'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'Moment',
                'sort' => 29,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 29],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'Moment', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'report-moment', 'except' => ['edit', 'delete', 'show'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse', 'create'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'Employees and Permissions',
                'sort' => 30,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 30],
                    PermissionType::SUPER_ADMIN->value => ['sort' => 9],
                    PermissionType::AREA_MANAGER->value => ['sort' => 9],
                ],
                'permissions' => [
                    ['key' => 'auth-users', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        PermissionType::SUPER_ADMIN->value => $defaultMethods,
                        PermissionType::AREA_MANAGER->value => $defaultMethods,
                    ],],
                    ['key' => 'roles', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        PermissionType::SUPER_ADMIN->value => $defaultMethods,
                        PermissionType::AREA_MANAGER->value => $defaultMethods,
                    ],],
                    ['key' => 'roles-reward', 'except' => ['show'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'Work Settings',
                'sort' => 31,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 31],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'delete-account-details', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'questions', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'country', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'page', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'payment-coin', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'coins', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'exchange', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'gold-coins', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'Sensitive Settings',
                'sort' => 32,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 32],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'updates', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'config', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'pusher-statistics', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'System Settings',
                'sort' => 33,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 33],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'settings', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'language', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'daily-prize', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'daily-gift', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'Level',
                'sort' => 34,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 34],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'level', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'level-interval', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'reward-level-interval', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'user parent',
                'sort' => 35,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 35],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'user-parent', 'except' => ['edit', 'delete', 'show'], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => ['browse', 'create'],
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'tribe events',
                'sort' => 36,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 36],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'tribe-periods', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'tribe-tops', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'tribe-rewards', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                ],
            ],
            [
                'name' => 'Room Boom',
                'sort' => 37,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 37],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'room-boom-levels', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    ['key' => 'room-boom-rewards', 'except' => [], 'additional' => [], 'types' => [
                        PermissionType::ADMIN->value => $defaultMethods,
                        //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                    ],],
                    [
                        'key' => 'room-boom-winners',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => [],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse'],
                            //PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'reward',
                'sort' => 38,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 38],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    [
                        'key' => 'user-reward',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => [],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse'],

                        ],
                    ],

                ],
            ],
            [
                'name' => 'Milestone',
                'sort' => 39,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 39],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    ['key' => 'milestone', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => ['dedicate-switch'], 'types' => [
                        PermissionType::ADMIN->value => ['browse', 'dedicate-switch'],

                    ],],

                ],
            ],
            [
                'name' => 'Bds',
                'sort' => 38,
                'types' => [
                    PermissionType::AREA_MANAGER->value => ['sort' => 4],
                    PermissionType::SUPER_ADMIN->value => ['sort' => 3],
                ],
                'permissions' => [
                    [
                        'key' => 'Bds',
                        'except' => [],
                        'additional' => ['delete-switch', 'choose-switch', 'stop-salary-switch'],
                        'types' => [
                            PermissionType::SUPER_ADMIN->value => ['browse', 'delete-switch', 'choose-switch', 'stop-salary-switch', 'create', 'edit', 'delete', 'show'],
                            PermissionType::AREA_MANAGER->value => ['browse', 'delete-switch', 'choose-switch', 'stop-salary-switch', 'create', 'edit', 'delete', 'show'],


                        ],
                    ],
                    [
                        'key' => 'professional-bd',
                        'except' => ['delete'],
                        'create',
                        'edit',
                        'additional' => [],
                        'types' => [
                            PermissionType::SUPER_ADMIN->value => ['browse', 'show'],
                            PermissionType::AREA_MANAGER->value => ['browse', 'show'],

                        ],
                    ],

                ],
            ],
            [
                'name' => 'Agencies',
                'sort' => 39,
                'types' => [

                    PermissionType::SUPER_ADMIN->value => ['sort' => 5],
                    PermissionType::AREA_MANAGER->value => ['sort' => 6],
                ],
                'permissions' => [


                    ['key' => 'agency', 'except' => ['delete'], 'additional' => ['delete-switch', 'change-users-agency-switch',], 'types' => [

                        PermissionType::SUPER_ADMIN->value => ['browse', 'delete-switch', 'change-users-agency-switch', 'show', 'create', 'edit',],
                        PermissionType::AREA_MANAGER->value => ['browse', 'delete-switch', 'change-users-agency-switch', 'show', 'create', 'edit',],

                    ],],
                    ['key' => 'shipping-agency', 'except' => ['delete', 'show'], 'additional' => ['delete-switch', 'switches-switch'], 'types' => [

                        PermissionType::SUPER_ADMIN->value => ['browse', 'create', 'edit', 'delete-switch'],
                        PermissionType::AREA_MANAGER->value => ['browse', 'create', 'edit', 'delete-switch'],

                    ],],

                    ['key' => 'professional-users', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => [], 'types' => [

                        PermissionType::SUPER_ADMIN->value => ['browse'],
                        PermissionType::AREA_MANAGER->value => ['browse'],
                    ],],
                    ['key' => 'host', 'except' => ['create'], 'additional' => [], 'types' => [
                        PermissionType::SUPER_ADMIN->value => ['browse', 'edit', 'delete', 'show',],
                        PermissionType::AREA_MANAGER->value => ['browse', 'edit', 'delete', 'show',],
                    ],],

                ],
            ],
            [
                'name' => 'super rewards',
                'sort' => 40,
                'types' => [

                    PermissionType::SUPER_ADMIN->value => ['sort' => 8],
                ],
                'permissions' => [

                    ['key' => 'reward-center', 'except' => ['create', 'edit', 'delete', 'show'], 'additional' => ['dedicate-switch'], 'types' => [

                        PermissionType::SUPER_ADMIN->value => ['browse', 'dedicate-switch'],
                    ],],

                ],
            ],
            [
                'name' => 'SuperAdmin',
                'sort' => 40,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 40],
                    PermissionType::AREA_MANAGER->value => ['sort' => 2],
                ],
                'permissions' => [
                    [
                        'key' => 'superadmin',
                        'except' => ['show'],
                        'additional' => [],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse', 'create', 'edit', 'delete'],
                            PermissionType::AREA_MANAGER->value => ['browse', 'create'],

                        ],
                    ],
                    [
                        'key' => 'superadmin-settings',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => [],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse'],

                        ],
                    ],
                ],
            ],
            [
                'name' => 'SuperAdmin Charge',
                'sort' => 41,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 41],
                    PermissionType::AREA_MANAGER->value => ['sort' => 3],
                ],
                'permissions' => [
                    [
                        'key' => 'charge-to-superadmin',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => ['add-switch', 'history-switch'],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse', 'add-switch', 'history-switch'],
                            PermissionType::AREA_MANAGER->value => ['browse', 'add-switch', 'history-switch'],

                        ],
                    ],
                ],
            ],
            [
                'name' => 'SuperAdmin Banners',
                'sort' => 42,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 42],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    [
                        'key' => 'superadmin-banners',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => ['add-switch', 'history-switch'],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse', 'add-switch', 'history-switch'],

                        ],
                    ],
                ],
            ],
            [
                'name' => 'SuperAdmin Rewards',
                'sort' => 43,
                'types' => [
                    PermissionType::ADMIN->value => ['sort' => 42],
                    // PermissionType::SUPER_ADMIN->value => ['sort' => 10],
                ],
                'permissions' => [
                    [
                        'key' => 'super-admin-reward',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => ['dedicate-switch', 'history-switch'],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse', 'dedicate-switch', 'history-switch'],

                        ],
                    ],
                    [
                        'key' => 'super-admin-reward-history',
                        'except' => ['create', 'edit', 'delete', 'show'],
                        'additional' => [],
                        'types' => [
                            PermissionType::ADMIN->value => ['browse'],

                        ],
                    ],
                ],
            ],


        ];



        $allSlugs = [];

        foreach ($categories as $category) {

            // ✅ Ensure 'types' exists
            if (!isset($category['types'])) continue;

            foreach ($category['types'] as $type => $typeData) {
                $categoryName = $category['name'];
                $categorySlug = $categoryName;

                DB::table('role_categories')->updateOrInsert(
                    ['slug' => $categorySlug, 'type' => $type],
                    [
                        'name_en' => $categoryName,
                        'sort' => $typeData['sort'] ?? 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $categoryId = DB::table('role_categories')
                    ->where('slug', $categorySlug)
                    ->where('type', $type)
                    ->value('id');

                foreach ($category['permissions'] as $perm) {
                    if (!isset($perm['types'])) continue;

                    foreach ($perm['types'] as $permType => $methods) {
                        if ($permType != $type) continue;

                        foreach ($methods as $method) {
                            if (in_array($method, $perm['except'] ?? [])) continue;

                            $slug = "{$method}-{$perm['key']}";
                            $name = ucfirst($method) . ' ' . str_replace('-', ' ', $perm['key']);

                            // 🔹 Save category as slug
                            DB::table('admin_permissions')->updateOrInsert(
                                ['slug' => $slug],
                                [
                                    'name' => $name,
                                    'category' => $categorySlug,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]
                            );

                            $permissionId = DB::table('admin_permissions')
                                ->where('slug', $slug)
                                ->value('id');

                            $allSlugs[] = $slug;

                            DB::table('permission_types')->updateOrInsert(
                                ['permission_id' => $permissionId, 'type' => $type],
                                [
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]
                            );
                        }

                        foreach ($perm['additional'] as $extra) {

                            // Only add this extra if it exists in the current $permType list
                            if (!in_array($extra, $perm['types'][$type] ?? [])) {
                                continue; // skip if this type doesn't have this additional permission
                            }

                            $slug = "{$extra}-{$perm['key']}";
                            $name = ucfirst(str_replace('-', ' ', $slug));

                            DB::table('admin_permissions')->updateOrInsert(
                                ['slug' => $slug],
                                [
                                    'name' => $name,
                                    'category' => $categorySlug,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]
                            );

                            $permissionId = DB::table('admin_permissions')
                                ->where('slug', $slug)
                                ->value('id');

                            $allSlugs[] = $slug;

                            DB::table('permission_types')->updateOrInsert(
                                ['permission_id' => $permissionId, 'type' => $type],
                                [
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]
                            );
                        }
                    }
                }
            }
        }
        $lastSort = RoleCategory::where('type', PermissionType::ADMIN->value)->max('sort');
        RoleCategory::updateOrCreate(
            ['name_en' => 'general'],
            ['slug' => 'general', 'sort' => $lastSort + 1]
        );
        DB::table('admin_permissions')
            ->whereNull('category')
            ->update(['category' => 'general']);


        DB::table('admin_permissions')
            ->whereNotIn('slug', $allSlugs)
            ->where('slug', '!=', '*')
            ->delete();
    }
}
