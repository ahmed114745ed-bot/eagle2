<?php

namespace Modules\RoleRewards\Http\Controllers\web;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Modules\RoleRewards\Entities\UserHistoryReward;

class UserHistoryRewardController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'UserHistoryReward';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */

     protected function grid()
     {
         $grid = new Grid(new UserHistoryReward());
     
         $grid->column('id', __('ID'))->sortable();
     
         $grid->column('user.name', __('User'))->display(fn($name) => $name ?: 'N/A');
     
         $grid->column('receive_type', __('Receive Type'));
     
         $grid->column('reward', __('Reward'))->display(function () {
            $reward = $this->rewardable; 
            if (!$reward) return 'N/A';
        
            $name = $reward->name ?? 'Unnamed';
            
            $path = 'coin.png'; 
            switch ($this->rewardable_type) {
                case \App\Models\Ware::class:
                    $path = $reward->img2 ?? $reward->show_img ?? '';
                    break;
                case \Modules\Vip\Entities\OVip::class:
                    $path = $reward->img ?? '';
                    break;
                case \Modules\Achievement\Entities\Achievement::class:
                    $path = $this->reward_achievement ?? '';
                    break;
                case \Modules\Badge\Entities\Badge::class:
                    $path = '';
                    break;
            }

            $url = getImagePath($path);
            $imgTag = handleShowImageWithTypes($this->id, $url, 50, 50);
            return $imgTag . $name;
        });
        
     
         $grid->column('extra', __('Extra'))->display(function ($extra) {
            if (!$extra) return '';
        
            if (is_array($extra)) {
                $data = $extra;
            } else {
                $data = json_decode($extra, true);
                if (!$data) return $extra; // لو مش JSON صحيح
            }
        
            return '<pre style="white-space: pre-wrap;">' . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
        });
        
     
         // التاريخ بشكل مفهوم
         $grid->column('created_at', __('Created At'))->display(fn($date) => \Carbon\Carbon::parse($date)->format('Y-m-d H:i'));
     
         // sub_type
         $grid->column('sub_type', __('Sub Type'));
     
         $grid->filter(function ($filter) {
            //  $filter->like('user.name', 'User Name');
             $filter->equal('sub_type', 'Sub Type')->select([
                 'role' => 'Role',
                 'reward' => 'Reward',
             ]);
            //  $filter->like('rewardable_type', 'Reward Type');
         });
     
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
