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
use Modules\RoleRewards\Actions\DeleteRole;
use Encore\Admin\Facades\Admin;

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
        return parent::index($content
            ->title(__('Roles'))
            ->body($this->grid()));
    }

    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title($this->title())
            ->description($this->description['edit'] ?? trans('admin.edit'))
            ->body($this->form($id)->edit($id)));
    }
    public function create(Content $content)
    {
        return parent::create($content
            ->title($this->title())
            ->description($this->description['create'] ?? trans('admin.create'))
            ->body($this->form()));
    }

    protected function grid()
    {
        \Admin::js('js/admin/preview.js');
        $roleModel = config('admin.database.roles_model');
        $areaManagerId = request('area_manager_id');

        if ($areaManagerId) {
            session(['area_manager_id' => $areaManagerId]);
        }

        if (request()->has('clear_area_manager')) {
            session()->forget('area_manager_id');
            $areaManagerId = null;
        }

        $roleAuthId = $areaManagerId ?? session('area_manager_id');

        $grid = new Grid(new $roleModel());
        // $grid->model()->where('admin_id', null);

        if ($roleAuthId) {
            $grid->model()->where('admin_id', $roleAuthId);
        } else {
            $grid->model()
                ->whereNull('admin_id')
                ->where(function ($q) {
                    $q->where('type', 'role-country-manager')
                        ->orWhere('type', 'role-area-manager');
                });
        }
        $grid->column('id', 'ID')->sortable();
        $grid->column('slug', trans('admin.slug'));

        $grid->column('name', trans('admin.name'));
        $grid->column('type', trans('type'));

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


    public function form($id = null)
    {
        $permissionModel = config('admin.database.permissions_model');

        $roleModel = config('admin.database.roles_model');

        $form = new Form(new $roleModel());
        $this->disableFormTools($form);

         $type = request('type') ?? 'role-country-manager';
        $form->select('type', __('type'))->options([
            'role-country-manager'        => __('country manager'),
            'role-area-manager' => __('area manager'),
        ])
            ->default($type)
            ->attribute(['id' => 'type']);

        $form->text('name', trans('role name'))->rules(function ($form) {
            // Get the record ID if editing, otherwise null
            $id = $form->model()?->id ?? null;

            // Get the type from request or from existing model when editing
            $type = PermissionType::ADMIN->value ?? $form->model()?->type;

            // Default to empty string if not found (avoids SQL issues)
            $type = $type ?? '';

            // Build unique rule with type condition
            return "required|unique:admin_roles,name," . ($id ?? 'NULL') . ",id,type," . $type;
        });



        Admin::script(<<<JS
            $('#type').on('change', function() {
                let value = $(this).val();
                // Reload page with ?type=selected-value
                let url = new URL(window.location.href);
                url.searchParams.set('type', value);
                window.location.href = url.toString();
            });
        JS);


        // default value if nothing selected yet (first load)
        if (!$type) {
            $type = $form->model()->type ?? null;
        }


        $permissionType = $type == 'role-country-manager' ? 'super_admin' : 'area-manager';

        // load permissions based on selected type
        $permissions = Permission::whereHas('permissionTypes', function ($q) use ($permissionType) {
            $q->where('type', $permissionType);
        })->get();
        $form->html(view('admin.super-Permission-tabs', [
            'permissions' => $permissions,
            'permissionType' => $permissionType,
            'selectedPermissions' => $id != null ? Role::where('id', $id)->first()->permissions->pluck('id')->toArray() : [],
        ])->render());

        $form->text('desc_en', __('Description en'));
        $form->text('desc_ar', __('Description ar'));
        $form->image('image', __('Image'))->help('');

        $form->saving(function (Form $form) {
            $form->ignore('permissions');

            // Automatically generate slug from name *before saving*
            $form->model()->slug = Str::slug($form->name);
        });
        $form->saving(function (Form $form) {
            $form->ignore('permissions'); // handled manually
        });

        $form->saved(function (Form $form) {
            $form->slug = Str::slug(request('name'));

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
            $url = url('admin/auth/super-roles');
            return redirect()->to($url);
        });



        return $form;
    }
}
