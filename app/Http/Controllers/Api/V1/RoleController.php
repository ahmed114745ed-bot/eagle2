<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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

    public function show($id)
    {
        $roleModel = config('admin.database.roles_model');

        $roles = $roleModel::with('permissions:name')
            ->select('id', 'slug', 'name', 'created_at', 'updated_at')
            ->where('id', $id)->first();

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
        $permissionsPaginated = $permissionModel::paginate(50);
        $permissions = $permissionsPaginated->getCollection()->groupBy(function ($item) {
            return $item->category ?? 'other';
        });
        return Common::apiResponse(1, '', $permissions);
    }

    public function update(Request $request, $id)
    {
        try {
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
        } catch (ValidationException $e) {
            Log::info('Validation Error:', $e->errors());
            return Common::apiResponse(0, 'Validation failed', $e->errors());
        } catch (\Throwable $th) {
            Log::info('Unexpected Error: ' . $th->getMessage());
            return Common::apiResponse(0, 'An unexpected error occurred');
        }
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

    // public function permissionsCategory()
    // {
    //     $permissionModel = config('admin.database.permissions_model');
    //     $permissionsPaginated = $permissionModel::groupBy(function ($item) {
    //         return $item->category ?? 'other';
    //     })->paginate(50);
    //     // $permissions = $permissionsPaginated->getCollection()->groupBy(function ($item) {
    //     //     return $item->category ?? 'other';
    //     // });
    //     return Common::apiResponse(1, '', $permissionsPaginated);
    // }

    public function permissionsCategory()
    {
        $permissionModel = config('admin.database.permissions_model');

        // Fetch all permissions first and then group by category
        $permissionsPaginated = $permissionModel::all();

        // Group the permissions by category
        $permissions = $permissionsPaginated->groupBy(function ($item) {
            return $item->category ?? 'other';
        });

        // Prepare pagination metadata


        return Common::apiResponse(1, '', $permissions, 200);
    }

    public function preview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->messages()->toJson()], 422);
        }

        $roleId = $request->role_id;
        $password = \Str::random(12);
        $values = [
            'username' => \Str::random(9),
            'password' => $password,
        ];
        $admin = \App\Models\Admin::create([
            ...$values,
            'name' => \Str::random(9),
            'is_preview' => true,
            'password' => bcrypt($password)
        ]);

        $admin->roles()->sync(['role_id' => $roleId]);

        return response()->json(['url' =>  url('/preview/admin/login') . '?token=' . $admin->id]);
    }
}
