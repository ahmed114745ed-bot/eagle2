<?php

namespace Utd\Agency\Http\Controllers\Admin\AgencyControllers;

use App\Admin\Controllers\MainController;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Utd\Agency\Traits\ResolvesExternalDependencies;

class AgencyTargetController extends MainController
{
    use HasResourceActions;
    use ResolvesExternalDependencies;

    public $permission_name = 'user-agent-target';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('user target'))
            ->body($this->grid()));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UserTarget);
        $grid->model()->ofAgency();
        $grid->model()->where('agency_obtain', '>', 0)->selectRaw('add_month,add_year,SUM(agency_obtain) as tar')->groupBy('add_month', 'add_year');
        $grid->column('add_month', __('month'));
        $grid->column('add_year', __('year'));
        $grid->column('tar', __('tar'));

        $grid->disableActions();
        $grid->disableCreateButton();

        return $grid;
    }
}
