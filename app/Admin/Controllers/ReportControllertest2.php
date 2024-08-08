<?php
namespace App\Admin\Controllers;
use App\Admin\Extensions\AgencyExporter;
use App\Admin\Extensions\UserExporter;
use App\Helpers\Common;
use App\Models\Agency;
use App\Models\SalaryTrx;
use App\Models\User;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Request;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;


class ReportController extends MainController {


    protected function users(){
        //        if (request ('update_salary') == 'yes'){
        //            $users = User::query ()
        //                ->where ('agency_id','!=',0)
        //                ->where ('agency_id','!=','')
        //                ->where ('agency_id','!=',null)
        //                ->get ();
        //            foreach ($users as $user){
        //                $user->salary = 0;
        //                $user->save();
        //            }
        //        }
        
                $grid = new Grid(new User());
                $grid->model ()
        //            ->where ('salary','>',0)
                    // ->where ('agency_id','!=',0)
                    // ->where ('agency_id','!=','')
                    // ->where ('agency_id','!=',null)
                ;
        
                $grid->filter (function (Grid\Filter $filter){
                    $filter->expand ();
                    $filter->column(1/2, function ($filter) {
                        $filter->equal('uuid',__ ('uuid'));
                    });
        
                    $filter->column(1/2, function ($filter) {
                        $filter->equal('agency_id',__('agency'))->select(Common::by_agency_filter ());
                        // $filter->equal('salaries.year', __('Year'));
                        // $filter->equal('salaries.month', __('Month'));
                        $filter->where(function ($query) {
                            $year = Request::input('year');
                            if (!empty($year)) {
                                session(['selected_year' => $year]);
                                $query->whereHas('userSallary', function ($q) use ($year) {
                                    $q->where('year', $year);
                                });
                            }
                        }, __('Year'), 'year')->integer();
                    });
        
        
                        $filter->where(function ($query) {
                            $month = Request::input('month');
                            if (!empty($month)) {
                                session(['selected_month' => $month]);
                                $query->whereHas('userSallary', function ($q) use ($month) {
                                    $q->where('month', $month);
                                });
                            }
                        }, __('Month'), 'month')->integer();
        
        
                });
                $selectedMonth = session('selected_month');
                $selectedYear = session('selected_selected_year');
                $grid->column ('id',__ ('id'));
                $grid->column ('uuid',__ ('uuid'));
                $grid->column ('name',__ ('name'));
               // $grid->column('monthly_diamond_received',_('diamond'));
                $grid->column ('target',__ ('target'))->display (function () use($selectedMonth, $selectedYear) {return @$this->getTotalSallary($selectedMonth,$selectedYear)??0;});
                $grid->column ('expenses',__ ('expenses'))->display (function ()use($selectedMonth, $selectedYear){return @$this->getTotalCutAmount($selectedMonth,$selectedYear)??0;});
                $grid->column ('old',__ ('old'))->display (function ()use($selectedMonth, $selectedYear){return $this->getOld($selectedMonth,$selectedYear)?:0;});
                $grid->column ('total',__ ('salary'))->display (
                    function ()use($selectedMonth, $selectedYear){
        
        
                        return $this->getSalary($selectedMonth, $selectedYear)??0;
                    });
              
                $grid->column ('agency',__ ('agency'))->display (function (){return @$this->agency->name;});
                $grid->column ('cashing',__ ('cashing'))->display (function (){
                    $options = ['user'=>__('user')];
                    return (new \App\Admin\Actions\SalaryAction($this->id,'user'))->render () ;
                });
                $grid->tools(function (Grid\Tools $tools) {
                    $tools->append('<a href="' . route('custom-export-users', ['month' => request()->month, 'year' => request()->year, 'agency_id' => request('agency_id')]) . '" target="_blank" class="btn btn-sm btn-success"><i class="fa fa-download"></i> Export Excel</a>');
                });
        //        $grid->exporter(new UserExporter());
                return $grid;
            }

}