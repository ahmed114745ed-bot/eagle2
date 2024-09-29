<?php

namespace Modules\Events\Http\Controllers\web;

use App\Models\Gift;
use App\Models\OVip;
use App\Models\Ware;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Admin;

use App\Selectables\Gifts;
use App\Helpers\UserCommon;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Layout\Content;
use Encore\Admin\Auth\Permission;
use Modules\Events\Entities\Reward;
use Modules\Events\Entities\WeeklyStar;
use App\Admin\Controllers\MainController;
use App\Services\AppFeatureService;
use Encore\Admin\Controllers\HasResourceActions;

class EventPeriodController extends MainController
{
    use HasResourceActions;

        public $permission_name = 'weekly-star';
    public $hiddenColumns = [

    ];
    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable("period_event");
    }
    public function index ( Content $content )
    {
        return $content
            ->title(__($this->title))
            ->row(function (Row $row) {
                $row->column(12, $this->grid2());
            })
            ->row(function (Row $row) {
                $row->column(12, $this->grid());
            });
    }
    protected function grid2()
    {
        $form = new Box();
        $form->view('admin.grid.users.EventPeriodView');

        return $form;
    }
    protected function grid()
    {
        $grid = new Grid(new WeeklyStar());
        $grid->model()->whereType("event_period");
        $grid->column('id', __('Id'));
        $grid->column('start_date_local', __('Start Date'));
        $grid->column('end_date_local', __('End Date'));
        $grid->column('created_at', __('Created at'));
        $grid->column( 'الاجرائات')->display(function () {
            // توليد الروابط
            $url1 = url('admin/weekly-events-gift/1/'.$this->id);
            $url2 = url('admin/weekly-events-gift/2/'.$this->id);
            $url3 = url('admin/weekly-events-gift/3/'.$this->id);

            // إنشاء أزرار HTML
            $button1 = "<a href='{$url1}' class='btn btn-sm btn-info'>هداية الفائز الاول</a>";
            $button2 = "<a href='{$url2}' class='btn btn-sm btn-danger'>هداية الفائز الثاني</a>";
            $button3 = "<a href='{$url3}' class='btn btn-sm btn-primary'>هداية الفائز الثالث</a>";

            // دمج الأزرار في سلسلة واحدة وإرجاعها
            return $button1 . ' ' . $button2 . ' ' . $button3;
        });

        $check_event_period=WeeklyStar::where("type",'event_period')->where("start_date",'<=',date("Y-m-d"))->where("end_date",'>=',date("Y-m-d"))->first();
        if ($check_event_period != null){
            $grid->disableCreateButton();
        }
        return $grid;
    }

    public function store()
    {
        $data = request()->all();
//        $data['start_date'] = UserCommon::convertArabicNumbers(request()['start_date']);
        $data['type']='event_period';
        request()->merge($data);
        return parent::store();
    }

    protected function form()
    {
        $form = new Form(new WeeklyStar);
        $form->display(__('admin.ID'));
        $form = new Form(new WeeklyStar());
        $form->hidden('type','Type')->default('event_period');
        $form->date('start_date', __('Start Date'))->default(date("Y-m-d"));
        $form->date('end_date', __('End Date'))->default(date("Y-m-d"));
        $form->belongsToMany('gifts', Gifts::class)
            ->rules('required|array|size:3', [
                'size' => __('choose only 3 gifts.'),
            ]);

        return $form;
    }

    protected function detail($id)
    {
        $show = new Show(WeeklyStar::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('start_date', __('Start date'));
        $show->field('type', __('Type'))->as(function ($type) {
            return  $type == 1?"gifts": ($type == 0?"charges":"PK" );
        });


        $this->extendShow ($show);

        return $show;
    }

    public function show ( $id , Content $content )
    {
        return $content
        ->row ("<h3>".__('weekly Star')."</h3>")->row (function ($row) use ($id){
            $row->column(12, $this->weeklyStar($id));
        })
        ->row ("<h3>".__('gifts')."</h3>")->row (function ($row) use ($id){
            $row->column(12, $this->giftList($id));
        })
            ->row ("<h3>".__('Rewards')."</h3>")->row (function ($row) use ($id){
                $row->column(12, $this->rewardList($id));
            })
        ;

    }


    protected function weeklyStar($id){

        $grid = new Grid(new WeeklyStar());
        $grid->model()->where('id',$id);

        $grid->column('start_date', __('Start date'));
        $grid->column('end_date', __('End date'));
        $grid->column('type', __('Type'))->display(function ($type) {

        return  $type == 1?"gifts": ($type == 0?"charges":"PK" );

        });

        $grid->disableActions();
        $grid->disableCreateButton ();
        $grid->disableFilter ();
        $grid->disableRowSelector ();
        $grid->disableExport ();

        return $grid;
    }
    protected function rewardList($id){

        $grid = new Grid(new Reward);
        $grid->model()->where('weekly_star_id',$id);

        $grid->column('level',trans ('level'));
         $grid->column('type',trans ('type'))->display(function ($type) {

            return   $type == "coins"?"coins": ($type == "ware"?"ware": ($type=="vip"? "vip":'achievement') );

        });
        $grid->column('target',trans ('target'))->display(function ($target){

                if($this->type == "coins"){
                    return $target;
                }elseif($this->type =="ware" ){
                    $ware = Ware::find($target);
                    return $ware->name;
                }elseif($this->type =="vip" ){
                  $vip = OVip::find($target);
                  return $vip->name;
                }else{
                    $value = getDriverUrl(). $target;
                    return "<img src='$value' width='80' height='80'>";
                }


        });

        $grid->disableActions ();
        $grid->disableCreateButton ();
        $grid->disableFilter ();
        $grid->disableRowSelector ();
        $grid->disableExport ();

        return $grid;
    }


    protected function giftList($id){
        $weeklyEvent = WeeklyStar::find($id);
        $giftIds = $weeklyEvent->gifts->pluck('id')->toArray();
        $grid = new Grid(new Gift);
        $grid->model()->whereIn('id',$giftIds);
        $grid->name(__('name'));
        $grid->column('img',trans ('image'))->image ('','30');

        $grid->disableActions ();
        $grid->disableCreateButton ();
        $grid->disableFilter ();
        $grid->disableRowSelector ();
        $grid->disableExport ();

        return $grid;
    }
}
