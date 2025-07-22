<?php

namespace App\Admin\Controllers;

use App\Admin\Services\AgencyService;
use App\Admin\Services\UserService;
use App\Models\SalaryTrx;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

use function request;

class SallariesHistoryController extends MainController
{
    use HasResourceActions;

    public $permission_name = 'salary-history';

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index(Content $content)
    {
        return parent::index($content
            ->header(trans('admin.index'))
            ->description(trans('admin.description'))
            ->body($this->grid()));
    }

    protected function grid()
    {
        $grid = new Grid(new SalaryTrx);
        $grid->model()->where('type', request('type'))->orderByDesc('id');
        $grid->id(__('Id'));
        if (request('type') == 0) {

            $grid->column('name', __('Name'))
                ->display(function ($name) {

                    $user = $this->user;
                    if (! $user) {
                        return '';
                    }

                    return app(UserService::class)->adminUserAvatar($user);
                });
        } else {
            $grid->column('name', __('Agency'))->display(function ($name) {
                $agency = $this->agency;
                if (! $agency) {
                    return '';
                }

                return app(AgencyService::class)->adminAgencyData($agency);
            });
        }
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
        });

        $grid->amount()->display(function ($num) {
            if ($num > 0) {
                return "<span class='text-primary '>$num</span>";
            }
            $num *= -1;

            return "<span class='text-danger '>$num</span>";

        });
        $grid->disableExport();
        $grid->disableCreateButton();

        return $grid;
    }
}
