<?php

namespace App\Admin\Controllers\V2;

use App\Admin\Services\UserService;
use Encore\Admin\Facades\Admin;
use Exception;
use App\Models\User;
use function request;
use App\Models\Agency;
use Encore\Admin\Grid;
use App\Models\SalaryTrx;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;
use App\Admin\Actions\SalariesAction;
use App\Admin\Actions\PaySalariesAction;
use App\Admin\Controllers\MainController;
use App\Helpers\Common;

class SalariesController extends MainController
{
   public $permission_name = 'salary';
    public function index(Content $content)
    {
        return $content->title(trans('Sallaries'))->description(__(request('desc') ?: 'users'))->row(function ($row) {
                $row->column(2, view('admin.grid.common.sallaries'));
                $row->column(10, $this->grid());
            });
    }

    protected function grid()
    {
        $name = request('name') ?: 'users';

        $grid = $name;
        $grid = $this->{$grid}();
        $grid->disableexport();
        $grid->disableActions();
        $grid->disableCreateButton();
        return $grid;
    }


    protected function users_grid()
    {
        $grid = new Grid(new User());
        $grid->column('id', __('id'));
        $grid->column('agency', __('agency'))->display(function () { return @$this->agency->name; });
        // $grid->column ('uuid',__ ('uuid'));
        $grid->column('name', __('name'))->display(fn($f) => app(UserService::class)->adminUserAvatar($this));
        $grid->column('old_usd', __('old usd'));
        $grid->column('target_usd', __('target usd'));
        $grid->column('target_token_usd', __('target token usd'));
        $grid->column('due', __('due'))->display(function () {
            return $this->old_usd + $this->target_usd - $this->target_token_usd;
        });
        if (Admin::user()->isRole('developer') || Admin::user()->isRole('admin')) {
            $grid->column('cashing', __('cashing'))->display(function () {
                $options = ['user' => __('user')];
                return (new SalariesAction($this->id, 'user'))->render();
            });
        }

        $grid->export(function ($export) {
            $export->filename('report');
            $export->column('uuid', function ($value, $original) {
                return $value;
            });
        });

        $grid->disableExport();

        return $grid;
    }

    protected function agencies_grid()
    {
        $grid = new Grid(new Agency());
        $grid->model()->where('target_usd', '>', 0);
        $grid->column('id', __('id'));
        $grid->column('name', __('name'));
        $grid->column('phone', __('phone'));
        $grid->column('old_usd', __('old usd'));
        $grid->column('target_usd', __('target usd'));
        $grid->column('target_token_usd', __('target token usd'));
        $grid->column('due', __('due'))->display(function () {
            return $this->old_usd + $this->target_usd - $this->target_token_usd;
        });
        $grid->column('users', __('users'))->display(function () {
            return '<a href="?name=users&desc=' . $this->name . '&aid=' . $this->id . '">' . $this->users()->count() . '</a>';
        });
        $grid->column('cashing', __('cashing'))->display(function () {
            return (new SalariesAction($this->id, 'agency'))->render();
        });

        return $grid;
    }

    protected function users()
    {
        $grid  = new Grid(new User());
        $model =
            $grid->model()->where('agency_id', '!=', 0)->LeftJoin('user_sallaries', 'users.id', '=', 'user_sallaries.user_id');
        if (request('salary_only') == 1) {
            $model->having('total', '>', 0);
        }
        $model->select('users.id', 'users.name', 'users.uuid', DB::raw('SUM(user_sallaries.sallary - user_sallaries.cut_amount) AS total'))->groupBy('users.id', 'users.name', 'users.uuid')->orderByRaw('total DESC');
        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->where(function ($query) {
                    $input = $this->input;
                    $query->where(function ($q) use ($input) {
                        $q->where('name', 'like', "%$input%")
                            ->orWhere('uuid', 'like', "%$input%")
                            ->orWhere('special_id', 'like', "%$input%")
                            ->orWhere('nickname', 'like', "%$input%")
                            ->orWhere('email', 'like', "%$input%");
                    });
                }, __('User'))->placeholder(__('Search by UUID'));
            });

            $filter->column(1 / 2, function ($filter) {
                $filter->equal('agency_id', __('agency'))->select(Common::by_agency_filter());
            });
        });
        $grid->column('id', __('id'));
        $grid->column('uuid', __('uuid'));
        $grid->column('name', __('name'))->display(fn($f) => app(UserService::class)->adminUserAvatar($this, withoutLevels: true));
        $grid->column('total', __('salary'))->default(0);
//        $grid->column('cashing', __('cashing'))->display(function () {
//            return (new SalariesAction($this->id, 'user'))->render();
//        });
        if (Admin::user()->isRole('developer') || Admin::user()->isRole('admin')) {
            $grid->column('pay', __('pay'))->display(function () {
                return (new PaySalariesAction($this->id, 'user', $this->salary))->render();
            });
        }
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append('<a href="' . url('/admin/sallaries_history?type=0') . '"  class="btn btn-sm btn-success">' . __('admin.history') . '</a>');

        });
        return $grid;
    }

    protected function agencies()
    {
        $grid = new Grid(new Agency());

        $model = $grid->model()
                 ->LeftJoin('agency_sallaries', 'agencies.id', '=', 'agency_sallaries.agency_id')
                 ->select('agencies.id', 'agencies.name', DB::raw('SUM(agency_sallaries.sallary - agency_sallaries.cut_amount) AS total'))
//            ->where('agencies.id', request('id'))
                 ->orderByRaw('total desc')
                 ->groupBy('agencies.id', 'agencies.name');

        if (request('salary_only') == 1) {
            $model->having('total', '>', 0);
        }
        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->expand();

            $filter->column(12, function(Grid\Filter $filter) {
                $filter->where( function ($q) {
                    $q->where('agencies.id', '=', $this->input);
                } , 'id');

            });
        });

        $grid->column('id', __('id'));
        $grid->column('name', __('name'));
        $grid->column('total', __('salary'))->default(0);

//        $grid->column('cashing', __('cashing'))->display(function () {
//            $options = ['agency' => __('agency')];
//            return (new SalariesAction($this->id, 'agency'))->render();
//        });
        $grid->column('pay', __('pay'))->display(function () {
            return (new PaySalariesAction($this->id, 'agency',$this->salary))->render();
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append('<a href="' . url('/admin/sallaries_history?type=1') . '"  class="btn btn-sm btn-success">' . __('admin.history') . '</a>');
        });
        return $grid;
    }


}
