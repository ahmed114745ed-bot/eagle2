<?php

namespace Utd\Agency\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AgencyMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $existingParent = DB::table('admin_menu')
            ->where('title', 'Agency System')
            ->where(function ($query) {
                $query->whereNull('parent_id')
                    ->orWhere('parent_id', 0);
            })
            ->first();

        if ($existingParent) {
            // Menu exists, just ensure it's linked to roles
            $this->linkMenusToRoles($existingParent->id);
            $this->command->info('   ℹ️  Agency menu already exists, linked to roles.');

            return;
        }

        // Get the maximum order
        $maxOrder = DB::table('admin_menu')->max('order') ?? 0;
        $baseOrder = $maxOrder + 1;

        // Store all created menu IDs
        $createdMenuIds = [];

        // Create main menu: Agency System
        $agencySystemId = DB::table('admin_menu')->insertGetId([
            'parent_id' => 0,
            'order' => $baseOrder,
            'title' => 'Agency System',
            'icon' => 'fa-bar-chart-o',
            'uri' => null,
            'permission' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $createdMenuIds[] = $agencySystemId;

        // Sub-menu: Host Agencies
        $hostAgenciesId = DB::table('admin_menu')->insertGetId([
            'parent_id' => $agencySystemId,
            'order' => $baseOrder + 1,
            'title' => 'Host Agencies',
            'icon' => 'fa-bars',
            'uri' => null,
            'permission' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Host Agencies children
        $hostAgenciesChildren = [
            [
                'title' => 'Agencies',
                'icon' => 'fa-bookmark',
                'uri' => 'host-agencies',
                'permission' => 'browse-agencies',
            ],
            [
                'title' => 'Hosts',
                'icon' => 'fa-users',
                'uri' => 'ag/users',
                'permission' => 'browse-hosts',
            ],
            [
                'title' => 'Host Diamonds',
                'icon' => 'fa-diamond',
                'uri' => 'ag/host-diamonds',
                'permission' => 'browse-host-diamond',
            ],
            [
                'title' => 'Hosts Target',
                'icon' => 'fa-blind',
                'uri' => 'ag/userTarget',
                'permission' => 'browse-hosts-target',
            ],
            [
                'title' => 'Agency Target',
                'icon' => 'fa-target',
                'uri' => 'ag/target',
                'permission' => 'browse-agency-target',
            ],
            [
                'title' => 'Users Salaries',
                'icon' => 'fa-shopping-bag',
                'uri' => 'agency-salaries',
                'permission' => 'browse-users-Wallet',
            ],
            [
                'title' => 'Charges',
                'icon' => 'fa-money',
                'uri' => 'ag/charges',
                'permission' => 'browse-agency-charges',
            ],
            [
                'title' => 'Achieved Target',
                'icon' => 'fa-anchor',
                'uri' => 'ag/userTarget',
                'permission' => 'browse-achieved-Target',
            ],
            [
                'title' => 'Users Joined Agencies',
                'icon' => 'fa-users',
                'uri' => 'users-joined-agencies',
                'permission' => 'browse-users-joined-agencies',
            ],
        ];

        foreach ($hostAgenciesChildren as $index => $menu) {
            DB::table('admin_menu')->insert([
                'parent_id' => $hostAgenciesId,
                'order' => $baseOrder + 10 + $index,
                'title' => $menu['title'],
                'icon' => $menu['icon'],
                'uri' => $menu['uri'],
                'permission' => $menu['permission'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Sub-menu: Host Agencies Settings
        $hostSettingsId = DB::table('admin_menu')->insertGetId([
            'parent_id' => $hostAgenciesId,
            'order' => $baseOrder + 20,
            'title' => 'Host Agencies Settings',
            'icon' => 'fa-bars',
            'uri' => null,
            'permission' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Host Agencies Settings children
        $hostSettingsChildren = [
            [
                'title' => 'Agency join requests',
                'icon' => 'fa-500px',
                'uri' => 'agency-join-requests',
                'permission' => 'browse-agencies-join-requests',
            ],
            [
                'title' => 'Agency Requests (ag-req)',
                'icon' => 'fa-tags',
                'uri' => 'ag/ag-req',
                'permission' => 'browse-agency-requests',
            ],
            [
                'title' => 'Change Agency Manager',
                'icon' => 'fa-exchange',
                'uri' => 'change_agencies_manger',
                'permission' => 'browse-change-agency-manger',
            ],
        ];

        foreach ($hostSettingsChildren as $index => $menu) {
            DB::table('admin_menu')->insert([
                'parent_id' => $hostSettingsId,
                'order' => $baseOrder + 21 + $index,
                'title' => $menu['title'],
                'icon' => $menu['icon'],
                'uri' => $menu['uri'],
                'permission' => $menu['permission'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Sub-menu: Charging Agencies
        $chargingAgenciesId = DB::table('admin_menu')->insertGetId([
            'parent_id' => $agencySystemId,
            'order' => $baseOrder + 30,
            'title' => 'Charging Agencies',
            'icon' => 'fa-bars',
            'uri' => null,
            'permission' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Charging Agencies children
        $chargingAgenciesChildren = [
            [
                'title' => 'Charge agents',
                'icon' => 'fa-bars',
                'uri' => 'charge-agencies',
                'permission' => 'browse-appear-charger-agency',
            ],
            [
                'title' => 'Verified Charging Agents',
                'icon' => 'fa-bars',
                'uri' => 'agency-country',
                'permission' => 'browse-charge-agency',
            ],
            [
                'title' => 'Payment Methods for Charging Agents',
                'icon' => 'fa-bars',
                'uri' => '/payment-gateways',
                'permission' => 'browse-payment-gat-way',
            ],
            [
                'title' => 'Settings charging agencies',
                'icon' => 'fa-gear',
                'uri' => 'agency-setting-manger',
                'permission' => 'browse-agency-manger-setting',
            ],
        ];

        foreach ($chargingAgenciesChildren as $index => $menu) {
            DB::table('admin_menu')->insert([
                'parent_id' => $chargingAgenciesId,
                'order' => $baseOrder + 31 + $index,
                'title' => $menu['title'],
                'icon' => $menu['icon'],
                'uri' => $menu['uri'],
                'permission' => $menu['permission'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Sub-menu: Agency Manager (BD)
        $agencyManagerId = DB::table('admin_menu')->insertGetId([
            'parent_id' => $agencySystemId,
            'order' => $baseOrder + 40,
            'title' => 'Agency Manager',
            'icon' => 'fa-bars',
            'uri' => null,
            'permission' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Agency Manager children
        $agencyManagerChildren = [
            [
                'title' => 'Agency Manager Agencies',
                'icon' => 'fa-building',
                'uri' => 'agencies-agency-manger',
                'permission' => 'browse-agency-manger-agencies',
            ],
            [
                'title' => 'Agency Manager Users',
                'icon' => 'fa-users',
                'uri' => 'agency-manger-users',
                'permission' => 'browse-agency-manger-users',
            ],
            [
                'title' => 'Agency Manager Target',
                'icon' => 'fa-bullseye',
                'uri' => 'agency-manger-target',
                'permission' => 'browse-agency-manger-target',
            ],
        ];

        foreach ($agencyManagerChildren as $index => $menu) {
            DB::table('admin_menu')->insert([
                'parent_id' => $agencyManagerId,
                'order' => $baseOrder + 41 + $index,
                'title' => $menu['title'],
                'icon' => $menu['icon'],
                'uri' => $menu['uri'],
                'permission' => $menu['permission'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Agency System Settings
        DB::table('admin_menu')->insert([
            'parent_id' => $agencySystemId,
            'order' => $baseOrder + 50,
            'title' => 'Agency System Settings',
            'icon' => 'fa-gear',
            'uri' => '/agency-settings',
            'permission' => 'browse-agency-settings',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Salary Pay
        DB::table('admin_menu')->insert([
            'parent_id' => $agencySystemId,
            'order' => $baseOrder + 2,
            'title' => 'Salary Pay',
            'icon' => 'fa-money',
            'uri' => 'agency-salaries',
            'permission' => 'browse-salary',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Link all created menus to roles
        $this->linkMenusToRoles($agencySystemId);

        $this->command->info('   ✅ Agency menu items created and linked to roles.');
    }

    /**
     * Link menu and all its children to admin roles.
     */
    protected function linkMenusToRoles(int $parentMenuId): void
    {
        // Get all menu IDs (parent and children)
        $menuIds = [$parentMenuId];
        $this->getChildMenuIds($parentMenuId, $menuIds);

        // Get admin role ID (usually role 1 or find by slug)
        $adminRoles = DB::table('admin_roles')
            ->whereIn('slug', ['administrator', 'admin', 'super-admin', 'developer'])
            ->orWhereIn('name', ['Administrator', 'Admin', 'super-admin', 'developer'])
            ->pluck('id')
            ->toArray();

        // If no admin roles found, use role ID 1
        if (empty($adminRoles)) {
            $adminRoles = [1];
        }

        foreach ($menuIds as $menuId) {
            foreach ($adminRoles as $roleId) {
                // Check if link already exists
                $exists = DB::table('admin_role_menu')
                    ->where('role_id', $roleId)
                    ->where('menu_id', $menuId)
                    ->exists();

                if (! $exists) {
                    DB::table('admin_role_menu')->insert([
                        'role_id' => $roleId,
                        'menu_id' => $menuId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Get all child menu IDs recursively.
     */
    protected function getChildMenuIds(int $parentId, array &$ids): void
    {
        $children = DB::table('admin_menu')
            ->where('parent_id', $parentId)
            ->pluck('id')
            ->toArray();

        foreach ($children as $childId) {
            $ids[] = $childId;
            $this->getChildMenuIds($childId, $ids);
        }
    }
}
