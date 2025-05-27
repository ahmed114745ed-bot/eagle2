<?php

namespace App\Bd\Controllers;

use App\Models\Charge;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;
use Encore\Admin\Layout\Content;

class ChargeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Charge';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */


     public function index(Content $content)
     {
        return $content
        ->header(trans('Charges'))
        ->row(function ($row) {
            $row->column(12, $this->grid());
        });
     }
    protected function grid()
    {
        $grid = new Grid(new Charge());

        $grid->model()->where('user_charger_type','bd')
                      ->where('charger_id', Auth::user()->app_id);
    
        // $grid->column('id', __('Id'));
        $grid->column('amount', __('Amount'));
        // $grid->column('amount_type', __('Amount type'));
    
        $grid->column('agency_id', __('receiver'))->display(function () {
            if ($this->agency) {
                $agency = $this->agency;
    
                $cacheKey = "agency_image_{$agency->id}";
                $image = \Cache::remember($cacheKey, 3600, function () use ($agency) {
                    $path = @$agency->img;
                    $defaultImage = asset("images/icon-agency.jpg");
                    $url = getImagePath($path) ?? $defaultImage;
                    if (!isImageExists($url)) $url = $defaultImage;
    
                    return handleShowImageWithTypes($agency->id, $url, 40, 40);
                });
    
                $profileUrl = route('admin.agency.profile', ['id' => $agency->id]);
    
                return "
                    <a href='{$profileUrl}' style='text-decoration: none; color: inherit;'>
                        <div style='display: flex; align-items: center; gap: 10px;'>
                            {$image}
                            <div style='display: flex; flex-direction: column;'>
                                <span style='text-decoration: underline; cursor: pointer;'>{$agency->name}</span>
                                <span style='font-size: smaller;'>ID: {$agency->id}</span>
                            </div>
                        </div>
                    </a>
                ";
            }
    
            $user = \App\Models\User::find($this->user_id);
            if ($user) {
                $path = $user->profile?->avatar ?? null;
                $defaultImage = asset("images/businessman-icon.jpg");
                $url = getImagePath($path) ?? $defaultImage;
                if (!isImageExists($url)) $url = $defaultImage;
    
                $image = handleShowImageWithTypes($user->id ?? 0, $url, 40, 40);
                $showUrl = url("admin/users/{$user->id}");
    
                return "
                    <a href='{$showUrl}' style='text-decoration: none; color: inherit;'>
                        <div style='display: flex; align-items: center; gap: 10px;'>
                            {$image}
                            <div>
                                <span style='text-decoration: underline; cursor: pointer;'>{$user->name}</span><br>
                                <span style='color: #aaa; font-size: smaller;'>UUID: {$user->uuid}</span>
                            </div>
                        </div>
                    </a>
                ";
            }
    
            return "<span class='text-danger'>".__('لا يوجد مستلم')."</span>";
        });
    
        $grid->column('created_at', __('تاريخ الإنشاء'))->display(function ($value) {
            return \Carbon\Carbon::parse($value)->translatedFormat('Y-m-d h:i A');
        });
    
        $grid->column('usd', __('Usd'));
    
        $grid->disableCreateButton();
       
        $grid->tools(function (Grid\Tools $tools) {
            $url = 'salaries';
            $button = '<a href="' . $url . '" class="btn btn-sm btn-success"><i class="fa fa-go"></i>&nbsp;&nbsp;' . __("back") . '</a>';
            $tools->append($button);
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
        $show = new Show(Charge::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('charger_id', __('Charger id'));
        $show->field('charger_type', __('Charger type'));
        $show->field('user_charger_type', __('User charger type'));
        $show->field('user_id', __('User id'));
        $show->field('user_type', __('User type'));
        $show->field('amount', __('Amount'));
        $show->field('amount_type', __('Amount type'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('balance_before', __('Balance before'));
        $show->field('is_used_transferred', __('Is used transferred'));
        $show->field('usd', __('Usd'));
        $show->field('agency_id', __('Agency id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Charge());

        $form->number('charger_id', __('Charger id'));
        $form->text('charger_type', __('Charger type'));
        $form->text('user_charger_type', __('User charger type'));
        $form->number('user_id', __('User id'));
        $form->text('user_type', __('User type'));
        $form->decimal('amount', __('Amount'))->default(0.00);
        $form->switch('amount_type', __('Amount type'))->default(1);
        $form->decimal('balance_before', __('Balance before'));
        $form->switch('is_used_transferred', __('Is used transferred'));
        $form->decimal('usd', __('Usd'));
        $form->number('agency_id', __('Agency id'));

        return $form;
    }
}
