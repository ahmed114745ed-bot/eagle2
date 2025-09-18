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
         $grid->column('user.name', __('User'))->display(function ($name) {
            $uid = $this->user?->uuid ?? '-';
            $path = $this->user?->profile?->avatar ?? null;
        
            $defaultImage = asset('images/businessman-icon.jpg');
        
            $url = $path ? getImagePath($path) : $defaultImage;
        
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
        
            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl = $this->user ? url("admin/users/{$this->user->id}") : '#';
            $safeName = $name ?: __('N/A');
        
            return <<<HTML
                <div style="display: flex; align-items: center; gap: 10px;">
                    {$image}
                    <div>
                       <a href="{$showUrl}" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;">
                         <span style="text-decoration: underline; cursor: pointer;">{$safeName}</span>
                        </a>
                        <br>
                        <span style="font-size: smaller; color: #666;">UUID: {$uid}</span>
                    </div>
                </div>
            HTML;
        });
        
         $grid->column('receive_type', __('receive_type'));
     
         $grid->column('reward', __('Rewards'))->display(function () {
            $reward = $this->rewardable; 
            if (!$reward) return 'N/A';
        
            $name = $reward->name ?? 'Unnamed';
            
            $path = 'coin.png'; 
            if ($this->rewardable_type === \App\Models\User::class) {
                $extra = $reward->extra ?? '';
        
                if (is_array($extra)) {
                    $data = $extra;
                } else {
                    $data = json_decode($extra, true);
                }
        
                if (!$data) {
                    return $extra ?: 'N/A';
                }
        
                return '<pre style="white-space: pre-wrap;">' .
                    json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) .
                    '</pre>';
            }
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
                    $path = $reward?->img ??' ';
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
                if (!$data) return $extra; 
            }
        
            return '<pre style="white-space: pre-wrap;">' . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
        });
        
     
         $grid->column('created_at', __('Created At'))->display(fn($date) => \Carbon\Carbon::parse($date)->format('Y-m-d H:i'));
     
         $grid->column('sub_type', __('Sub Type'));
     
         $grid->filter(function ($filter) {
             $filter->equal('sub_type', 'Sub Type')->select([
                 'role' => 'roles',
                 'reward' => 'milestons',
             ]);
         });
     
         $grid->disableCreateButton();

         $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableDelete();
            $actions->disableEdit();
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
