<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\AgencySallary;
use App\Models\User;
use App\Tik\Services\BackgroundService;
use Illuminate\Support\Facades\Validator;
use App\Tik\Services\RequestBackgroundImagService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    public function index()
    {
        $roleModel = config('admin.database.roles_model');

        $roles = $roleModel::with('permissions:name')
                    ->select('id', 'slug', 'name', 'created_at', 'updated_at')
                    ->get();

        $roles = $roles->map(function ($role) {
            $role->can_delete = !in_array($role->slug, ['administrator', 'admin', 'developer', 'agency', 'charger']);
            $role->permissions = $role->permissions->pluck('name')->take(7);
            return $role;
        });

        return Common::apiResponse(1, '', $roles);
    }

    public function store(Request $request)
    {
       
        $validator = Validator::make($request->all(), [
            'slug' => 'required|string|max:255',
            'name' => 'required|string|max:255|unique:admin_roles,name',
            'permissions' => 'required',
        ]);
        if ($validator->fails()) {

            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        $roleModel = config('admin.database.roles_model');

        $role = new $roleModel();
        $role->slug = $request->input('slug');
        $role->name = $request->input('name');
        $role->save();

        if (is_string($request->permissions)) {
            $permissions = json_decode($request->permissions, true);
        }

        $role->permissions()->sync($permissions);
        return Common::apiResponse(1, 'Role created successfully', $role);
    }

    public function permissions()
    {
        $permissionModel = config('admin.database.permissions_model');

        $permissions = $permissionModel::all()->pluck('name', 'id');
        return Common::apiResponse(1, '', $permissions);
    }

    public function update(Request $request, $id)
    {
        $roleModel = config('admin.database.roles_model');
        $role = $roleModel::findOrFail($id);

        $validated = $request->validate([
            'slug' => 'required|string|max:255',
            'name' => 'required|string|max:255|unique:admin_roles,name,' . $id,
            'permissions' => 'required',
            // 'permissions.*' => 'exists:admin_permissions,id',
        ]);

        $role->slug = $request->input('slug');
        $role->name = $request->input('name');
        $role->save();

        if (is_string($request->permissions)) {
            $permissions = json_decode($request->permissions, true);
        }
        $role->permissions()->sync($permissions);
        return Common::apiResponse(1, 'Role updated successfully', $role);
    }

    public function destroy($id)
    {
        $roleModel = config('admin.database.roles_model');
        $role = $roleModel::findOrFail($id);

        if (in_array($role->slug, ['administrator', 'admin', 'developer', 'agency', 'charger'])) {
            return response()->json([
                'message' => 'You cannot delete this role.',
            ], 403); // 403 Forbidden
        }

        $role->delete();
        return Common::apiResponse(1, 'Role deleted successfully');
    }

    public function permissionsCategory()
    {
        $permissionModel = config('admin.database.permissions_model');

        $permissions =Permission::orderBy('category')->get();
        return Common::apiResponse(1, '', $permissions);
    }
}
 