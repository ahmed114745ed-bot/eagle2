<?php

namespace Database\Seeders;

use App\Models\AdminRole;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuperAdminRoleSeeder extends Seeder
{
    public function run()
    {
        $role = Role::where('slug', 'super-admin')->first();

        if (!$role) {
            $role = Role::create([
                'name' => 'super-admin',
                'slug' => 'super-admin',
                'desc_en' => 'Full system access role',
                'desc_ar' => 'صلاحيات كاملة للنظام',
                'image' => null,
                'admin_id' => 1,
                'type' => 'system',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $permissions = Permission::whereHas('permissionTypes', function ($q) {
                $q->where('type', 'super_admin');
            })->pluck('id')->toArray();

            $role->permissions()->sync($permissions);

            echo "✅";
        } else {


            $permissions = Permission::whereHas('permissionTypes', function ($q) {
                $q->where('type', 'super_admin');
            })->pluck('id')->toArray();

            $role->permissions()->sync($permissions);
            echo "ℹ️ ";
        }
    }
}
