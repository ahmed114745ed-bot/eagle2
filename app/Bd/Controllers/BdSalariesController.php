<?php

namespace App\Bd\Controllers;

use App\Admin\Controllers\MainController;
use App\Helpers\Common;
use App\Models\Admin;
use App\Models\Charge;
use App\Models\BDSallary;
use App\Models\User;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;
use Encore\Admin\Widgets\InfoBox;
use Encore\Admin\Layout\Row;
use Encore\Admin\Controllers\AdminController;



class BdSalariesController extends AdminController
{
    use HasResourceActions;

    protected $title = "Charges";

    public $permission_name = "browse-get-salary-bd";

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        $appID = Auth::user()->app_id;

        $netSalary = BDSallary::where('bd_id', $appID)
        ->selectRaw('SUM(sallary) as total_sallary, SUM(cut_amount) as total_cut')
        ->first();
        $totalCut =$netSalary->total_cut;
        $total_sallary =$netSalary->total_sallary;
        $finalSalary = ($netSalary->total_sallary ?? 0) - ($netSalary->total_cut ?? 0);
            return $content
                ->header(trans('admin.index'))
                ->description(trans('admin.description'))

        ->row(function ($row) use ($finalSalary) {
            // الكارت سيتم تضمينه من Blade View
            // $row->column(12, view('admin.grid.bd.sallary', ['finalSalary' => $finalSalary]));
            $row->column(12, view('admin.grid.bd.wallet', ['finalSalary' => $finalSalary]));
        })
        ->row(function (Row $row) use ($total_sallary, $totalCut ) {
            $row->column(6, new InfoBox(__('total_sallary'), 'money', 'green', '', $total_sallary  . ' 💰' ));
            $row->column(6, new InfoBox(__('totalCut'), 'money', 'red', 'charges', number_format($totalCut)));
        })

        ->row(function ($row) {
            $row->column(12, $this->grid());
        });
    }

    

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new BDSallary);
        $appID = Auth::user()->app_id;

 
        $grid->model()
            ->where('bd_id', $appID)
            ->with('agency'); // تحميل العلاقة
            // ->selectRaw('agency_id, SUM(sallary) as total_sallary, SUM(cut_amount) as total_cut, COUNT(*) as count')
            // ->groupBy('agency_id');
    
        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
        
            $filter->equal('month', __('Month'))->select([
                1 => __('January'),
                2 => __('February'),
                3 => __('March'),
                4 => __('April'),
                5 => __('May'),
                6 => __('June'),
                7 => __('July'),
                8 => __('August'),
                9 => __('September'),
                10 => __('October'),
                11 => __('November'),
                12 => __('December'),
            ]);
        
            $currentYear = now()->year;
            $years = [];
            for ($i = $currentYear; $i >= $currentYear - 10; $i--) {
                $years[$i] = $i;
            }
            $filter->equal('year', __('Year'))->select($years);
        });
        
        $grid->column('agency.name', trans('agency'))->display(function () {
            $agency = $this->agency;
            if (request()->filled('_export_')) {
                return $agency?->name;
            }
            if (!$agency) {
                return "<span style='color:red;'>No agency</span>";
            }
    
            $cacheKey = "agency_image_{$agency->id}";
            $image = \Cache::remember($cacheKey, 3600, function () use ($agency) {
                $path = @$agency->img;
                $defaultImage = asset("images/icon-agency.jpg");
                $url = getImagePath($path) ?? $defaultImage;
    
                if (!isImageExists($url)) {
                    $url = $defaultImage;
                }
    
                return handleShowImageWithTypes($agency->id, $url, 40, 40);
            });
    
            $profileUrl = route('bd.agency.profile', ['id' => $agency->id]);
    
            return "
                <a href='{$profileUrl}' style='text-decoration: none; color: inherit;'>
                    <div style='display: flex; align-items: center; gap: 10px;'>
                        {$image}
                        <div style='display: flex; flex-direction: column;'>
                            <span style='text-decoration: underline; cursor: pointer;'>{$agency->name}</span>
                            <span style='font-size: smaller;'>ID: {$agency->id}</span>
                        </div>
                    </div>
                </a>
            ";
        });
    
        $grid->column('sallary', trans('totalBd'));
        // $grid->column('total_cut', trans('cut'));
    
        // $grid->column('created_at', __('Created at'))->display(function ($value) {
        //     return Carbon::parse($value)->translatedFormat('d F Y - h:i A');
        // });     
    
        $grid->column('total_agency_sallary', __('Total Agency Sallary'));
        $grid->column('total_users_sallary', __('Total Users Sallary'));
        $grid->column('total_diamond', __('Total Diamond'));
        $grid->column('month', __('month'));
        $grid->column('year', __('year'));
        // $grid->tools(function (Grid\Tools $tools) {
        //     $url = 'charges';
        //     $button = '<a href="' . $url . '" class="btn btn-sm btn-success"><i class="fa fa-go"></i>&nbsp;&nbsp;' . __("Charge History") . '</a>';
        //     $tools->append($button);
        // });
        return $grid;
    }
    
    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Charge::find($id));


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
        $form = new Form(new Charge);



        return $form;
    }
}
