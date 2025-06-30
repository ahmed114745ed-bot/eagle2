<?php

namespace App\Admin\Controllers;

use App\Helpers\UserCommon;
use App\Models\User;
use App\Models\Charge;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\CoinLog;
use App\Enums\PaymentType;
use App\Models\ExchangeLog;
use App\Models\PaymentCoin;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use App\Admin\Forms\CustomForm;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Admin\Actions\SalariesAction;
use App\Admin\Extensions\UserExporter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use App\Admin\Extensions\AgencyExporter;

class UserChargeReportController extends MainController
{
    public $permission_name = 'charger-reports';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans("Reports"))
            ->row(function (Row $row) {
                $row->column(12, function (Column $column) {
                    $box = new Box();
                    $box->title(__('user'));
                    $box->content($this->combinedContent());
                    $column->append($box);
                });
            }));


        //        return parent::index($content
        //            ->title(trans("Reports"))
        //            ->row(function (Row $row) {
        //                $row->column(12, $this->tabsComponent());
        //            })
        //            ->row(function (Row $row) {
        //                $row->column(12, $this->grid());
        //            }));
    }

    private function combinedContent()
    {
        $tabs = $this->tabsComponent();
        $grid = $this->grid()->render();

        return "<div style='margin-bottom: 20px;'>{$tabs}</div>{$grid}";
    }

    protected function grid()
    {
        $name = "result";



        $grid = $name;
        $grid = $this->{$grid}();
        $grid->disableexport();
        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();

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
        $grid->model()
        ->where('user_id', '=', request('id'))
        ->orderByDesc('created_at')->with(['sender', 'receiver']);

        if ($charger_type == "dash") {
            $grid->model()->where('charger_type', "dash");
        } else {
            $grid->model()->where('charger_type', "!=", "dash");
        }

        $grid->filter(function (Grid\Filter $filter) use ($charger_type) {

            // $filter->expand();

            // if ($charger_type == "dash") {
            //     $filter->column(1 / 2, function ($filter) {
            //         $filter->equal('receiver.uuid', __("receiver"));
            //     });
            // } else {
            //     $filter->column(1 / 2, function ($filter) {
            //         $filter->equal('sender.uuid', __('Sender'));
            //     });

            //     $filter->column(1 / 2, function ($filter) {
            //         $filter->equal('receiver.uuid', __('receiver'));
            //     });
            // }
        });
        $grid->disableFilter();


        $grid->column('id', __('transaction id'));
        $grid->column('charger_id', __("sender"))->display(function () use ($charger_type) {
            // if ($charger_type == "dash") {
            //     $name = @$this->admin_user->name ?? '';
            //     $uuid = @$this->admin_user->id;
            //     $path = @$this->admin_user->avatar;
            // } else {
            //     $name = @$this->sender->name ?? '';
            //     $uuid = @$this->sender->uuid;
            //     $path = @$this->sender?->profile?->avatar;
            // }
            $sender = Common::getChargerInfo($this);
            $name = $sender['name'];
            $uuid = $sender['uuid'];
            $path = $sender['image'];
            $showUrl = $sender['url'];
           
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = $path ?? $defaultImage;

            // $defaultImage = asset("images/businessman-icon.jpg");
            // $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            // if (!isImageExists($url)) {
            //     $url = $defaultImage;
            // }
            $image = handleShowImageWithTypes($this->id, $url, 40, 40);

            return "
                 <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>

                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                        <strong>$name</strong><br>
                        <span style='color: #aaa; font-size: smaller;'>UID: $uuid</span>
                    </div>
                </div>
             </a>

            ";
        });
        // $grid->column('user_id', __('recipient'))->display(function ($recever) {
        //     $name =  $this->receiver->name ?? '';
        //     $uid = @$this->receiver->uuid ?? 0;
        //     $path = @$this->receiver?->profile?->avatar;
        //     $defaultImage = asset("images/businessman-icon.jpg");
        //     $url = getImagePath($path) ?? $defaultImage;

        //     // Check if the image exists
        //     if (!isImageExists($url)) {
        //         $url = $defaultImage;
        //     }
        //     $image = handleShowImageWithTypes($this->id, $url, 40, 40);

        //     return "
        //     <div style='display: flex; align-items: center; gap: 10px;'>
        //         $image
        //         <div>
        //             <strong>$name</strong><br>
        //             <span style='color: #aaa; font-size: smaller;'>UID: $uid</span>
        //         </div>
        //     </div>
        // ";
        // });
        if ($charger_type == "dash") {
            // $grid->column('agency_id', __('Agency'))->display(function () {
            //     if (!$this->agency) {
            //         return "<span style='color: #aaa;'>No Agency</span>";
            //     }

            //     $name = $this->agency->name ?? 'Unknown Agency';
            //     $coins = number_format($this->agency->coins ?? 0);
            //     $path = $this->agency->img ?? '';
            //     $defaultImage = asset("images/agency-icon.jpg");
            //     $url = getImagePath($path) ?? $defaultImage;
            //     $icon = asset('images/coin.jpg'); // تأكد من وجود الصورة في هذا المسار

            //     if (!isImageExists($url)) {
            //         $url = $defaultImage;
            //     }

            //     $image = handleShowImageWithTypes($this->id, $url, 40, 40);

            //     return "
            //     <div style='display: flex; align-items: center; gap: 10px;'>
            //         $image
            //         <div>
            //             <strong>$name</strong><br>
            //             <span style='color: green;'> Coins: $coins</span>
            //             <img src='{$icon}' alt='Coin' width='20' height='20'>
            //         </div>
            //     </div>
            //     ";
            // });
        }
        if ($charger_type == "dash") {
       

            // $grid->column('balance_before', __('balance_before') . ' ' . "<img src='{$image}' alt='USD' width='20' height='20' style='vertical-align: middle;'> ")
            //     ->display(function ($coin) {
            //         $image = asset('images/coin.png'); // تأكد من أن الصورة موجودة

            //         return "<div style='display: flex; align-items: center; gap: 5px;'>
            //                 <span>{$coin}</span>
            //                 <img src='{$image}' alt='USD' width='20' height='20'>
            //             </div>";
            //     });

                // $grid->column('usd', __('amount') . ' ' . "")
                //    ->display(function ($coin) {
                //     $image = asset('images/coin.png'); // تأكد من أن الصورة موجودة

                //     return "<div style='display: flex; align-items: center; gap: 5px;'>
                //             <span>{$coin}</span>
                //         </div>";
                // });

             
        } elseif ((request("name") == "host") || (request("name") == "app")) {
            // $grid->column('amount', __('amount'))->display(function ($coin) {
            //     $icon = asset('images/coin.jpg'); // تأكد من وجود الصورة في هذا المسار

            //     return "
            //         <div style='display: flex; align-items: center; gap: 5px;'>
            //             <span>" . number_format($coin) . "</span>
            //             <img src='{$icon}' alt='Coin' width='20' height='20'>

            //         </div>
            //     ";
            // });
        }


        //        $grid->column('balance_before', __("amount"))->display(function ($coin) {
        //            $balance_after = $this->amount + $this->balance_before;
        //            $icon = asset('images/coin.png'); // أيقونة نزول إذا كان الرصيد بعد أقل من قبل
        //
        //            return "<div style='display: flex; align-items: center; gap: 5px;'>
        //            <span>
        //            " . number_format($balance_after) . "</span>
        //            <img src='{$icon}' alt='USD' width='20' height='20'>
        //            </div>";
        //        });

        // $grid->column('balance_after', __("Balance After"))->display(function () {

        //     $balance_after = $this->amount + $this->balance_before;
        //     $icon = asset('images/arrows.png'); // أيقونة صعود أو نزول حسب المبلغ

        //     return "<div style='display: flex; align-items: center; gap: 5px;'>
        //     <span>
        //     " . number_format($balance_after) . "</span>
        //     <img src='{$icon}' alt='USD' width='20' height='20'>
        //     </div>";
        // });
        $grid->column('usd', __('amount $'))->display(function ($coin) {
            $icon = asset('images/dollar.jpg'); // تأكد من وجود الصورة في هذا المسار
            return "
                <div style='display: flex; align-items: center; gap: 5px;'>
                    <span>" . number_format($coin) . "</span>
                    <img src='{$icon}' alt='Coin' width='20' height='20'>

                </div>
            ";
        });
        $image = asset('images/coin.png');


        $grid->column('amount', __('coins') . ' ' . "<img src='{$image}' alt='USD' width='20' height='20' style='vertical-align: middle;'> ")
            ->display(function ($coin) {
            $image = asset('images/coin.png'); 

                return "<div style='display: flex; align-items: center; gap: 5px;'>
                        <span>{$coin}</span>
                        <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
                });

                $grid->column('change_type', __('change_type'))
                    ->display(function () {
                        if ($this->amount > 0) {
                            return "<span style='color:green; font-weight:bold;'>" . __('increase') . "</span>";
                        } elseif ($this->amount < 0) {
                            return "<span style='color:red; font-weight:bold;'>" . __('decrease') . "</span>";
                        } else {
                            return "<span style='color:gray;'>" . __('no_change') . "</span>"; 
                        }
                    });

        $grid->column('created_at', __('shipping date'));

        return $grid;
    }




    private function tabsComponent()
    {
        $user=User::find(request('id'));
        return view('admin.grid.common.report.usersCharge',compact('user') )->render();

        $content = new Row();

        $box = (new Box(
            title: __('user'),
            // content: view('admin.grid.common.report.charge')
        ))->collapsable();
        //        $content->column(12, $box);
        //        $content->column(12, $box);
        //        $box = (new Box(
        //            title: __('Details'),
        //            content: view('admin.grid.common.report.show-statistics-for-charge')
        //        ))->collapsable();
        //        $content->column(12, $box);


        return $box;
    }

    public function showChargeReports(Content $content, $agency_id)
    {
        if (!request()->has('scope')) {
            return redirect()->to(url()->current() . '?scope=dash');
        }

        return $content
            ->title(__('Charge Reports'))
            ->body($this->customGrid($agency_id));
    }

    protected function customGrid($agency_id)
    {
        $grid = new Grid(new Charge());
        $grid->model()->where('agency_id', $agency_id);

        // Add tabs to the header
        $grid->header(function () {
            $scope = request('scope', 'dash');
            return '
        <div class="tab-buttons">
            <a href="?scope=dash" class="tab-button btn-dash ' . ($scope === 'dash' ? 'active' : '') . '">' . __('Charged by dash') . '</a>
            <a href="?scope=not_dash" class="tab-button btn-agency ' . ($scope === 'not_dash' ? 'active' : '') . '">' . __('Charged by app') . '</a>
        </div>
    ';
        });

        // Apply scope based on query parameter
        $scope = request('scope');
        if ($scope === 'dash') {
            $grid->model()->where('charger_type', 'dash');
        } else {
            $grid->model()->where('charger_type', '!=', 'dash');
        }

        // Add filters
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->disableIdFilter();

            $filter->where(function ($query) {
                $date = UserCommon::arabicToEnglishNumbers($this->input);
                $query->whereDate('created_at', '>=', $date);
            }, __('from_date'), 'from_date')->date();

            $filter->where(function ($query) {
                $date = UserCommon::arabicToEnglishNumbers($this->input);
                $query->whereDate('created_at', '<=', $date);
            }, __('to_date'), 'to_date')->date();
        });

        // Define columns
        $grid->column('id', __('ID'));
        if ($scope === 'dash') {
            $grid->column('admin.name', __('creator'))->display(function () {
                $name = $this->admin->name ?? '';
                $path = $this->admin->avatar ?? null;
                $defaultImage = asset("images/businessman-icon.jpg");
                $url = getImagePath($path) ?? $defaultImage;

                if (!isImageExists($url)) {
                    $url = $defaultImage;
                }

                $image = handleShowImageWithTypes($this->id, $url, 40, 40);

                $showUrl = '#';
                if ($this->admin && $this->admin->id) {
                    $showUrl = url("admin/auth/users/{$this->admin->id}");
                }

                return "
                 <div style='display: flex; align-items: center; gap: 10px;'>
                     <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                         $image
                         <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                     </a>
                 </div>
                ";
            });
        }

        if ($scope !== 'dash') {
            $grid->column('charger_id', __('charger'))->display(function () {
                if ($this->charger_type == 'agency') {
                    $name = $this->agency->name ?? 'No Agency';
                    $path = $this->agency->img ?? null;
                    $defaultImage = asset("images/icon-agency.jpg");
                    $url = getImagePath($path) ?? $defaultImage;

                    if (!isImageExists($url)) {
                        $url = $defaultImage;
                    }
                    $image = handleShowImageWithTypes($this->id, $url, 40, 40);

                    $showUrl = $this->agency ? url("admin/agencies/{$this->agency->id}") : '#';
                    $link = $this->agency ? "
                                                <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                                                    <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                                                </a>
                                            " : "<span style='color: gray;'>No Agency</span>";

                    return "
                                <div style='display: flex; align-items: center; gap: 10px;'>
                                    $image
                                    $link
                                </div>
                            ";
                } else {
                    $name = $this->user->name ?? 'No User';
                    $uid = $this->user->uuid ?? 'N/A';
                    $path = $this->user->profile->avatar ?? null;
                    $defaultImage = asset("images/businessman-icon.jpg");
                    $url = getImagePath($path) ?? $defaultImage;

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
                }
            });
        }

        $grid->column('amount', __('Amount'));
        $grid->column('amount', __('Amount'));
        if ($scope === 'not_dash' ) {
           // dd(123);
            $grid->column('amount_type', __('status'))->display(function () use($agency_id){
                return $this->user_id == $agency_id  ?  __('increment') : __('decrement');
            });
        } else {
            $grid->column('amount_type', __('status'))->display(function () {
                return $this->amount < 0 ? __('decrement') : __('increment');
            });
        }
        $grid->column('created_at', __('Created at'));

        // Disable unnecessary buttons
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableActions();

        return $grid;
    }
}
