<?php

namespace Modules\SalaryTransaction\Http\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use Encore\Admin\Controllers\AdminController;
use Modules\SalaryTransaction\Entities\SalaryRequest;

class SalaryRequestController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'SalaryRequest';
    public $permission_name = 'salary-request';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SalaryRequest());
        $grid->model()->orderByDesc('id');
        $grid->filter (function (Grid\Filter $filter){
            $filter->column(1/2, function ($filter) {
                $filter->equal('status',__('status'))->select([0=>__('waiting'),1=>__('accepting'),2=>__('transferred'),3=>__('completed'),4=>__('rejected')]);

            });
            $filter->column(1/2, function ($filter) {
                $filter->equal('agency_id',__('agency'))->select(Common::by_agency_filter ());

                $filter->equal('agency_owner_id',__('Agency owner'))->select(Common::by_user_filter ());
            });
        });
        $grid->column('id', __('Id'));
        $grid->column('agency.name', __('agency'))->display(function ($name) {
            $imageUrl =getImagePath( $this->agency?->img ??''); // Assuming 'img' is the field in the 'agency' model where the image URL is stored
            return "{$name} <br> <img src='{$imageUrl}'  style='max-width: 50px; margin-top: 10px;'>";
        });
        $grid->column('agencyOwner.name', __('Agency owner'))->display(function ($name) {
            $imageUrl = getImagePath($this->agencyOwner?->profile?->avatar ??''); // Assuming 'img' is the field in the 'agency' model where the image URL is stored
            return "{$name} <br> <img src='{$imageUrl}'  style='max-width: 50px; margin-top: 10px;'>";
        });
        $grid->column('host.name', __('host'))->display(function ($name) {
            $imageUrl = getImagePath($this->host?->profile?->avatar ??''); // Assuming 'img' is the field in the 'agency' model where the image URL is stored
            return "{$name} <br> <img src='{$imageUrl}'  style='max-width: 50px; margin-top: 10px;'>";
        });
        $grid->column('status', __('status'))->display(function ($status) {
            switch ($status) {
                case 0:
                 return __('waiting');
                  break;
                case 1:
                    return __('accepting');
                  break;
                  case 2:
                    return __('transferred');
                  break;
                  case 3:
                    return __('completed');
                  break;
                  case 4:
                    return $this->request_admin_status==1 ?__('rejectedAdmin'):__('rejected');
                  break;
                }
        });
        $grid->column('payment_gateway.title', __('Payment gateway'));
        $grid->column('country.name', __('country'));
        $grid->column('usd', __('Usd'));
        $grid->column('coins', __('coins'));
        $grid->column('bill_image', __('bill image'))->image ('',50);

        $grid->disableActions ();
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
        $show = new Show(SalaryRequest::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('agency_id', __('Agency id'));
        $show->field('host_id', __('Host id'));
        $show->field('status', __('Status'));
        $show->field('payment_gateway_id', __('Payment gateway id'));
        $show->field('country_id', __('Country id'));
        $show->field('agency_owner_id', __('Agency owner id'));
        $show->field('usd', __('Usd'));
        $show->field('coins', __('Coins'));
        $show->field('bill_image', __('Bill image'));
        $show->field('host_check', __('Host check'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('note', __('Note'));
        $show->field('request_admin_status', __('Request admin status'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SalaryRequest());

        $form->number('agency_id', __('Agency id'));
        $form->number('host_id', __('Host id'));
        $form->number('status', __('Status'));
        $form->number('payment_gateway_id', __('Payment gateway id'));
        $form->number('country_id', __('Country id'));
        $form->number('agency_owner_id', __('Agency owner id'));
        $form->number('usd', __('Usd'));
        $form->number('coins', __('Coins'));
        $form->text('bill_image', __('Bill image'));
        $form->number('host_check', __('Host check'));
        $form->text('note', __('Note'));
        $form->switch('request_admin_status', __('Request admin status'));

        return $form;
    }
}
