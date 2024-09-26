<?php

namespace Modules\Events\Http\Controllers\web;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Services\AppFeatureService;
use Modules\Events\Entities\GeneralRole;
use App\Admin\Controllers\MainController;
use Encore\Admin\Controllers\HasResourceActions;

class GeneralRoleController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'general-roles';
    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable("event_role");
    }
    protected function grid()
    {
        $grid = new Grid(new GeneralRole());
        $grid->model()->orderByDesc('id');
        $grid->id(__('admin.ID'));
        $grid->type(__('type'));
        $grid->url(__('url'));
        $grid->sub_type(__('subType'));
        $grid->desc_en(__('Description En'));
        $grid->desc_ar(__('Description An'));

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(GeneralRole::findOrFail($id));

        //        $show->id('ID');
        //        $show->charger_id('charger_id');
        //        $show->charger_type('charger_type');
        //        $show->user_id('user_id');
        //        $show->user_type('user_type');
        //        $show->amount('amount');
        //        $show->amount_type('amount_type');
        //        $show->created_at(trans('admin.created_at'));
        //        $show->updated_at(trans('admin.updated_at'));
        $this->extendShow($show);
        return $show;
    }


    protected function form()
    {
        $form = new Form(new GeneralRole);

        $form->display('ID');
        $form->select('type', __('type'))->options([
            'weekly_star' => 'weekly_star',
            'pk_event' => 'pk_event',
            'charge_event' => 'charge_event',
            'event_period' => 'period_event',
        ])->creationRules(['required', "unique:general_roles"], ['unique' => 'هذا النوع مستخدم من قبل روح عدل عليه '])
            ->updateRules(['required', "unique:general_roles,type,{{id}}"]);
        $form->url('url', trans('url'))->required();
        $form->text('sub_type', 'sub_type');
        $form->textarea('desc_en', 'Description En');
        $form->textarea('desc_ar', 'Description Ar');

        return $form;
    }
}
