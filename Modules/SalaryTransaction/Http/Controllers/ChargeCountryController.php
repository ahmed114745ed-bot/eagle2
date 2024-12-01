<?php

namespace Modules\SalaryTransaction\Http\Controllers;

use App\Admin\Controllers\MainController;
use App\Helpers\Common;
use App\Models\Emoji;
use App\Http\Controllers\Controller;
use App\Models\Country;
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
use Modules\SalaryTransaction\Entities\ChargeCountry;

class ChargeCountryController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'charge-country';

    public function index(Content $content)
    {
        return $content
            ->title(trans('charge-country'))
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
            ->title(trans('charge-country'))
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
            ->title(trans('charge-country'))
            ->body($this->form()->edit($id));
    }

    public function create(Content $content)
    {
        return $content
            ->title(trans('charge-country'))
            ->body($this->form());
    }

    protected function grid()
    {
        $grid = new Grid(new ChargeCountry());

        $grid->id(__('Id'));
        $grid->column('country.name',trans('name'));
        $grid->column('country.e_name',trans('english name'));
        $grid->column('country.phone_code',trans('phone code'));
        $grid->column('country.language',trans ('language'));
        $grid->column ('country.flag',trans ('flag'))->image ('',30);
        
        $this->extendGrid ($grid);

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(ChargeCountry::findOrFail($id));

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
        $form = new Form(new ChargeCountry);

        $form->display(__('admin.ID'));
        $form->select('country_id', __('country'))->options (function (){
            $ops = [0=>'root'];
            $ps = Country::query ()->WhereDoesntHave('chargeCountry')->get ();
            foreach ($ps as $p){
                $ops[$p->id] = $p->name;
            }
            return $ops;
        })->rules(function ($form) {
            if (!$id = $form->model()->id) {
                return 'unique:charge_countries,country_id';
            }
        
        });
        return $form;
    }
}
