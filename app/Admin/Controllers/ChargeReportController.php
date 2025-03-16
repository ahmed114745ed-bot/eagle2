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
                    $filter->equal('receiver.uuid', __("receiver"));
                });
            }else{
                $filter->column(1/2, function ($filter) {
                    $filter->equal('sender.uuid', __('Sender'));
                });

                $filter->column(1/2, function ($filter) {
                    $filter->equal('receiver.uuid', __('receiver'));
                });
            }
        });


        $grid->column ('id',__ ('id'));
        $grid->column ('charger_id',__("receiver"))->display (function () use ($charger_type){
            if ($charger_type == "dash") {
                $name= @$this->admin_user->name?? '' ;
                $uuid =@$this->admin_user->id;
                $path = @$this->admin_user->avatar;

            }else{
               $name= @$this->sender->name ?? '';
               $uuid =@$this->sender->uuid;
               $path = @$this->sender?->profile?->avatar;
            }

            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
                $image = handleShowImageWithTypes($this->id, $url, 40, 40);

                return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                        <strong>$name</strong><br>
                        <span style='color: #aaa; font-size: smaller;'>UID: $uuid</span>
                    </div>
                </div>
            ";

        });
        $grid->column ('user_id',__ ('sendTo'))->display (function ($recever){
           $name =  $this->receiver->name ?? '';
            $uid = @$this->receiver->uuid ?? 0;
            $path = @$this->receiver?->profile?->avatar;
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
            $image = handleShowImageWithTypes($this->id, $url, 40, 40);

            return "
            <div style='display: flex; align-items: center; gap: 10px;'>
                $image
                <div>
                    <strong>$name</strong><br>
                    <span style='color: #aaa; font-size: smaller;'>UID: $uid</span>
                </div>
            </div>
        ";

        });
        // $grid->column ('amount',__("amount"))->display (function ($coin){
        //     return number_format($coin);
        //  });
        // $grid->column ('balance_before',__("balance_before"))->display (function ($coin){
        //     return number_format($coin);
        //  });
        // $grid->column ('balance_after',__("balance_after"))->display (function (){
        //    return number_format($this->amount + $this->balance_before);
        // });
        $grid->column('amount', __("Amount"))->display(function ($coin) {
            $image = asset('images/coin.png'); // تأكد من أن الصورة موجودة

            return "<div style='display: flex; align-items: center; gap: 5px;'>
                        <span>{$coin}</span>
                        <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";

        });

        $grid->column('balance_before', __("Balance Before"))->display(function ($coin) {
            $balance_after = $this->amount + $this->balance_before;
            $icon = asset('images/arrowdown.png'); // أيقونة نزول إذا كان الرصيد بعد أقل من قبل

            return "<div style='display: flex; align-items: center; gap: 5px;'>
            <span>
            ".number_format($balance_after)."</span>
            <img src='{$icon}' alt='USD' width='20' height='20'>
            </div>";

        });

        $grid->column('balance_after', __("Balance After"))->display(function () {

            $balance_after = $this->amount + $this->balance_before;
            $icon =asset('images/arrows.png'); // أيقونة صعود أو نزول حسب المبلغ

            return "<div style='display: flex; align-items: center; gap: 5px;'>
            <span>
            ".number_format($balance_after)."</span>
            <img src='{$icon}' alt='USD' width='20' height='20'>
            </div>";

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
        $grid->column ('user_id',__ ('charger'))->display (function ($recever){
            $name =  $this->user->name ?? '';
             $uid = @$this->user->uuid ?? 0;
             $path = @$this->user?->profile?->avatar;
             $defaultImage = asset("images/businessman-icon.jpg");
             $url = getImagePath($path) ?? $defaultImage;

             // Check if the image exists
             if (!isImageExists($url)) {
                 $url = $defaultImage;
             }
             $image = handleShowImageWithTypes($this->id, $url, 40, 40);

             return "
             <div style='display: flex; align-items: center; gap: 10px;'>
                 $image
                 <div>
                     <strong>$name</strong><br>
                     <span style='color: #aaa; font-size: smaller;'>UID: $uid</span>
                 </div>
             </div>
         ";

         });
        // $grid->column ('obtained_coins',__ ('amount'))->display (function ($coin){
        //     return number_format($coin);
        //  });
        $grid->column('obtained_coins', __('Amount'))->display(function ($coin) {
            $icon = asset('images/coin.jpg'); // تأكد من وجود الصورة في هذا المسار
            return "
                <div style='display: flex; align-items: center; gap: 5px;'>
                  <span>" . number_format($coin) . "</span>
                    <img src='{$icon}' alt='Coin' width='20' height='20'>
                </div>
            ";
        });
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
        $grid->column ('user_id',__ ('charger'))->display (function ($recever){
            $name =  $this->user->name ?? '';
             $uid = @$this->user->uuid ?? 0;
             $path = @$this->user?->profile?->avatar;
             $defaultImage = asset("images/businessman-icon.jpg");
             $url = getImagePath($path) ?? $defaultImage;

             // Check if the image exists
             if (!isImageExists($url)) {
                 $url = $defaultImage;
             }
             $image = handleShowImageWithTypes($this->id, $url, 40, 40);

             return "
             <div style='display: flex; align-items: center; gap: 10px;'>
                 $image
                 <div>
                     <strong>$name</strong><br>
                     <span style='color: #aaa; font-size: smaller;'>UID: $uid</span>
                 </div>
             </div>
         ";

         });
        // $grid->column ('obtained_coins',__ ('amount'))->display (function ($coin){
        //     return number_format($coin);
        //  });

        $grid->column('obtained_coins', __('amount'))->display(function ($coin) {
            $icon = asset('images/coin.jpg'); // تأكد من وجود الصورة في هذا المسار
            return "
                <div style='display: flex; align-items: center; gap: 5px;'>
                    <span>" . number_format($coin) . "</span>
                    <img src='{$icon}' alt='Coin' width='20' height='20'>

                </div>
            ";
        });
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
