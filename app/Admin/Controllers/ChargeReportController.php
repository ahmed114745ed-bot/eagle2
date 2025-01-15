<?php
namespace App\Admin\Controllers;
use App\Admin\Forms\CustomForm;
use Encore\Admin\Show;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Charge;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use App\Helpers\Common;
use App\Models\CoinLog;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Admin\Actions\SalariesAction;
use App\Admin\Extensions\UserExporter;
use Illuminate\Support\Facades\Request;
use App\Admin\Extensions\AgencyExporter;

class ChargeReportController extends MainController {
    public $permission_name = 'charger-report';

    public function index ( Content $content )
    {
        return parent::index($content
            ->title(trans("Reports"))
            ->description("Charges")
            ->row(function(Row $row) {
                $row->column(12, $this->tabsComponent());
            })
            ->row(function(Row $row) {
                $row->column(12, $this->grid());
            }));
    }

    protected function grid(){
        $name= "result";
        if (request("name") == "stripe") {
            $name="stripe";
        }
        if (request("name") == "in-app-purchas") {
            $name="in_app_purchas";
        }


        $grid = $name;
        $grid = $this->{$grid}();
        $grid->disableexport();
        $grid->disableActions ();
        $grid->disableCreateButton ();
        $grid->disableColumnSelector ();

        return $grid;
    }

    public function form()
    {
        $form = new Form(new Charge);
        return $form;
    }


    protected function result()
    {
        $charger_type = "dash";
        if (request("name") == "app") {
            $charger_type = "app";
        }

        $grid = new Grid(new Charge());
        $grid->model ()->orderByDesc('created_at')->with(['sender', 'receiver']);

        if ($charger_type == "dash") {
            $grid->model()->where ('charger_type',"dash");
        }else{
            $grid->model()->where ('charger_type',"!=","dash");
        }

        $grid->filter (function (Grid\Filter $filter) use($charger_type){

            $filter->expand ();
            if ($charger_type == "dash" ) {
                $filter->column(1/2, function ($filter) {
                    $filter->equal('receiver.uuid', __("sendTo"));
                });
            }else{
                $filter->column(1/2, function ($filter) {
                    $filter->equal('sender.uuid', __('receiver'));
                });

                $filter->column(1/2, function ($filter) {
                    $filter->equal('receiver.uuid', __('sendTo'));
                });
            }
        });


        $grid->column ('id',__ ('id'));
        $grid->column ('charger_id',__("receiver"))->display (function () use ($charger_type){
            if ($charger_type == "dash") {
                return @$this->admin_user->name ."<br>"."#".@$this->admin_user->id;
            }else{
                return @$this->sender->name ."<br>"."#".@$this->sender->uuid;
            }
        });
        $grid->column ('user_id',__ ('sendTo'))->display (function ($recever){
            if (!isset($this->receiver->name)) {
                return "not user found";
            }
            return @$this->receiver->name."<br>"."#".@$this->receiver->uuid;
        });
        $grid->column ('amount',__("amount"));
        $grid->column ('balance_before',__("balance_before"));
        $grid->column ('balance_after',__("balance_after"))->display (function (){
           return $this->amount + $this->balance_before;
        });
        $grid->column('created_at', __('Created at'))->sortable()->diffForHumans();

        return $grid;
    }

    protected function stripe()
    {
        $grid = new Grid(new CoinLog());
        $grid->model ()->orderByDesc('created_at')->where('method', '!=', 'huawei_pay')->where('method', '!=', 'google_pay')->where('method', '!=', 'apple_pay');
        $grid->filter (function (Grid\Filter $filter){
            $filter->column(1/2, function ($filter) {
                $filter->equal('user.uuid', __('charger'));
            });
        });


        $grid->column ('id',__ ('id'));
        $grid->column ('user_id',__ ('charger'))->display (function (){
            if (!isset($this->user->name)) {
                return "not found user";
            }
            return @$this->user->name ."<br>"."#".@$this->user->uuid;
        });
        $grid->column ('obtained_coins',__ ('amount'));
        $grid->column ('trx',__ ('trx'));
        $grid->column ('status',__ ('status'))->display (function (){
            if ($this->status == 1) {
                return "success";
            }elseif ($this->status == 0) {
                return "faild";
            }
        });
        $grid->column('created_at', __('Created at'))->sortable()->diffForHumans();
        return $grid;
    }

    protected function in_app_purchas()
    {
        // dd(request('uuid'));

        $grid = new Grid(new CoinLog());
        $grid->model ()->orderByDesc('created_at')->whereIn('method', ['huawei_pay','google_pay', 'apple_pay']);

        $grid->filter (function (Grid\Filter $filter){
            $filter->column(1/2, function ($filter) {
                $filter->equal('user.uuid', __('charger'));
            });

            $filter->disableIdFilter();
            $filter->where(function ($query) {
                if ($this->input != null) {
                    $query->where('method', $this->input);
                }
            }, __('Select type'), 'name_for_url_shortcut')->radio([
                                                                         '' => __('All'),
                                                                         'huawei_pay' => __('huawei_pay'),
                                                                         'google_pay' => __('google_pay'),
                                                                         'apple_pay' => __('apple_pay'),
                                                                     ]);
        });

        $grid->quickSearch ();
        $grid->column ('id',__ ('id'));
        $grid->column ('user_id',__ ('charger'))->display (function (){
            if (!isset($this->user->name)) {
                return "not found user";
            }
            return @$this->user->name ."<br>"."#".@$this->user->uuid;
        });
        $grid->column ('obtained_coins',__ ('amount'));
        $grid->column ('trx',__ ('trx'));
        $grid->column ('status',__ ('status'))->display (function (){
            if ($this->status == 1) {
                return "success";
            }elseif ($this->status == 0) {
                return "faild";
            }
        });
        $grid->column('created_at', __('Created at'))->sortable()->diffForHumans();
        // $grid->column('action', __('action'))->display (function (){
        //     return '<a href="?name=in-app-purchas&id='.@$this->id.'" class="btn btn-xs btn-danger">'.__("Return").'</a>';
        // });
        $grid->column ('return',__ ('Return'))->display (function (){
            return (new \App\Admin\Actions\ReturnDiAction($this->id))->render () ;
        });
        return $grid;
    }

    private function tabsComponent()
    {
        $content = new Row();

        $box = (new Box(
            title: __('Fields'),
            content: view('admin.grid.common.report.charge')
        ));
        $content->column(12, $box);
        $box = (new Box(
            title: __('Details'),
            content: view('admin.grid.common.report.show-statistics-for-charge')
        ))->collapsable();
        $content->column(12, $box);


        return $content;
    }
}

class TemporaryModel extends Model
{
    // Prevent Laravel from trying to map the model to a database table
    protected $table = null;

    // Disable timestamps
    public $timestamps = false;

    // Disable incrementing IDs and primary key
    protected $primaryKey = null;
    public $incrementing = false;

    // Disable auto connection to the database
    protected $connection = null;

    // Optionally, define fillable attributes if you want to use it like a regular model
    protected $fillable = ['name', 'age', 'email'];

    
    public function getTestAttribute() : string
    {
        return 'this is test attribute';
    }
}
