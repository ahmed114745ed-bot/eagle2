<?php

namespace App\Admin\Controllers;

use App\Models\User;
use App\Models\Agency;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;


class ChangeAgencyMangerController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Agency';

    public function index ( Content $content )
    {
       
            return $content
            ->title(trans('change agency Manger'))
            ->description(__(request ('desc')?:'الرئيسيه'))
            ->row(function($row) {
                // $row->column(2, view('admin.grid.common.allStatistics'));
                $row->column(15, view('agency.changeManger'));
                $row->column(20,$this->grid());
            });
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Agency);
        $grid->filter (function (Grid\Filter $filter){
            $filter->expand ();
        });

        $grid->model ()->orderByDesc('id')->with('agencyManger');
        $grid->id(__('ID'));
       
        $grid->column('name',trans ('name'));
        $grid->column('agencyManger.name',__('Agency manger id'))->display(function ($name) {
            $uid = @$this->agencyManger->uuid;

            return "$name <br>
            <span style=\"color: #aaa; font-size: smaller;\">UID: $uid</span>";
        });
        $grid->actions(function ($actions) {
            $actions->disableView(); // Disable the "View" action
            // $actions->disableEdit(); // Disable the "Edit" action
            $actions->disableDelete();
        });
        $grid->disableExport();
        $grid->disableCreateButton();


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
        $show = new Show(Agency::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('owner_id', __('Owner id'));
        $show->field('name', __('Name'));
        $show->field('notice', __('Notice'));
        $show->field('status', __('Status'));
        $show->field('phone', __('Phone'));
        $show->field('url', __('Url'));
        $show->field('img', __('Img'));
        $show->field('contents', __('Contents'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('old_usd', __('Old usd'));
        $show->field('target_usd', __('Target usd'));
        $show->field('target_token_usd', __('Target token usd'));
        $show->field('app_owner_id', __('App owner id'));
        $show->field('salary', __('Salary'));
        // $show->field('Shipping_agency', __('Shipping agency'));
        $show->field('Host_agency', __('Host agency'));
        $show->field('agency_manger_id', __('Agency manger id'));
        $show->field('agency_dash_manger_id', __('Agency dash manger id'));
        $show->field('deleted_at', __('Deleted at'));
        $show->field('monthly_target', __('Monthly target'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Agency());
        $form->text('name', __('Name'));
        if ($form->isEditing()) {
            $form->select('agency_manger_id', __('Agency Manger app Id'))->options(
                User::where('is_manger', 1)->selectRaw("id, CONCAT(uuid, '_', name) as label")->pluck('label', 'id')
            );
        }

        return $form;
    }
}
