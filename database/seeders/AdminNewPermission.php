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
        'games'




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
            ['name' => 'Fast orders', 'sort' => 1, 'permissions' => []],
            ['name' => 'Wallet', 'sort' => 2, 'permissions' => []],
            ['name' => 'charge system', 'sort' => 3, 'permissions' => []],
            ['name' => 'users', 'sort' => 4, 'permissions' => []],
            ['name' => 'Advertisements', 'sort' => 6, 'permissions' => []],
            ['name' => 'Store', 'sort' => 7, 'permissions' => []],
            ['name' => 'Distinguished identifier', 'sort' => 8, 'permissions' => []],
            ['name' => 'Vip', 'sort' => 9, 'permissions' => []],
            ['name' => 'families', 'sort' => 10, 'permissions' => []],
            ['name' => 'Agency System', 'sort' => 11, 'permissions' => []],
            ['name' => 'Internal Sales System', 'sort' => 12, 'permissions' => []],
            ['name' => 'Host Agencies', 'sort' => 13, 'permissions' => []],
            ['name' => 'Agency Settings', 'sort' => 14, 'permissions' => []],
            ['name' => 'Charging Agencies', 'sort' => 15, 'permissions' => []],
            ['name' => 'Agency Manager', 'sort' => 16, 'permissions' => []],
            ['name' => 'Room', 'sort' => 17, 'permissions' => []],
            ['name' => 'Achievements', 'sort' => 18, 'permissions' => []],
            ['name' => 'Group chat', 'sort' => 19, 'permissions' => []],
            ['name' => 'Lucky box', 'sort' => 20, 'permissions' => []],
            ['name' => 'Events', 'sort' => 21, 'permissions' => []],
            ['name' => 'Reels', 'sort' => 22, 'permissions' => []],
            ['name' => 'Moment', 'sort' => 23, 'permissions' => []],
            ['name' => 'Work Settings', 'sort' => 24, 'permissions' => []],
            ['name' => 'Sensitive Settings', 'sort' => 25, 'permissions' => []],
            ['name' => 'System Settings', 'sort' => 26, 'permissions' => []],
            ['name' => 'Level', 'sort' => 27, 'permissions' => []],
             ['name' => 'user parent', 'sort' => 28, 'permissions' => []],
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
        $actions = ['create', 'edit', 'delete', 'show', ];
        $targets = ['dashboard'];

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

        $actions = ['create', 'edit', 'show'];
        $targets = [];

        DB::table('admin_permissions')->where(function ($query) use ($actions, $targets) {
            foreach ($actions as $action) {
                foreach ($targets as $target) {
                    $query->orWhere('slug', 'like', "{$action}-{$target}");
                }
            }
        })->delete();

        $actions = ['create'];
        $targets = [];

        DB::table('admin_permissions')->where(function ($query) use ($actions, $targets) {
            foreach ($actions as $action) {
                foreach ($targets as $target) {
                    $query->orWhere('slug', 'like', "{$action}-{$target}");
                }
            }
        })->delete();
    }
}
