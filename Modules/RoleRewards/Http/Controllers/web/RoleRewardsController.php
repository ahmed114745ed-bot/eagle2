<?php

namespace Modules\RoleRewards\Http\Controllers\web;

use App\Models\Role;
use Modules\Achievement\Entities\Achievement;
use Modules\RoleRewards\Actions\DeleteRoleReward;
use Modules\Badge\Entities\Badge;
use Modules\RoleRewards\Entities\RoleReward;
use Modules\RoleRewards\Helpers\UserRoleRewardHelper;
use Modules\Vip\Entities\OVip;
use App\Models\Ware;
use App\Selectables\Wares;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Admin;

use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;
use App\Services\AppFeatureService;
use Modules\Events\Entities\ChargeTargetEvent;
use Modules\Events\Entities\RewardTarget;
use Encore\Admin\Controllers\HasResourceActions;

class RoleRewardsController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'roles';
    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable("target_events");
    }
    public function index(Content $content ,$roleId = null)
    {
        $role = Role::find($roleId);
      
        return parent::index($content
            ->header(__('Role') . '-:-'. $role->name)
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
        $grid->model()->where('role_id' ,$role_id);
        
        // الأعمدة الأساسية
        $grid->column('id', __('Id'));
        $grid->column('type', __('Type'))->label();

        $grid->column('reward_id', __('Rewards'))->display(function () {
            if ($this->type === "ware") {
                return $this->rewardable?->name ?? "-";
            } elseif ($this->type === "vip") {
                return $this->rewardable?->name ?? "-";
            } elseif ($this->type === "achievement") {
                return $this->rewardable?->title ?? "-";
            } elseif ($this->type === "badge") {
                return $this->rewardable?->title ?? "-";
            }
            return "-";
        });
        
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
        $grid->column('expire', __('expire'))->display(function ($expire) {
            return $expire ?: '-';
        });

        // $grid->column('created_at', __('Created at'));

        $grid->tools(function (Grid\Tools $tools) {
            $url = url('admin/auth/roles');
            $back = __('Back');
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
            // $actions->disableEdit();
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

        if ($form->isEditing()) {
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
    $form->select('type', __('Type'))->options([
        "ware"        => __('Ware'),
        "vip"         => __('Vip'),
        "achievement" => __('Achievement'),
        "badge"       => __('Badge'),
    ])->when("ware", function (Form $form) {
        $form->belongsTo('rewardable_id', Wares::class, trans('wares'))->rules('required');
    })->when("vip", function (Form $form) {
        $form->select('rewardable_id', __('Vip'))
            ->options(OVip::pluck('name', 'id'))
            ->rules('required');
    })->when("achievement", function (Form $form) {
        $form->image("reward_achievement", __('image'))->name(function ($file) {
            return now()->timestamp . '.' . $file->guessExtension();
        })->disk('gcs');
    })->when("badge", function (Form $form) {
        $form->select('rewardable_id', __('Badge'))
            ->options(Badge::pluck('name', 'id'))
            ->rules('required');
    });
}


protected function addExpireField(Form $form)
{
    $form->number('expire', __('expire'))->default(1);

}


protected function handleSaving(Form $form)
{
    $form->saving(function (Form $form) {
        switch ($form->type) {
            case 'ware':
                $form->rewardable_type = \App\Models\Ware::class;
                $form->model()->rewardable_type = \App\Models\Ware::class;
                break;
            case 'vip':
                $form->rewardable_type = \Modules\Vip\Entities\OVip::class;
                $form->model()->rewardable_type = \Modules\Vip\Entities\OVip::class;
                break;
            case 'badge':
                $form->rewardable_type = \Modules\Badge\Entities\Badge::class;
                $form->model()->rewardable_type = \Modules\Badge\Entities\Badge::class;
                break;
            case 'achievement':
                $form->rewardable_id = 0;
                $form->rewardable_type = \Modules\Achievement\Entities\Achievement::class;
                $form->model()->rewardable_type = \Modules\Achievement\Entities\Achievement::class;
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
    // $form->deleted(function (Form $form) {
    //     $this->syncRewards($form->model());
    // });
}


protected function syncRewards(RoleReward $roleReward)
{
    $role = \Encore\Admin\Auth\Database\Role::find($roleReward->role_id);
    if (! $role) return;

    $slug = $role->slug;



    UserRoleRewardHelper::syncRewardsForRole(
        $roleReward->role_id,
        $slug
    );
}



    

  

}


