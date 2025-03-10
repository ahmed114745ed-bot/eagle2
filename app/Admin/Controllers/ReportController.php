<?php

namespace App\Admin\Controllers;

use App\Models\User;
use App\Models\Agency;
use Encore\Admin\Grid;
use App\Helpers\Common;
use App\Models\AdminUser;
use App\Models\SalaryTrx;
use App\Facades\ManagerHelper;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Facades\Excel;
use App\Admin\Extensions\UserExporter;
use Illuminate\Support\Facades\Request;
use App\Admin\Extensions\AgencyExporter;
use App\Admin\Controllers\MainController;

class ReportController extends MainController
{
    public $permission_name = 'report';
    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('reports'))
            ->description(__(request('desc') ?: 'users'))
            ->row(function ($row) {
                $row->column(2, view('admin.grid.common.actions'));
                $row->column(10, $this->grid());
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



    
    // user reports
    protected function users()
    {
       

        $grid = new Grid(new User());
        $grid->model()
            //            ->where ('salary','>',0)
             ->where ('agency_id','!=',0)
             ->where ('agency_id','!=','')
             ->where ('agency_id','!=',null);

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('uuid', __('uuid'));
            });

            $filter->column(1 / 2, function ($filter) {
                $filter->equal('agency_id', __('agency'))->select(Common::by_agency_filter());
                // $filter->equal('salaries.year', __('Year'));
                // $filter->equal('salaries.month', __('Month'));
                $filter->where(function ($query) {
                    $year = Request::input('year');
                    if (!empty($year)) {
                        $query->whereHas('userSallary', function ($q) use ($year) {
                            $q->where('year', $year);
                        });
                    }
                }, __('Year'), 'year')->integer();
            });


            $filter->where(function ($query) {
                $month = Request::input('month');
                if (!empty($month)) {

                    $query->whereHas('userSallary', function ($q) use ($month) {
                        $q->where('month', $month);
                    });
                }
            }, __('Month'), 'month')->integer();
        });
        $grid->column('id', __('Id'));
      
        $grid->column('name', __('user'))->display(function ($name) {
            $name = @$this->name ?? '';
            $uid = @$this->uuid;
            $path = @$this?->profile?->avatar;
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl =  ($this) ? url("admin/users/{$this->id}") : 0;
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
        });
         $grid->column('monthly_diamond_received',__('diamond'))->display(function () {
           $diamond= @$this->getTotalDiamond(request()->month, request()->year)?? 0;
           $image = asset('images/diamond.jpg'); // Adjust path as needed
           return "<div style='display: flex; align-items: center; '>
                     
                       <span>{$diamond}</span>
                         <img src='{$image}' alt='USD' width='20' height='20'>
                   </div>";
        });
        $grid->column('target', __('target'))->display(function () {
            return @$this->getTotalSallary(request()->month, request()->year) ?? 0;
        });
        $grid->column('expenses', __('expenses'))->display(function () {
            return @$this->getTotalCutAmount(request()->month, request()->year) ?? 0;
        });
       
        $grid->column('total', __('salary'))->display(function () {
            $salary = $this->getSalary(request()->month, request()->year) ?? 0;
            $image = asset('images/dollar.jpg'); // Adjust path as needed
            return "<div style='display: flex; align-items: center; '>
                      
                        <span>{$salary}</span>
                          <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        });

        $grid->column('agency', __('agency'))->display(function () {
            $name = @$this->agency->name ?? '';
            $path = @$this->agency->img;
            $defaultImage = asset("images/icon-agency.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
            $image = handleShowImageWithTypes($this->id, $url, 40, 40);

            return "
            <div style='display: flex; align-items: center; gap: 10px;'>
                $image
                <span>$name</span>
            </div>
        ";
        });
      
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append('<a href="' . route('custom-export-users', ['month' => request()->month, 'year' => request()->year, 'agency_id' => request('agency_id')]) . '" target="_blank" class="btn btn-sm btn-success"><i class="fa fa-download"></i>'. __('admin.exportExcel') .'</a>');
        });
        
        return $grid;
    }



    protected function agencies()
    {
        $grid = new Grid(new Agency());

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                // $filter->equal('salaries.year', __('Year'));
                // $filter->equal('salaries.month', __('Month'));
                $filter->where(function ($query) {
                    $year = Request::input('year');
                    if (!empty($year)) {
                        $query->whereHas('agencySalaries', function ($q) use ($year) {
                            $q->where('year', $year);
                        });
                    }
                }, __('Year'), 'year')->integer();
            });


            $filter->where(function ($query) {
                $month = Request::input('month');
                if (!empty($month)) {
                    $query->whereHas('agencySalaries', function ($q) use ($month) {
                        $q->where('month', $month);
                    });
                }
            }, __('Month'), 'month')->integer();
        });

        $grid->column('id', __('Id'));
        $grid->column('name', __('name'))
        ->display(function () {
            $name = @$this->name ?? '';
            $path = @$this->img;
            $defaultImage = asset("images/icon-agency.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
            $image = handleShowImageWithTypes($this->id, $url, 40, 40);

            return "
            <div style='display: flex; align-items: center; gap: 10px;'>
                $image
                <span>$name</span>
            </div>
        ";
        });
        $grid->column('target', __('target'))->display(function () {
            return @$this->getTotalSallaryAgency(request()->month, request()->year)?? 0;
        });
        $grid->column('expenses', __('expenses'))->display(function () {
            return @$this->getTotalCutAmountAgency(request()->month, request()->year)?? 0;
        });
       
        $grid->column('total', __('salary'))->display(function () {
            $salary= $this->getSalaryAgency(request()->month, request()->year)?? 0;
            $image = asset('images/dollar.jpg'); // Adjust path as needed
            return "<div style='display: flex; align-items: center; '>
                      
                        <span>{$salary}</span>
                          <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        });
        $grid->column('agent', __('agent'))->display(function () {
            return ;
                $name =@$this->owner->name ?: @$this->dashOwner->name;
                $uid = @$this->owner->uuid;
                $path =@$this->owner->profile?->avatar ?? @$this->dashOwner->avatar;
                $defaultImage = asset("images/businessman-icon.jpg");
                $url = getImagePath($path) ?? $defaultImage;
    
                // Check if the image exists
                if (!isImageExists($url)) {
                    $url = $defaultImage;
                }
               $id = @$this->owner->id ?: @$this->dashOwner->id ?? 0;
                $image = handleShowImageWithTypes($this->id, $url, 40, 40);
                $showUrl =   url("admin/users/{$id}") ;
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
            
        });
        $grid->column('users', __('users'))->display(function () {
            return '<a href="?name=users&desc=' . $this->name . '&aid=' . $this->id . '">' . $this->users()->count() . '</a>';
        });
        // $grid->column('cashing', __('cashing'))->display(function () {
        //     return (new \App\Admin\Actions\SalaryAction($this->id, 'agency'))->render();
        // });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->append('<a href="' . route('agency-export-report', ['month' => request()->month, 'year' => request()->year, 'agency_id' => request()->id]) . '" target="_blank" class="btn btn-sm btn-success"><i class="fa fa-download"></i> '. __('admin.exportExcel') .'</a>');
        });

        return $grid;
    }




    protected function agencies_manger()
    {

        $grid = new Grid(new AdminUser());
        $grid->model()
             ->where('app_id','!=',0);

        $grid->column('user.id', __('Id'));

        $grid->column('user.name', __('name'))->display(function ($name) {
            
            $uid = @$this->user->uuid;
            $path = @$this?->user->profile?->avatar;
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl =  ($this->user) ? url("admin/users/{$this->user->id}") : 0;
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

        $grid->column('due', __('due'))->display(function ($_){
            $salary= ManagerHelper::getTotalAgenciesSalary($this->managerAgencies, $this->app_id);
            $image = asset('images/dollar.jpg'); // Adjust path as needed
            return "<div style='display: flex; align-items: center; '>
                      
                        <span>{$salary}</span>
                          <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        });

        $grid->export(function ($export) {
            $export->filename('report');
            $export->column('uuid', function ($value, $original) {
                return $value;
            });
        });

        $grid->disableExport();

        return $grid;
    }
}
