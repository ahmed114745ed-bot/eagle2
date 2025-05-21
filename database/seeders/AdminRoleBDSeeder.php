<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminRoleBDSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = DB::table('admin_roles')->where('slug', 'bd')->first();

        if (!$role) {
            $roleId = DB::table('admin_roles')->insertGetId([
                'name'       => 'BD',
                'slug'       => 'bd',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $roleId = $role->id;
        }

        // Define the permissions allowed for BD
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

        // Fetch permission IDs based on slug
        $permissionIds = DB::table('admin_permissions')
            ->whereIn('slug', $permissions)
            ->pluck('id')
            ->toArray();

        // Attach permissions to BD role
        foreach ($permissionIds as $permissionId) {
            $exists = DB::table('admin_role_permissions')
                ->where('role_id', $roleId)
                ->where('permission_id', $permissionId)
                ->first();

            if (!$exists) {
                DB::table('admin_role_permissions')->insert([
                    'role_id'       => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }
 }
