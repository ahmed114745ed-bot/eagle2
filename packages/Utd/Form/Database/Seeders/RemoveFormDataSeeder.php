<?php

namespace Utd\Form\Database\Seeders;

use Encore\Admin\Auth\Database\Menu;
use Encore\Admin\Auth\Database\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RemoveFormDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->removeMenus();
        $this->removePermissions();
    }

    protected function removeMenus(): void
    {
        $parent = Menu::where('title', 'registration forms')->first();

        if ($parent) {
            Menu::where('parent_id', $parent->id)->delete();
            $parent->delete();
            $this->command->info('Deleted form menus (registration forms + children).');
        } else {
            $this->command->warn('No form menus found.');
        }
    }

    protected function removePermissions(): void
    {
        $slugs = [
            'browse-templates-form',
            'edit-templates-form',
            'show-templates-form',
            'browse-form-request',
            'type-switch-form-request',
            'approve-switch-form-request',
            'reject-switch-form-request',
            'show-form-request',
        ];

        $permissions = Permission::whereIn('slug', $slugs)->get();
        $permissionIds = $permissions->pluck('id')->toArray();

        if (!empty($permissionIds)) {
            DB::table('permission_types')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('admin_role_permissions')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('admin_user_permissions')->whereIn('permission_id', $permissionIds)->delete();
            Permission::whereIn('id', $permissionIds)->delete();
            $this->command->info('Deleted ' . count($permissionIds) . ' form permissions + types.');
        } else {
            $this->command->warn('No form permissions found.');
        }
    }
}
