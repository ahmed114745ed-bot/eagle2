<?php

namespace App\Admin\Controllers;

use App\Models\Ware;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use App\Selectables\Badges;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Modules\Vip\Entities\OVip;
use App\Selectables\SuperAdmins;
use App\Selectables\WaresByType;
use Encore\Admin\Layout\Content;
use Modules\Badge\Entities\Badge;
use App\Admin\Services\UserService;
use App\Admin\Controllers\MainController;
use Modules\SuperAdmin\Entities\SuperAdmin;
use Modules\SuperAdmin\Entities\SuperAdminReward;

class SuperAdminRewardControllerHistory extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'SuperAdminReward';

    public $permission_name = 'admin-reward-history';
    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('Reward History'))
            //    ->row(function (Row $row) {
            //         $row->column(12, $this->grid2());
            //     })
            ->row(function ($row) {
                $row->column(12, $this->grid());
            }));
    }

    protected function grid2()
    {
        return (new Box(
            title: __('admin.description'),
            content: view('admin.grid.superadmin.description'),
        ));
    }
    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(trans('Super Admin Reward'))
            ->body($this->detail($id)));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(trans('Super Admin Reward'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('Super Admin Reward'))
            ->body($this->form()));
    }

    protected function grid()
    {
        $grid = new Grid(new SuperAdminReward());
        $type = request('type') ?? 'vip';


        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column(1 / 2, function ($filter) {
                $filter->where(function ($query) {
                    $query->whereHas('superAdmin', function ($subQuery) {
                        $subQuery->where('username', 'like', "%{$this->input}%");
                    });
                }, __('username'))->placeholder(__('search for host by username'));
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('type', __('status'))->select(['ware' => __('ware'), "vip" => __('vip'), 'badge' => __("badge")]);
            });


            $filter->column(1 / 2, function ($filter) {
                $filter->where(function ($query) {
                    $date = \App\Helpers\UserCommon::arabicToEnglishNumbers($this->input);
                    $query->whereDate('created_at', $date);
                }, __('Created At'))->date();
            });
        });
        $grid->model()->where('type', $type)->with([
            'superAdmin',
            'admin',
            'user',
            'user.senderLevel',
            'user.receiverLevel',
            'user.profile',
            'user.country',
            'areaManager',
            'ware',
            'vip',
            'badge'
        ]);
        $grid->column('id', __('Id'));
        $grid->column('superadmin', __('super admin'))->display(function ($name) {
            if ($this->user_type == 'user') {

                $user = $this->user;
                if (!$user) {
                    return __('No User');
                }
                return app(UserService::class)->adminUserAvatar($user);
            }
            $admin = $this->user_type == 'super_admin' ? $this->superAdmin : $this->areaManager;
            $name = @$admin->name ?? '';
            $uid = @$admin->username ?? '';
            $path = @$admin->avatar;
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl = $this ? url("admin/superadmin-users/{$this->id}") : 0;
            return "<div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                       <a href='{}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                         <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                        </a>
                        <span style='color: #aaa; font-size: smaller;'>UUID: $uid</span>
                    </div>
                </div>";
        });
        $grid->column('type', __('Type'));
        $grid->column('gift_id', __('gifts'))->display(function () {
            if ($this->type == "ware") {
                return @$this->ware->name ?? '';
            } elseif ($this->type == "vip") {
                return @$this->vip->name ?? '';
            } elseif ($this->type == "badge") {
                return @$this->badge->name ?? '';
            } elseif ($this->type == "coins") {
                return @$this->target;
            } elseif ($this->type == "achievement") {
                $value = getDriverUrl() . '/' . @$this->target;
                return "<img src='$value' width='80' height='80'>";
            }
        });
        if (!request()->filled('_export_')) {
            $grid->column('image', __('image'))->display(function ($path) {
                if ($this->type == 'ware') {
                    $ware = Ware::find($this->target);
                    $path = $ware->img2 ?? ($ware->show_img ?? "");
                } elseif ($this->type == 'vip') {
                    $vips = OVip::find($this->target);
                    $path = $vips->img ?? '';
                } elseif ($this->type == 'badge') {
                    // $vips = Badge::find($this->target);
                    $path = @$this->badge->image ?? '';
                } elseif ($this->type == 'achievement') {
                    $path = $this->target;
                } else {
                    $path = 'coin.png';
                }

                /** @var Gift $this */
                $url = getImagePath($path);
                return handleShowImageWithTypes($this->id, $url, 50, 50);
            });
        }
        $grid->column('expire', __('Expire'));
        $grid->column('no_reward', __('No reward'));
        $grid->column('created_at', __('created_at'));
        $grid->column('created_by', __('created_by'))->display(function ($name) {

            $admin =  $this->admin;
            $name = @$admin->name ?? '';
            $uid = @$admin->username ?? '';
            $path = @$admin->avatar;
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl = $this ? url("admin/superadmin-users/{$this->id}") : 0;
            return "<div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                       <a href='{}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                         <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                        </a>
                        <span style='color: #aaa; font-size: smaller;'>UUID: $uid</span>
                    </div>
                </div>";
        });
        $grid->tools(function (Grid\Tools $tools) {
            $url = url('admin/admin-rewards?type=vip');
            $back = __(' back');

            $customButtonHTML = <<<HTML
                     <div style="display: contents; align-items: center;">
                        <a href="{$url}" class="btn btn-sm btn-info" style="margin-right: 10px;">
                            <i class="fa fa-arrow-left"></i> {$back}
                        </a>
                    </div>
                HTML;
            $tools->append($customButtonHTML);
        });
        $grid->disableRowSelector();
        $grid->disableExport();
        $grid->disableActions();
        $grid->disableCreateButton();
        return $grid;
    }


    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SuperAdminReward());

        $this->addSuperAdminField($form);
        $form->select('type', trans('type'))->options(["ware" => __('ware'), "badge" => __('badge'), "vip" => __('vip'), "coins" => __('coins'), "achievement" => __('achievement')])
            ->when("ware", function () use ($form) {
                $this->addWareField($form);
                $form->number('expire', __('expire'))->default(1);
            })
            ->when("badge", function () use ($form) {
                $this->addBadgeField($form);
                $form->number('expire', __('expire'))->default(1);
            })
            ->when("vip", function () use ($form) {
                $form->select('target2', trans('vips'))->options(function () {
                    $vips = OVip::query()->select('id', 'name')->get();
                    foreach ($vips as  $vip) {
                        $ops[$vip->id] = $vip->name;
                    }
                    return $ops;
                });
                $form->number('expire', __('expire'))->default(1);
            })
            ->when("coins", function () use ($form) {
                $form->number("target3", __("coins"));
            })->when("achievement", function () use ($form) {
                $form->image("target4", __('image'))->name(function ($file) {
                    return now()->timestamp . '.' . $file->guessExtension();
                })->disk('gcs');
                $form->number('expire', __('expire'))->default(1);
            })->rules('required');

        $form->number('no_reward', __('No reward'))->default(1);


        return $form;
    }

    protected function addWareField(Form $form)
    {
        $prefix = 'wares';
        $form->belongsTo('target1', WaresByType::class, __('Ware'), function ($form) use ($prefix) {
            $form->setElementName($prefix . 'target1')
                ->select('id', __('wares'))
                ->options(function ($id) {
                    if (!$id) return [];
                    $ware = Ware::find($id);
                    return $ware ? [$ware->id => "{$ware->name}_{$ware->id}"] : [];
                })
                ->attribute([
                    'data-image-select' => 1,
                    'data-load-url' => admin_url('wares-by-id')
                ]);

            $form->html('<div id="ware-image-preview" style="margin-top:10px;"></div>');

            $this->addWareJs();
        });
    }

    protected function addSuperAdminField(Form $form)
    {

        $form->belongsTo('super_admin_id', SuperAdmins::class, __('Super admin'), function ($form) {
            $form->select('id', __('super Admin'))
                ->options(function ($id) {
                    if (!$id) return [];
                    $ware = SuperAdmin::find($id);
                    return $ware ? [$ware->id => "{$ware->name}_{$ware->id}"] : [];
                })
                ->attribute([
                    'data-image-select' => 1,
                    'data-load-url' => admin_url('wares-by-id')
                ]);

            $form->html('<div id="ware-image-preview" style="margin-top:10px;"></div>');

            $this->addWareJs();
        });
    }

    protected function addBadgeField(Form $form)
    {
        $prefix = 'badges';
        $form->belongsTo('target5', Badges::class, __('Badges'), function ($form) use ($prefix) {
            $form->setElementName($prefix . 'target5')
                ->select('id', __('badges'))
                ->options(function ($id) {
                    if (!$id) return [];
                    $ware = Badge::find($id);
                    return $ware ? [$ware->id => "{$ware->name}_{$ware->id}"] : [];
                })
                ->attribute([
                    'data-image-select' => 1,
                    'data-load-url' => admin_url('wares-by-id')
                ]);

            $form->html('<div id="ware-image-preview" style="margin-top:10px;"></div>');

            $this->addWareJs();
        });
    }
}
