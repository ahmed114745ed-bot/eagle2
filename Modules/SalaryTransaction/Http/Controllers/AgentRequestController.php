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
use Modules\SalaryTransaction\Actions\AcceptAgentRequestAction;
use Modules\SalaryTransaction\Actions\AccepRequestAction;
use Modules\SalaryTransaction\Actions\CancelRequestAction;
use Modules\SalaryTransaction\Actions\RejectedAgentRequestAction;
use Modules\SalaryTransaction\Entities\AgentSalaryRequest;

class AgentRequestController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'agent-request-transaction';

    public function index(Content $content)
    {
        return $content
            ->title(trans('agent-salary-requests'))
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->title(trans('agent-salary-requests'))
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->title(trans('agent-salary-requests'))
            ->body($this->form()->edit($id));
    }

    public function create(Content $content)
    {
        return $content
            ->title(trans('agent-salary-requests'))
            ->body($this->form());
    }


    protected function grid()
    {
        $grid = new Grid(new AgentSalaryRequest());
        $grid->model()->where("status",0);
        $grid->column('id', __('Id'));
        $grid->column('agency.name',__("agency"));
        $grid->column('agent.name',__("name"));
        $grid->column('agent.uuid',__("Id"));
        $grid->column('type',__('Payment method'))->display(function($q){
            return $this->type == 1 ? __('coins') : 'usd' ;
        });
        $grid->column('usd',__("amount usd"));
        $grid->column('coins',__("amount coins"));
        // $grid->column('payment_gateway.title',__("payment title"));
        // $grid->column('country.name',__("country"));
        $grid->disableCreateButton();
        $grid->actions (function ($actions){
            $actions->disableEdit();
            $actions->disableDelete();
            $actions->add(new AcceptAgentRequestAction());
            $actions->add(new RejectedAgentRequestAction());
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append('<a href="' . url('/admin/agent-requests-history') . '"  class="btn btn-sm btn-success">' . __('admin.history') . '</a>');

        });

        return $grid;
    }

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
        $this->extendShow ($show);
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
        $form->select('pid', __('pid'))->options (function (){
            $ops = [0=>'root'];
            $ps = Emoji::query ()->where ('enable',1)->where ('pid',0)->where ('id','!=',$this->id)->get ();
            foreach ($ps as $p){
                $ops[$p->id] = $p->name;
            }
            return $ops;
        });
        $form->text('name', __('name'));
        $form->file('emoji', __('emoji'));
        $form->number('t_length', __('t_length'));
        $form->switch('enable', __('enable'))->states (Common::getSwitchStates ());
        $form->number('sort', __('sort'));

        return $form;
    }
}
