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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use function request;

class SallariesController extends MainController
{
    public $permission_name = 'users-Wallet';

    public function index(Content $content)
    {
        checkAgencyFeature();

        return parent::index($content
            ->title(trans('Agencies Wallet'))
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
        $grid->disableRowSelector();

        $model =
            //$grid->model()->where('agency_id', '!=', 0)->LeftJoin('user_sallaries', 'users.id', '=', 'user_sallaries.user_id');
            $grid->model()->where('agency_id', '!=', 0);

        // $model->select('users.id', 'users.name', 'users.uuid', DB::raw('SUM(user_sallaries.sallary - user_sallaries.cut_amount) AS total'), DB::raw('SUM(user_sallaries.sallary) AS salary'), DB::raw('SUM(user_sallaries.cut_amount) AS withdrawal'))->groupBy('users.id', 'users.name', 'users.uuid')->orderByRaw('total DESC');

        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('uuid', __('uuid'));
            });

            $filter->column(1 / 2, function ($filter) {

                $filter->where(function ($query) {
                    $year = request('year');
                }, __('Year'), 'year')->integer();
            });

            $filter->where(function ($query) {
                $month = request('month');
            }, __('Month'), 'month')->integer();
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
        $grid->column('total', __('net salary'))
            ->display(function () {
                $usd =   @$this->sumNetSalary(request('month'), request('year')) ?? 0;
                $image = asset('images/dollar.jpg'); // Adjust path as needed
                $usd = rtrim(rtrim(number_format($usd, 10, '.', ''), '0'), '.');
                return "<div style='display: flex; align-items: center; '>

                        <span>{$usd}</span>
                          <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
            })->default(0);
        $grid->column('salary', __('salary'))->display(function () {
            $usd =   @$this->sumSalary(request('month'), request('year')) ?? 0;
            $image = asset('images/dollar.jpg'); // Adjust path as needed
            return "<div style='display: flex; align-items: center; '>

                        <span>{$usd}</span>
                          <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        })->default(0);
        $grid->column('withdrawal', __('withdrawal'))->display(function () {
            $usd =   @$this->sumCutAmount(request('month'), request('year')) ?? 0;

            $image = asset('images/dollar.jpg'); // Adjust path as needed
            return "<div style='display: flex; align-items: center; '>

                        <span>{$usd}</span>
                          <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        })->default(0);

        $grid->tools(function (Grid\Tools $tools) {
            //  $tools->append('<a href="' . url('admin/wallet-export-users?uuid=' . request('uuid')) . '" target="_blank" class="btn btn-sm btn-success"><i class="fa fa-download"></i>' . __('admin.exportExcel') . '</a>');
            $tools->append('<a href="' . url('admin/wallet-export-users?uuid=' . request('uuid') . '&month=' . request('month') . '&year=' . request('year')) . '" target="_blank" class="btn btn-sm btn-success"><i class="fa fa-download"></i>' . __('admin.exportExcel') . '</a>');

            $tools->append('<a href="' . url('/admin/sallaries_history?type=0') . '"  class="btn btn-sm btn-success">' . __('admin.history') . '</a>');
        });

        return $grid;
    }

    protected function agencies()
    {
        $grid = new Grid(new Agency());
        $grid->disableRowSelector();


        // $model = $grid->model()
        //     ->LeftJoin('agency_sallaries', 'agencies.id', '=', 'agency_sallaries.agency_id')
        //     ->select('agencies.id', 'agencies.name', DB::raw('SUM(agency_sallaries.sallary - agency_sallaries.cut_amount) AS total', DB::raw('SUM(agency_sallaries.sallary) AS salary'), DB::raw('SUM(agency_sallaries.cut_amount) AS withdrawal')))
        //     //            ->where('agencies.id', request('id'))
        //     ->orderByRaw('total desc')
        //     ->groupBy('agencies.id', 'agencies.name');

        // if (request('salary_only') == 1) {
        //     $model->having('total', '>', 0);
        // }
        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->expand();

            $filter->where(function ($query) {
                $query->where('agencies.id', $this->input) // Match directly on agency_id
                    ->orWhereHas('owner', function ($subQuery) {
                        $subQuery->where('uuid', $this->input); // Match on related owner UUID
                    });
            }, __('UUID'), 'agency_or_uuid')->placeholder(__('search for agency or host by UUID'));

            $filter->column(1 / 2, function ($filter) {

                $filter->where(function ($query) {
                    $year = request('year');
                }, __('Year'), 'year')->integer();
            });

            $filter->where(function ($query) {
                $month = request('month');
            }, __('Month'), 'month')->integer();
        });

        $grid->column('name', __('Agency'))
            ->display(function ($name) {
                $cacheKey = "agency_image_{$this->id}";
                $image = Cache::remember($cacheKey, 3600, function () {
                    $path = @$this->img;
                    $defaultImage = asset("images/icon-agency.jpg");
                    $url = getImagePath($path) ?? $defaultImage;

                    if (!isImageExists($url)) {
                        $url = $defaultImage;
                    }

                    return handleShowImageWithTypes($this->id, $url, 40, 40);
                });

                $profileUrl = route('admin.agency.profile', ['id' => $this->id]);

                return "
                    <a href='{$profileUrl}' style='text-decoration: none; color: inherit;'>
                        <div style='display: flex; align-items: center; gap: 10px;'>
                            {$image}
                            <div style='display: flex; flex-direction: column;'>
                                <span style='text-decoration: underline; cursor: pointer;'>{$name}</span>
                                <span style='font-size: smaller;'>ID: {$this->id}</span>
                            </div>
                        </div>
                    </a>
                ";
            });
        $grid->column('total', __('net salary'))
            ->display(function () {
                $usd =   @$this->sumNetSalary(request('month'), request('year')) ?? 0;
                $image = asset('images/dollar.jpg'); // Adjust path as needed
                $usd = rtrim(rtrim(number_format($usd, 10, '.', ''), '0'), '.');
                return "<div style='display: flex; align-items: center; '>

                        <span>{$usd}</span>
                          <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
            })->default(0);
        $grid->column('salary', __('salary'))->display(function () {
            $usd =   @$this->sumSalary(request('month'), request('year')) ?? 0;
            $image = asset('images/dollar.jpg'); // Adjust path as needed
            return "<div style='display: flex; align-items: center; '>

                        <span>{$usd}</span>
                          <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        })->default(0);
        $grid->column('withdrawal', __('withdrawal'))->display(function () {
            $usd =   @$this->sumCutAmount(request('month'), request('year')) ?? 0;

            $image = asset('images/dollar.jpg'); // Adjust path as needed
            return "<div style='display: flex; align-items: center; '>

                        <span>{$usd}</span>
                          <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        })->default(0);


        $grid->tools(function (Grid\Tools $tools) {
            //  $tools->append('<a href="' . url('admin/wallet-export-agency?id=' . request('2f787fe5f965209024c8597149cbb43e')) . '" target="_blank" class="btn btn-sm btn-success"><i class="fa fa-download"></i>' . __('admin.exportExcel') . '</a>');
            $tools->append('<a href="' . url('admin/wallet-export-agency') . '?' . http_build_query([
                'id' => request('agency_or_uuid'),
                'month' => request('month'),
                'year' => request('year'),
            ]) . '" target="_blank" class="btn btn-sm btn-success"><i class="fa fa-download"></i> ' . __('admin.exportExcel') . '</a>');
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
