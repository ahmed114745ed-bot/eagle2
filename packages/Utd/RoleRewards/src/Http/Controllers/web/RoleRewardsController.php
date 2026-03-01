<?php

namespace Utd\RoleRewards\Http\Controllers\web;

use App\Models\Role;
use Utd\RoleRewards\Actions\DeleteRoleReward;
use Modules\Badge\Entities\Badge;
use Utd\RoleRewards\Entities\RoleReward;
use Utd\RoleRewards\Services\RoleRewardService;
use App\Selectables\Wares;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Admin;
use App\Selectables\Badges;
use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;
use App\Services\AppFeatureService;
use Encore\Admin\Controllers\HasResourceActions;
use Utd\Achievements\Entities\Achievement;
use Utd\Vip\Entities\OVip;
use App\Support\PackageHelper;
use Utd\Vip\Selectables\OVips;

class RoleRewardsController extends MainController
{
    use HasResourceActions;

    public $permission_name = 'roles-reward';

    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable("target_events");
    }

    public function index(Content $content, $roleId = null)
    {
        $role = Role::find($roleId);

        return parent::index($content
            ->header(__('Role') . '-:-' . $role->name)
            ->description(trans('id') . $role->id)
            ->body($this->grid($roleId)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->header(trans('admin.create'))
            ->description(trans('admin.description'))
            ->body($this->form()));
    }

    public function update($id)
    {
        $id = request()->route('id');
        return $this->form()->update($id);
    }

    public function edit($id, Content $content)
    {
        $id = request()->route('id');
        return parent::edit($id, $content
            ->header(trans('admin.edit'))
            ->description(trans('admin.description'))
            ->body($this->form()->edit($id)));
    }

    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->header(trans('admin.detail'))
            ->description(trans('admin.description'))
            ->body($this->detail($id)));
    }


    protected function grid($role_id)
    {

        $grid = new Grid(new RoleReward());
        $grid->model()->where('role_id', $role_id);

        $grid->column('id', __('Id'));
        if (!request()->filled('_export_')) {
            $grid->column('type', __('Type'))->label();
        } else {
            $grid->column('type', __('Type'));
        }
        $grid->column('reward_id', __('Rewards'))->display(function () {
            if ($this->type === "ware") {
                return $this->rewardable?->name ?? "-";
            } elseif ($this->type === "vip") {
                return $this->rewardable?->name ?? "-";
            } elseif ($this->type === "achievement") {
                return $this->rewardable?->title ?? "-";
            } elseif ($this->type === "badge") {
                return $this->rewardable?->name ?? "-";
            }
            return "-";
        });
        if (!request()->filled('_export_')) {
            $grid->column('image', __('Image'))->display(function () {
                if ($this->type === "ware") {
                    $path = $this->rewardable?->img2 ?? $this->rewardable?->show_img;
                } elseif ($this->type === "vip") {
                    $path = $this->rewardable?->img;
                } elseif ($this->type === "achievement") {
                    $path = $this?->reward_achievement;
                } elseif ($this->type === "badge") {
                    $path = $this->rewardable?->icon;
                } else {
                    $path = 'coin.png';
                }

                $url = getImagePath($path);
                return handleShowImageWithTypes($this->id, $url, 50, 50);
            });
        }
        $grid->column('expire', __('expire'))->display(function ($expire) {
            return $expire ?: '-';
        });

        $grid->tools(function (Grid\Tools $tools) {
            $url = url('admin/auth/roles');
            $back = __('back');
            $customButtonHTML = <<<HTML
                <a href="{$url}" class="btn btn-sm btn-info" style="margin-right: 10px;">
                    <i class="fa fa-arrow-left"></i> {$back}
                </a>
            HTML;
            $tools->append($customButtonHTML);
        });

        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
            $actions->add(new DeleteRoleReward());
        });

        Admin::script("
            if (window.innerWidth >= 1024) {
                $('.table-responsive').removeClass('table-responsive');
            }
        ");

        return $grid;
    }


    protected function form()
    {
        $form = new Form(new RoleReward());
        $this->disableFormTools($form);
        $this->addHiddenFields($form);


        if (request()->route()->getName() === 'admin.role-rewards.create') {
            $this->addTypeSelector($form);
        }

        $this->addExpireField($form);

        $this->handleSaving($form);
        $this->handleSaved($form);
        $this->handleDeleted($form);

        return $form;
    }


    protected function addHiddenFields(Form $form)
    {
        $form->hidden('role_id')->value(request('role_id'));
    }


    protected function addTypeSelector(Form $form)
    {
        $options = [
            "coins" => __('Coins'),
            "ware" => __('Wares'),
            "badge" => __('Badge'),
        ];
        if (PackageHelper::isInstalled('vip')) {
            $options["vip"] = __('vip');
        }

        if (class_exists(Achievement::class)) {
            $options['achievement'] = __('Achievement');
        }

        $form->select('type', __('Type'))->options($options)
            ->when("ware", function (Form $form) {
                $form->belongsTo('rewardable_id1', Wares::class, trans('wares'));
            })->when("vip", function () use ($form) {
                if (PackageHelper::isInstalled('vip')) {
                    $form->belongsTo('rewardable_id2', OVips::class, trans('vips'));
                }
            })->when("achievement", function (Form $form) {
                if (! class_exists(Achievement::class)) {
                    return;
                }
                $form->image("reward_achievement", __('image'))->name(function ($file) {
                    return now()->timestamp . '.' . $file->guessExtension();
                })->disk('gcs');
            })
            ->when("badge", function () use ($form) {
                $this->addBadgeField($form);
            });
    }

    protected function addBadgeField(Form $form)
    {
        $prefix = 'badges';
        $form->belongsTo('rewardable_id3', Badges::class, __('Badges'), function ($form) use ($prefix) {
            $form->setElementName($prefix . 'rewardable_id3')
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


    protected function addExpireField(Form $form)
    {
        $form->number('expire', __('expire'))->default(1);
    }


    protected function handleSaving(Form $form)
    {
        $form->saving(function (Form $form) {
            switch (request('type')) {
                case 'ware':
                    $form->rewardable_type = \App\Models\Ware::class;
                    $form->model()->rewardable_type = \App\Models\Ware::class;
                    break;
                case 'vip':
                    $form->rewardable_type = OVip::class;
                    $form->model()->rewardable_type = OVip::class;
                    break;
                case 'badge':
                    $form->rewardable_type = \Modules\Badge\Entities\Badge::class;
                    $form->model()->rewardable_type = \Modules\Badge\Entities\Badge::class;
                    break;
                case 'achievement':
                    if (! class_exists(Achievement::class)) {
                        return false;
                    }
                    $form->rewardable_id = 0;
                    $form->rewardable_type = Achievement::class;
                    $form->model()->rewardable_type = Achievement::class;
                    break;
            }
        });
    }


    protected function handleSaved(Form $form)
    {
        $form->saved(function (Form $form) {
            $this->syncRewards($form->model());
        });
    }


    protected function handleDeleted(Form $form)
    {
    }


    protected function syncRewards(RoleReward $roleReward)
    {
        $role = \Encore\Admin\Auth\Database\Role::find($roleReward->role_id);
        if (!$role) return;

        $slug = $role->slug;

        app(RoleRewardService::class)->syncRewardsForRole(
            $roleReward->role_id,
            $slug
        );
    }
}
