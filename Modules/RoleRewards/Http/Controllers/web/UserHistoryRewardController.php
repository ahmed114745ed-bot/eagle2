<?php

namespace Modules\RoleRewards\Http\Controllers\web;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Modules\RoleRewards\Entities\UserHistoryReward;
use Encore\Admin\Facades\Admin;
use App\Admin\Services\UserService;
use Encore\Admin\Layout\Content;
use Modules\RoleRewards\Entities\VUserHistoryReward;


class UserHistoryRewardController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */

    public function index(Content $content )
    {
      
        return parent::index($content
            ->header(__('User history rewards'))
            ->description(__('User history rewards'))
            // ->body($this->grid())
        );
    }
  
    protected function grid()
    {
        $grid = new Grid(new VUserHistoryReward());

        $grid->model()->with([
            'user' => function ($query) {
                $query->select(['id', 'name', 'uuid'])
                    ->with([
                        'profile:id,user_id,avatar',
                        'packs',
                    ]);
            },
        ])
        ->orderByDesc('id');            

        $grid->column('id', __('ID'))->sortable();

        $grid->column('user_id', __('User'))->display(function () {
            if (! $this->user) {
                return __('No User');
            }
            return app(UserService::class)->adminUserAvatar($this->user, withoutLevels: true);
        });

        $grid->column('receive_name', __('receive_type'));

        $grid->column('reward_preview', __('Rewards'));

        $grid->column('created_at', __('Created At'))
            ->display(fn($date) => \Carbon\Carbon::parse($date)->format('Y-m-d H:i'));

        $grid->filter(function ($filter) {
            $filter->equal('receive_category', __('Receive Type'))->select([
                'Role' => __('Role'),
                'Milestone' => __('Milestone'),
            ]);
        });

        $grid->disableCreateButton();
        $grid->disableActions();

        Admin::script("
            if (window.innerWidth >= 1024) {
                $('.table-responsive').removeClass('table-responsive');
            }
        ");

        return $grid;
    }

    
     

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(UserHistoryReward::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('receive_type', __('Receive type'));
        $show->field('rewardable_id', __('Rewardable id'));
        $show->field('rewardable_type', __('Rewardable type'));
        $show->field('extra', __('Extra'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('sub_type', __('Sub type'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new UserHistoryReward());

        $form->number('user_id', __('User id'));
        $form->text('receive_type', __('Receive type'));
        $form->number('rewardable_id', __('Rewardable id'));
        $form->text('rewardable_type', __('Rewardable type'));
        $form->text('extra', __('Extra'));
        $form->text('sub_type', __('Sub type'));

        return $form;
    }
}
