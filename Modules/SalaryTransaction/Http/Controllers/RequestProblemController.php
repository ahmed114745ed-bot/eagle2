<?php

namespace Modules\SalaryTransaction\Http\Controllers;

use App\Admin\Controllers\MainController;
use App\Helpers\Common;
use App\Models\Emoji;
use App\Http\Controllers\Controller;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Modules\SalaryTransaction\Entities\AdminCheck;
use Encore\Admin\Widgets\Table;
use Illuminate\Support\Collection;
use Modules\SalaryTransaction\Actions\AccepRequestAction;
use Modules\SalaryTransaction\Actions\CancelRequestAction;

class RequestProblemController extends MainController
{
    use HasResourceActions;

    public $permission_name = 'request-problem';
    protected $title;

    public function __construct()
    {
        $this->title = __('transaction-request-problem');
    }
    protected function grid()
    {
        $grid = new Grid(new AdminCheck());
        $grid->model()->where("admin_check", '!=', 1);
        $grid->column('id', __('Id'));
        $grid->column('request_id', __('request'))->modal('request info', function ($model) {
            $show = new Show($model);
            $show->id('ID');
            $show->field('request.agency_id', __('agency id'));
            $show->field('request.agency_owner_id', __('agency owner id'));
            $show->field('request.host_id', __('host id'));
            $show->field('request.status', __('status'));
            $show->field('request.usd', __('usd'));
            $show->field('request.coins', __('coins'));
            $show->field('request.host_check', __('host_check'))->display(function ($q) {
                if ($q->host_check == 1) {
                    return __('Accept');
                } elseif ($q->host_check == 2) {
                    return __('Reject');
                } else {
                    return __('Pending');
                }
            });
            $show->field('request.bill_image', __('bill image'))->image();
            return $show;
        });
        $grid->column('request.bill_image', __('bill image'))->image('', 50);
        $grid->column('request',__('Shipping agent ID'))->display(function () {
            return $this->request?->agency?->owner?->uuid;
        });
        $grid->column(__('Shipping Agent Name'))->display(function () {
            return $this->request?->agency?->owner?->name;
        });

        $grid->column(__('Host ID'))->display(function () {
            return $this->request?->host?->uuid;
        });
        $grid->column(__('Host Name'))->display(function () {
            return $this->request?->host?->name;
        });

        $grid->column('type', __('type'));
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableDelete();
            $actions->add(new AccepRequestAction());
            $actions->add(new CancelRequestAction());
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
        $show = new Show(Emoji::findOrFail($id));

        $show->id(__('admin.ID'));
        $show->pid('pid');
        $show->name('name');
        $show->emoji('emoji');
        $show->t_length('t_length');
        $show->enable('enable');
        $show->sort('sort');
        $this->extendShow($show);
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Emoji);

        $form->display(__('admin.ID'));
        $form->select('pid', __('pid'))->options(function () {
            $ops = [0 => 'root'];
            $ps = Emoji::query()->where('enable', 1)->where('pid', 0)->where('id', '!=', $this->id)->get();
            foreach ($ps as $p) {
                $ops[$p->id] = $p->name;
            }
            return $ops;
        });
        $form->text('name', __('name'));
        $form->file('emoji', __('emoji'));
        $form->number('t_length', __('t_length'));
        $form->switch('enable', __('enable'))->states(Common::getSwitchStates());
        $form->number('sort', __('sort'));

        return $form;
    }
}
