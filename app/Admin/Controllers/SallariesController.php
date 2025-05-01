<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\PaySalariesAction;
use App\Admin\Actions\SalariesAction;
use App\Models\Agency;
use App\Models\SalaryTrx;
use App\Models\User;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Exception;
use Illuminate\Support\Facades\DB;
use function request;

class SallariesController extends MainController
{
    public $permission_name = 'sailer';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('Sallaries'))
            ->description(__(request('desc') ?: 'users'))
            ->row(function ($row) {
                $row->column(3, $this->salaryNavbar());
                $row->column(9, $this->grid());
            }));
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

    protected function users()
    {
        $grid = new Grid(new User());
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
                $filter->equal('uuid', __('uuid'));
            });
        });

        $grid->column('name', __('name'))->display(function ($name) {
            $uid = @$this->uuid;
            $path = @$this->profile?->avatar;
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl =  url("admin/users/{$this->id}");
            return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                       <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                         <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                        </a>
                        <span style='color: #aaa; font-size: smaller;'>UUID: $uid</span>
                    </div>
                </div>
            ";
        });;
        $grid->column('total', __('salary'))->display(function ($usd) {
            $image = asset('images/dollar.jpg'); // Adjust path as needed
            return "<div style='display: flex; align-items: center; '>

                        <span>{$usd}</span>
                          <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        })->default(0);
//        $grid->column('cashing', __('cashing'))->display(function () {
//            return (new SalariesAction($this->id, 'user'))->render();
//        });
//        $grid->column('pay', __('pay'))->display(function () {
//            return (new PaySalariesAction($this->id, 'user', $this->salary))->render();
//        });
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

            $filter->column(12, function (Grid\Filter $filter) {
                $filter->where(function ($q) {
                    $q->where('agencies.id', '=', $this->input);
                }, 'id');
            });
        });

        $grid->column('id', __('id'));
        $grid->column('name', __('name'))->display(function ($name) {
            $path = @$this->img;
            $defaultImage = asset("images/icon-agency.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
            $image = handleShowImageWithTypes($this->id, $url, 40, 40);

            $showUrl =  url("admin/agencies/{$this->id}");
            $link = "
                    <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                        <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                    </a>
                ";


            return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    $link
                </div>
            ";
        });
        $grid->column('total', __('salary'))->display(function ($usd) {
            $image = asset('images/dollar.jpg'); // Adjust path as needed
            return "<div style='display: flex; align-items: center; '>

                        <span>{$usd}</span>
                          <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        })->default(0);

        $grid->column('cashing', __('cashing'))->display(function () {
            $options = ['agency' => __('agency')];
            return (new SalariesAction($this->id, 'agency'))->render();
        });
        $grid->column('pay', __('pay'))->display(function () {
            return (new PaySalariesAction($this->id, 'agency', $this->salary))->render();
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append('<a href="' . url('/admin/sallaries_history?type=1') . '"  class="btn btn-sm btn-success">' . __('admin.history') . '</a>');
        });
        return $grid;
    }

    private function salaryNavbar()
    {
        $content = new Row();

        $box = (new Box(
            title: __('Fields'),
            content: view('admin.grid.common.salaries')
        ));
        $content->column(12, $box);
        $box = (new Box(
            title: __('Details'),
            content: view('admin.grid.common.salaries-statistics')
        ))->collapsable();
        $content->column(12, $box);


        return $content;
    }
}
