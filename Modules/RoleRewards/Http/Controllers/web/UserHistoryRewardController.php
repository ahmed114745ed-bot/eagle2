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
         $grid = new Grid(new UserHistoryReward());
         $grid->model()->orderByDesc('id');
     
         $grid->column('id', __('ID'))->sortable();

         $grid->column('user_id', __('User'))->display(function ($name) {
            $user = $this->user;
            if (! $user) {
                return __('No User');
            }

            return app(UserService::class)->adminUserAvatar($user,withoutLevels: true);
        });
      
        
        // $grid->column('receive_name', __('Receive_type'));
     
         $grid->column('reward', __('Rewards'))->display(function () {
            
            $reward = $this->rewardable; 
            if (in_array($this->rewardable_type, [\App\Models\User::class, \Modules\Achievement\Entities\Achievement::class])) {
                $extra = $reward->extra ?? '';
                $data = is_array($extra) ? $extra : json_decode($extra, true);
                if (!$data) return $extra ?: 'N/A';
        
                if ($this->rewardable_type === \App\Models\User::class) {
                    $data = $data['coins'] ?? $data;
                }
                
                if ($this->rewardable_type === \Modules\Achievement\Entities\Achievement::class) {
                    $data = $data['reward_achievement'] ?? $data;
                }
        
                return '<pre style="white-space: pre-wrap;">' .
                    json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) .
                    '</pre>';
            }
        
            $name = $reward->name ?? 'Unnamed';
            $path = match ($this->rewardable_type) {
                \App\Models\Ware::class => $reward->img2 ?? $reward->show_img ?? '',
                \Modules\Vip\Entities\OVip::class => $reward->img ?? '',
                \Modules\Badge\Entities\Badge::class => $reward?->img ?? '',
                default => 'coin.png',
            };
        
            $url = getImagePath($path);
            $imgTag = handleShowImageWithTypes($this->id, $url, 50, 50);
            return $imgTag . $name;
        });
        
     
        
     
         $grid->column('created_at', __('Created At'))->display(fn($date) => \Carbon\Carbon::parse($date)->format('Y-m-d H:i'));
     
        //  $grid->column('sub_type', __('Sub Type'));
     
         $grid->filter(function ($filter) {
             $filter->equal('sub_type', __('Sub Type'))->select([
                 'role' => __('Roles'),
                 'reward' => __('Milestone'),
             ]);
         });
     
         $grid->disableCreateButton();

         $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
        });
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
