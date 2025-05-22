<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminRoleBDSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
  
        public function run()
        {
            $now = Carbon::now();
            DB::table('admin_permissions')->updateOrInsert([
                'slug' => 'browse-request-agencies',
            ], [
                'name'       => 'browse-request-agencies',
                'slug'       => 'browse-request-agencies',
                'http_method'=> 'GET',
                'http_path'  => 'request-agencies*',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
        
            $role = DB::table('admin_roles')->where('slug', 'bd')->first();
    
            if (!$role) {
                $roleId = DB::table('admin_roles')->insertGetId([
                    'name'       => 'BD',
                    'slug'       => 'bd',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                $roleId = $role->id;
            }
    
            $permissions = [
                'browse-agencies',
                'create-agencies',
                'edit-agencies',
                'browse-request-agencies',
                'edit-request-agencies',
                'browse-get-salary-bd',
                'browse-charge',
                'create-charge',
            ];
    
            $permissionIds = DB::table('admin_permissions')
                ->whereIn('slug', $permissions)
                ->pluck('id')
                ->toArray();
    
            foreach ($permissionIds as $permissionId) {
                DB::table('admin_role_permissions')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_id' => $permissionId]
                );
            }
    
            $menus = [
                [
                    'id' => 53,
                    'parent_id' => 0,
                    'order' => 2,
                    'title' => 'Home',
                    'icon' => 'fa-bars',
                    'uri' => 'bd',
                    'permission' => '*',
                    'created_at' => '2025-05-15 11:03:26',
                    'updated_at' => '2025-05-22 06:30:09',
                ],
                [
                    'id' => 54,
                    'parent_id' => 0,
                    'order' => 4,
                    'title' => 'agencies',
                    'icon' => 'fa-bars',
                    'uri' => 'bd/agencies',
                    'permission' => '*',
                    'created_at' => '2025-05-15 11:03:53',
                    'updated_at' => '2025-05-22 06:30:09',
                ],
                [
                    'id' => 55,
                    'parent_id' => 0,
                    'order' => 3,
                    'title' => 'salaries BD',
                    'icon' => 'fa-bars',
                    'uri' => 'bd/salaries',
                    'permission' => 'delete-wares-bd',
                    'created_at' => '2025-05-15 11:05:32',
                    'updated_at' => '2025-05-22 06:30:09',
                ],
                [
                    'id' => 57,
                    'parent_id' => 0,
                    'order' => 5,
                    'title' => 'request agencies',
                    'icon' => 'fa-bars',
                    'uri' => 'request-agencies',
                    'permission' => '*',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];
    
            foreach ($menus as $menu) {
                DB::table('admin_menu')->updateOrInsert(
                    ['id' => $menu['id']],
                    $menu
                );
    
                DB::table('admin_role_menu')->updateOrInsert(
                    ['role_id' => $roleId, 'menu_id' => $menu['id']],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
    
            $this->command->info('BD role, permissions, menus, and role menu relations seeded successfully.');
        }
    
 }
