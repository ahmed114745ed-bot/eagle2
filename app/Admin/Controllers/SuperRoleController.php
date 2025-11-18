<?php

namespace App\Admin\Controllers;

use App\Models\Role;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\Permission;
use Illuminate\Support\Str;
use App\Enums\PermissionType;
use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;
use Illuminate\Http\Request;

class SuperRoleController extends MainController
{
    public $permission_name = 'super-roles';
    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return trans('Roles');
    }

    public function index(Content $content)
    {
        $permissionType =  'super_admin';
        $permissions = Permission::whereHas('permissionTypes', function ($q) use ($permissionType) {
            $q->where('type', $permissionType);
        })->get();

        $selectedPermissions =  Role::where('slug', 'super-admin')->first()->permissions->pluck('id')->toArray() ?? [];
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $areaPermissionType =  'area-manager';

        // load permissions based on selected type
        $areaPermissions = Permission::whereHas('permissionTypes', function ($q) use ($areaPermissionType) {
            $q->where('type', $areaPermissionType);
        })->get();



        $areaSelectedPermissions =  Role::where('slug', 'area-manager')->first()->permissions->pluck('id')->toArray() ?? [];
        $areaManagerRole = Role::where('slug', 'area-manager')->first();
        return parent::index($content
            ->header(__('Roles'))
            ->description('   ')
            ->body(view('admin.super-Permission-tabs', compact(['permissions', 'areaManagerRole', 'superAdminRole', 'permissionType', 'selectedPermissions', 'areaSelectedPermissions', 'areaPermissions', 'areaPermissionType']))));
    }





    public function updatePermissionRole(Request $request)
    {

        $permission = request('permissions');
        $id = $request->role_id;
        $this->syncRolePermissions($permission, $id);

        admin_toastr(__('Updated successfully'), 'success');
        return back();
    }

    protected function syncRolePermissions($permissionString, $id)
    {


        // Sync with the role
        $role = Role::find($id);
        if ($role) {
            $role->permissions()->sync($permissionString);
        }
    }
}
