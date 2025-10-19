<?php

namespace App\SuperAdmin\Controllers;

use App\Models\Role;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\Permission;
use Illuminate\Support\Str;
use App\Enums\PermissionType;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Auth;
use App\Admin\Controllers\MainController;
use Modules\RoleRewards\Actions\DeleteRole;
use Encore\Admin\Controllers\AdminController;
use Modules\RoleRewards\Helpers\UserRoleRewardHelper;

class RoleController extends MainController
{
    public $permission_name = 'roles';
    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return trans('Roles');
    }

    public function index(Content $content)
    {
        return $content
            ->title(__('Roles'))
            ->body($this->grid());
    }

    public function edit($id, Content $content)
    {
        return  $content
            ->title($this->title())
            ->description($this->description['edit'] ?? trans('admin.edit'))
            ->body($this->form($id)->edit($id));
    }
    public function create(Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description['create'] ?? trans('admin.create'))
            ->body($this->form());
    }

    public function store()
    {
        return $this->form()->store();
    }
    public function show($id, Content $content)
    {
        return $content
            ->title(trans(__('Roles')))
            ->body($this->detail($id));
    }
    public function update($id)
    {
        return $this->form()->update($id);
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        \Admin::js('js/admin/preview.js');
        $roleModel = config('admin.database.roles_model');

        $grid = new Grid(new $roleModel());
        $grid->model()->where('admin_id', Auth::id());
        $grid->column('id', 'ID')->sortable();
        $grid->column('slug', trans('admin.slug'));

        $grid->column('name', trans('admin.name'));

        $grid->column('preview', trans('admin.preview'))->display(function () {
            $id = $this->id; // Assuming 'id' is the record ID field
            return '<a href="javascript:void(0);" onclick="openPreview(' . $id . ')">
                <i class="fa fa-eye"></i>
            </a>';
        });

        $grid->column('rewards', __('Rewards'))->display(function () {
            $url = admin_url("role-rewards/{$this->id}");
            return '<a href="' . $url . '" class="btn btn-sm btn-info">
                        <i class="fa fa-gift"></i> ' . __('rewards') . '
                    </a>';
        });
        // $grid->column('permissions', trans('admin.permission'))->pluck('name')->take(7)->label();
        $grid->column('permissions', trans('admin.permission'))->display(function ($permissions) {
            return collect($permissions)->pluck('name')->take(7)->map(function ($name) {
                return __($name);
            });
        })->label();
        $grid->column('created_at', trans('admin.created_at'));
        $grid->column('updated_at', trans('admin.updated_at'));




        $grid->actions(function (Grid\Displayers\Actions $actions) {
            // $protectedSlugs = ['administrator', 'admin', 'developer', 'agency', 'charger'];

            // if (in_array($actions->row->slug, $protectedSlugs)) {
            //     $actions->disableDelete(); 
            // } else {
            $actions->disableDelete();
            $actions->add(new DeleteRole());
            // }
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });




        $grid->disableExport();
       // $this->extendGrid($grid);
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $roleModel = config('admin.database.roles_model');

        $show = new Show($roleModel::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('slug', trans('admin.slug'));
        $show->field('name', trans('admin.name'));
        $show->field('permissions', trans('admin.permissions'))->as(function ($permission) {
            return $permission->pluck('name');
        })->label();
        $show->field('created_at', trans('admin.created_at'));
        $show->field('updated_at', trans('admin.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */

    public function form($id = null)
    {
        $permissionModel = config('admin.database.permissions_model');
        $permissions = Permission::with('permissionTypes')
            ->whereHas('permissionTypes', fn($q) => $q->where('type', 'super_admin'))
            ->get();
        $roleModel = config('admin.database.roles_model');

        $form = new Form(new $roleModel());
        $this->disableFormTools($form);

        // $form->text('slug', trans('admin.slug'))->rules('required|unique:admin_roles,slug,{{id}}');

        $form->text('name', trans('role name'))->rules('required|unique:admin_roles,name,{{id}}');

        // Hide default listbox and use custom tabbed permission UI
        // $form->listbox('permissions', trans('admin.permissions'))->options($permissionModel::all()->pluck('name', 'id'));

        // Custom tabbed view
       // dd(Role::where('id', $id)->first()->permissions->pluck('id')->toArray());
        $form->html(view('admin.super-admin-permission-tabs', [
            'permissions' => $permissions,
            
            'selectedPermissions' => $id != null ? Role::where('id', $id)->first()->permissions->pluck('id')->toArray() : [],
        ])->render());

        $form->text('desc_en', __('Description en'));
        $form->text('desc_ar', __('Description ar'));
        $form->image('image', __('Image'))->help('');

        $form->saving(function (Form $form) {
            $form->ignore('permissions');
                        $form->model()->admin_id = Auth::id();
                        $form->model()->type = PermissionType::SUPER_ADMIN->value;
            // Automatically generate slug from name *before saving*
            $form->model()->slug = Str::slug($form->name);
        });
        $form->saving(function (Form $form) {
            $form->ignore('permissions'); // handled manually
        });

        $form->saved(function (Form $form) {
            $form->slug = Str::slug(request('name'));
            $form->admin_id = Auth::id();
            $all = request('permissions_all');
            $selectedPermissionIds = array_filter(explode(',', $all));

            $permissionModel = config('admin.database.permissions_model');
            $allPermissions = $permissionModel::all();

            $slugToId = $allPermissions->pluck('id', 'slug')->toArray();
            $idToSlug = $allPermissions->pluck('slug', 'id')->toArray();

            $resourceActions = [];
            foreach ($selectedPermissionIds as $pid) {
                $slug = $idToSlug[$pid] ?? '';
                if (preg_match('/^(browse|create|edit|delete)\-(.+)$/', $slug, $m)) {
                    $action = $m[1];
                    $resource = $m[2];
                    $resourceActions[$resource][$action] = true;
                }
            }

            $finalPermissionIds = [];

            foreach ($resourceActions as $resource => $actions) {
                $hasBrowse = !empty($actions['browse']);
                $hasCrud   = !empty($actions['create']) || !empty($actions['edit']) || !empty($actions['delete']);

                if ($hasBrowse) {
                    if (isset($slugToId["browse-$resource"])) $finalPermissionIds[] = $slugToId["browse-$resource"];
                    foreach (['create', 'edit', 'delete'] as $act) {
                        if (!empty($actions[$act]) && isset($slugToId["$act-$resource"])) {
                            $finalPermissionIds[] = $slugToId["$act-$resource"];
                        }
                    }
                } elseif ($hasCrud) {
                    if (isset($slugToId["browse-$resource"])) $finalPermissionIds[] = $slugToId["browse-$resource"];
                    foreach (['create', 'edit', 'delete'] as $act) {
                        if (!empty($actions[$act]) && isset($slugToId["$act-$resource"])) {
                            $finalPermissionIds[] = $slugToId["$act-$resource"];
                        }
                    }
                }
            }

            foreach ($selectedPermissionIds as $pid) {
                $slug = $idToSlug[$pid] ?? '';
                if (!preg_match('/^(browse|create|edit|delete)\-(.+)$/', $slug)) {
                    $finalPermissionIds[] = $pid;
                }
            }

            $finalPermissionIds = array_unique($finalPermissionIds);

            $form->model()->permissions()->sync($finalPermissionIds);

            admin_toastr(__('Updated successfully'), 'success');
        });

        return $form;
    }


    public function form1()
    {
        $permissionModel = config('admin.database.permissions_model');
        $roleModel = config('admin.database.roles_model');

        $form = new Form(new $roleModel());

        $roleId = request()->route('role');

        $form->text('name', trans('admin.name'))->rules('required|unique:admin_roles,name,' . $roleId);
        $form->listbox('permissions', trans('admin.permissions'))->options($permissionModel::all()->pluck('name', 'id'));

        $form->display('created_at', trans('admin.created_at'));
        $form->display('updated_at', trans('admin.updated_at'));

        return $form;
    }


    public function getPermissionsByCategory($category)
    {
        $permissionModelClass = config('admin.database.permissions_model');

        $permissions = $permissionModelClass::where('category', $category)->get();
        $data = $permissions->map(function ($perm) {
            return [
                'id' => $perm->id,
                'name' => __($perm->name),
            ];
        });

        return response()->json(['permissions' => $data]);
    }


    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        UserRoleRewardHelper::revokeRewardsFromAllUsersForRole($role->id, $role->slug);

        return parent::destroy($id);
    }
}
