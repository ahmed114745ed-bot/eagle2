<?php

namespace Modules\SalaryTransaction\Http\Controllers;

use App\Admin\Controllers\MainController;
use App\Models\Agency;
use App\Models\ChargeAgency;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Modules\SalaryTransaction\Entities\ChargeAgency as EntitiesChargeAgency;

class ChargeAgencyController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'charge-agency';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('Verified Charging Agents'))
            ->body($this->grid()));
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
        return parent::show($id,$content
            ->title(trans('agency-country'))
            ->body($this->detail($id)));
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
        return parent::edit($id,$content
            ->title(trans('agency-country'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('agency-country'))
            ->body($this->form()));
    }

    protected function grid()
    {
        $grid = new Grid(new EntitiesChargeAgency());

        $grid->id(__('ID'));
        $grid->column('agency.name',trans('name'));
        $grid->column('agency.phone',trans('phone'));
        $grid->column('agency.image',trans ('image'))->image ('',30);

        $this->extendGrid ($grid);

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(EntitiesChargeAgency::findOrFail($id));

        $show->id(__('admin.ID'));
        $show->name('name');
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
        $form = new Form(new EntitiesChargeAgency);

        $form->display(__('admin.ID'));
        $form->select('agency_id', __('agency'))->options (function (){
            $ops = [0=>'root'];
            $ps = Agency::query ()->WhereDoesntHave('chargeAgency')->where("Shipping_agency",1)->get ();
            foreach ($ps as $p){
                $ops[$p->id] = $p->name;
            }
            return $ops;
        })->rules(function ($form) {
            if (!$id = $form->model()->id) {
                return 'unique:charge_agencies,agency_id';
            }

        });
        return $form;
    }
}
