<?php

namespace App\Admin\Controllers;

use App\Admin\Widgets\InfoBox;
use Carbon\Carbon;
use App\Models\Charge;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use App\Helpers\Common;
use App\Models\CoinLog;
use Encore\Admin\Admin;
use App\Enums\PaymentType;
use App\Helpers\UserCommon;
use App\Models\ExchangeLog;
use App\Models\PaymentCoin;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;


class ChargeReportController extends MainController
{
    public $permission_name = 'charger-reports';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans("Reports"))
            ->row(function (Row $row) {
                $row->column(12, function (Column $column) {
                    $box = new Box();
                    $box->title(__('Fields'));
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
        if (request("name") == "stripe") {
            $name = "stripe";
        }
        if (request("name") == "in-app-purchas") {
            $name = "in_app_purchas";
        }
        if (request("name") == "exchange") {
            $name = "exchange";
        }


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
        $charger_type = "";
        if (request("name") == "shipping-agency-activity") {
            $charger_type = "shipping-agency-activity";
        } elseif (request("name") == "host") {
            $charger_type = "host";
        } else {
            $charger_type = "dash";
        }

        $grid = new Grid(new Charge());
        $grid->disableRowSelector();
        $grid->model()->orderByDesc('created_at')->with(['sender', 'receiver']);

        if ($charger_type == "dash") {
            // $grid->model()->where('charger_type', "dash")->where('agency_id', '!=', null);
            $grid->model()->where('charger_type', "dash");
        } elseif (request("name") == "host") {

            $grid->model()->where('charger_type', 'host_agency');
        } else {
            $grid->model()->where('charger_type', 'agency')->orWhere('user_type', 'agency');
        }
        if ($charger_type == "shipping-agency-activity") {
            $grid->filter(function (Grid\Filter $filter) {

                if (!request()->has('filter_type')) {
                    request()->merge(['filter_type' => 'shipping']);
                }
                $filter->disableIdFilter();
                $filter->expand();

                $filter->column(1 / 4, function ($filter) {
                    $filter->where(function () {}, __('Type'), 'filter_type')
                        ->select([
                            'user'     => 'User',
                            'shipping' => 'Shipping Agency',
                        ])->default('shipping');
                });

                $filter->column(3 / 4, function ($filter) {
                    $filter->where(function ($query) {
                        $type  = request('filter_type');
                        $value = trim($this->input);

                        if ($type === 'user') {
                            $query->whereHas('senderUser', fn($q) => $q->where('uuid', $value))
                                ->orWhereHas('receiverUser', fn($q) => $q->where('uuid', $value));
                        } elseif ($type === 'shipping') {
                            $query->whereHas('senderShippingAgency', fn($q) => $q->where('id', $value))
                                ->orWhereHas('shippingAgency', fn($q) => $q->where('id', $value));
                        }
                    }, __('Dynamic Sender/Receiver Filter'));
                });

                $filter->column(1 / 4, function ($filter) {
                    $filter->where(function () {}, __('sender type'), 'sender_type')
                        ->select([
                            'user'     => 'User',
                            'shipping' => 'Shipping Agency',
                        ])->default('shipping');
                });

                $filter->column(3 / 4, function ($filter) {
                    $filter->where(function ($query) {
                        $type  = request('sender_type');
                        $value = trim($this->input);

                        if ($type === 'user') {
                            $query->whereHas('senderUser', fn($q) => $q->where('uuid', $value));
                        } elseif ($type === 'shipping') {
                            $query->whereHas('senderShippingAgency', fn($q) => $q->where('id', $value));
                        }
                    }, __('Sender UUID or Shipping Agency ID'));
                });

                $filter->column(1 / 4, function ($filter) {
                    $filter->where(function () {}, __('receiver type'), 'receiver_type')
                        ->select([
                            'user'     => 'User',
                            'shipping' => 'Shipping Agency',
                        ])->default('shipping');
                });

                $filter->column(3 / 4, function ($filter) {
                    $filter->where(function ($query) {
                        $type  = request('receiver_type');
                        $value = trim($this->input);

                        if ($type === 'user') {
                            $query->whereHas('receiverUser', fn($q) => $q->where('uuid', $value));
                        } elseif ($type === 'shipping') {
                            $query->whereHas('shippingAgency', fn($q) => $q->where('id', $value));
                        }
                    }, __('Receiver UUID or Shipping Agency ID'));
                });


                $filter->column(1 / 2, function ($filter) {
                    $filter->where(function ($query) {
                        $from = request('from_date');
                    }, __('From Date'), 'from_date')->date();
                });

                $filter->column(1 / 2, function ($filter) {
                    $filter->where(function ($query) {
                        $to = request('to_date');
                    }, __('To Date'), 'to_date')->date();
                });
            });
        }

        Admin::style("
            @media (min-width: 992px) {
                .ltr label {
                    margin: 0 20px 0 0 !important;
                }
                .col-md-8 {
                    width: auto !important;
                }
            }
        ");

        $grid->model()->when(request('from_date') && request('to_date'), function ($query,) {

            $start = Carbon::parse(convertArabicToEnglishNumbers(request('from_date')))->startOfDay();
            $end   = Carbon::parse(convertArabicToEnglishNumbers(request('to_date')))->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        });
        $grid->column('id', __('transaction id'));
        $grid->column('charger_id', __("sender"))->display(function () use ($charger_type) {

            $sender = Common::getChargerInfo($this);
            if (empty($sender['name']) && empty($sender['uuid'])) {
                return "
                <div style='display: flex; align-items: center; gap: 10px;'>

                            <span style=' cursor: pointer;'>Unknown </span>

                </div>
            ";
            }

            $name = $sender['name'];
            $uuid = $sender['uuid'];
            $path = $sender['image'];
            $showUrl = $sender['url'];
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
            // $image = handleShowImageWithTypes($this->id, $url, 40, 40);


            $imageStyle = $this->charger_type == 'agency'
                ? 'width: 40px; height: 40px; object-fit: cover; border-radius: 0;'     // rectangle
                : 'width: 40px; height: 40px; object-fit: cover; border-radius: 50%;';
            $image = "<img src='{$url}' alt='User Image' style='{$imageStyle}'>";

            return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                        <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                            <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                        </a>
                        <span style='color: #aaa; font-size: smaller;'>UUID: $uuid</span>
                    </div>
                </div>
            ";
        });
        $grid->column('user_id', __('recipient'))->display(function ($recever) {

            // $name =  $this->receiver->name ?? '';
            // $uid = @$this->receiver->uuid ?? 0;
            // $path = @$this->receiver?->profile?->avatar;
            $sender = Common::getReceiverInfo($this);
            if (empty($sender['name']) && empty($sender['uuid'])) {
                return "
                <div style='display: flex; align-items: center; gap: 10px;'>

                            <span style=' cursor: pointer;'>Unknown </span>

                </div>
            ";
            }
            $name = $sender['name'];
            $uid = $sender['uuid'];
            $path = $sender['image'];
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
            //  $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $imageStyle = $this->user_type == 'agency'
                ? 'width: 40px; height: 40px; object-fit: cover; border-radius: 0;'     // rectangle
                : 'width: 40px; height: 40px; object-fit: cover; border-radius: 50%;';
            $image = "<img src='{$url}' alt='User Image' style='{$imageStyle}'>";

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
        if (request("name") == "dash") {
            $grid->column('agency_id', __('Agency'))->display(function () {
                if (!$this->agency) {
                    return "<span style='color: #aaa;'>No Agency</span>";
                }

                $name = $this->agency->name ?? 'Unknown Agency';
                $coins = number_format($this->agency->coins ?? 0);
                $path = $this->agency->img ?? '';
                $defaultImage = asset("images/agency-icon.jpg");
                $url = getImagePath($path) ?? $defaultImage;
                $icon = asset('images/coin.jpg'); // تأكد من وجود الصورة في هذا المسار

                if (!isImageExists($url)) {
                    $url = $defaultImage;
                }

                $image = handleShowImageWithTypes($this->id, $url, 40, 40);

                return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                        <strong>$name</strong><br>
                        <span style='color: green;'> Coins: $coins</span>
                        <img src='{$icon}' alt='Coin' width='20' height='20'>
                    </div>
                </div>
                ";
            });
        }

        if ($charger_type == "dash") {
            // $grid->column('usd', __('amount $'))->display(function ($coin) {
            //     $icon = asset('images/dollar.jpg'); // تأكد من وجود الصورة في هذا المسار
            //     return "
            //         <div style='display: flex; align-items: center; gap: 5px;'>
            //             <span>" . number_format($coin) . "</span>
            //             <img src='{$icon}' alt='Coin' width='20' height='20'>

            //         </div>
            //     ";
            // });

            // $image = asset('images/coin.png');
            // $grid->column('amount', __('coins') . ' ' . "<img src='{$image}' alt='USD' width='20' height='20' style='vertical-align: middle;'> ")
            //     ->display(function ($coin) {
            //         $image = asset('images/coin.png'); // تأكد من أن الصورة موجودة

            //         return "<div style='display: flex; align-items: center; gap: 5px;'>
            //                 <span>{$coin}</span>
            //                 <img src='{$image}' alt='USD' width='20' height='20'>
            //             </div>";
            //     });
        } elseif ((request("name") == "host") || (request("name") == "shipping-agency-activity")) {
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
                $image = asset('images/coin.png'); // تأكد من أن الصورة موجودة

                return "<div style='display: flex; align-items: center; gap: 5px;'>
                        <span>{$coin}</span>
                        <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
            });

        if ($charger_type == "dash") {
            $grid->column('status', __('Status'))->display(function () {
                if ($this->amount > 1) {
                    return '<span style="display:inline-block; padding:5px 10px; font-size:12px; font-weight:bold; border-radius:4px; background-color:#28a745; color:white;">Increment</span>';
                } elseif ($this->amount < 0) {
                    return '<span style="display:inline-block; padding:5px 10px; font-size:12px; font-weight:bold; border-radius:4px; background-color:#dc3545; color:white;">Decrement</span>';
                }
            });
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


        $grid->column('created_at', __('shipping date'));

        return $grid;
    }

    protected function stripe()
    {
        $grid = new Grid(new CoinLog());
        $grid->disableRowSelector();
        $grid->model()->orderByDesc('created_at')->where('method', '!=', 'huawei_pay')->where('method', '!=', 'google_pay')->where('method', '!=', 'apple_pay');
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('user.uuid', __('charger'));

                $filter->where(function ($query) {
                    if ($this->input !== '') {
                        $query->where('method', $this->input);
                    }
                }, __('Select type'), 'method')
                    ->select(
                        ['' => __('All')] +
                            PaymentCoin::orderBy('type')
                            ->pluck('title', 'type')
                            ->toArray()
                    );

                $filter->column(1 / 2, function ($filter) {
                    $filter->where(function ($query) {
                        request('from_date');

                        $start = Carbon::parse(convertArabicToEnglishNumbers(request('from_date')))->startOfDay();
                        $query->whereDate('created_at', '>=', $start);
                    }, __('From Date'), 'from_date')->date();
                });

                $filter->column(1 / 2, function ($filter) {
                    $filter->where(function ($query) {
                        request('to_date');

                        $end   = Carbon::parse(convertArabicToEnglishNumbers(request('to_date')))->endOfDay();
                        $query->whereDate('created_at', '<=', $end);
                    }, __('To Date'), 'to_date')->date();
                });

                $filter->column(1 / 2, function ($filter) {
                    $filter->equal('status', __('Status'))->select([
                        '' => __('All'),
                        1  => __('success'),
                        0  => __('failed'),
                    ]);
                });
            });
        });

        $grid->header(function () {
            $query =  CoinLog::where('method', '!=', 'huawei_pay')->where('method', '!=', 'google_pay')->where('method', '!=', 'apple_pay'); // Get the actual Eloquent builder
            $total = $query->where('status', 1)->sum('paid_usd'); // Execute and cast

            return view('admin.grid.common.report.charge-summary', [
                'total' => $total,
            ])->render();
        });


        $grid->column('id', __('transaction id'));
        $grid->column('user_id', __('charger'))->display(function ($recever) {
            if (!$this->user) {
                return "
                <div style='display: flex; align-items: center; gap: 10px;'>

                            <span style=' cursor: pointer;'>Unknown </span>

                </div>
            ";
            }
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


        $grid->column('coin.usd', __('dollar'))->display(function ($coin) {
            $icon = asset('images/dollar.jpg'); // تأكد من وجود الصورة في هذا المسار
            return "
                <div style='display: flex; align-items: center; gap: 5px;'>
                    <span>" . number_format($coin) . "</span>
                    <img src='{$icon}' alt='Coin' width='20' height='20'>

                </div>
            ";
        });
        $grid->column('obtained_coins', __('Amount'))->display(function ($coin) {
            $icon = asset('images/coin.jpg'); // تأكد من وجود الصورة في هذا المسار
            return "
                <div style='display: flex; align-items: center; gap: 5px;'>
                  <span>" . number_format($coin) . "</span>
                    <img src='{$icon}' alt='Coin' width='20' height='20'>
                </div>
            ";
        });
        $grid->column('trx', __('trx'));
        $grid->column('coin.payment_gateway_id', __('type'))->display(function ($value) {
            $paymentCoin = PaymentCoin::find($value);
            if (!$paymentCoin) return '';
            $options = PaymentType::getTranslatedOptions();

            return $options[$paymentCoin->title] ?? '';
        });
        $grid->column('status', __('Status'))->display(function () {
            if ($this->status == 1) {
                return '<span style="display:inline-block; padding:5px 10px; font-size:12px; font-weight:bold; border-radius:4px; background-color:#28a745; color:white;">Success</span>';
            } elseif ($this->status == 0) {
                return '<span style="display:inline-block; padding:5px 10px; font-size:12px; font-weight:bold; border-radius:4px; background-color:#dc3545; color:white;">Failed</span>';
            }
        });

        $grid->column('created_at', __('shipping date'));
        return $grid;
    }

    protected function in_app_purchas()
    {
        // dd(request('uuid'));

        $grid = new Grid(new CoinLog());
        $grid->disableRowSelector();
        $grid->model()->orderByDesc('created_at')->whereIn('method', ['huawei_pay', 'google_pay', 'apple_pay']);

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('user.uuid', __('charger'));
            });

            $filter->disableIdFilter();
            $filter->where(function ($query) {
                if ($this->input != null) {
                    $query->where('method', $this->input);
                }
            }, __('Select type'), 'name_for_url_shortcut')->radio([
                '' => __('All'),
                'huawei_pay' => __('huawei pay'),
                'google_pay' => __('google pay'),
                'apple_pay' => __('apple pay'),
            ]);
        });

        $grid->quickSearch();
        $grid->column('id', __('id'));
        $grid->column('user_id', __('charger'))->display(function ($recever) {
            if (!$this->user) {
                return "
                <div style='display: flex; align-items: center; gap: 10px;'>

                            <span style=' cursor: pointer;'>Unknown </span>

                </div>
            ";
            }
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
        $grid->column('trx', __('trx'));
        $grid->column('status', __('Status'))->display(function () {
            if ($this->status == 1) {
                return '<span style="display:inline-block; padding:5px 10px; font-size:12px; font-weight:bold; border-radius:4px; background-color:#28a745; color:white;">Success</span>';
            } elseif ($this->status == 0) {
                return '<span style="display:inline-block; padding:5px 10px; font-size:12px; font-weight:bold; border-radius:4px; background-color:#dc3545; color:white;">Failed</span>';
            }
        }); // Allows rendering raw HTML

        $grid->column('method', __('type'))->display(function ($value) {
            $options = [
                'huawei_pay' => __('huawei pay'),
                'google_pay' => __('google pay'),
                'apple_pay' => __('apple pay'),
            ];

            return $options[$value] ?? $value;
        });
        $grid->column('created_at', __('shipping date'));
        // $grid->column('action', __('action'))->display (function (){
        //     return '<a href="?name=in-app-purchas&id='.@$this->id.'" class="btn btn-xs btn-danger">'.__("Return").'</a>';
        // });
        $grid->column('return', __('Return'))->display(function () {
            return (new \App\Admin\Actions\ReturnDiAction($this->id))->render();
        });
        return $grid;
    }

    protected function exchange()
    {


        $grid = new Grid(new ExchangeLog());
        $grid->disableRowSelector();
        $grid->model()->orderByDesc('created_at')->where('status', 1);

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('user.uuid', __('charger'));
            });

            $filter->disableIdFilter();
        });

        $grid->quickSearch();
        $grid->column('id', __('id'));
        $grid->column('user_id', __('charger'))->display(function ($recever) {
            if (!$this->user) {
                return "
                <div style='display: flex; align-items: center; gap: 10px;'>

                            <span style=' cursor: pointer;'>Unknown </span>

                </div>
            ";
            }
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
        $grid->column('diamonds', __('diamonds'))->display(function ($usd) {

            $image = asset('images/diamond.jpg'); // تأكد من أن الصورة موجودة

            return "<div style='display: flex; align-items: center; gap: 5px;'>
                        <span>{$usd}</span>
                        <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        });

        $grid->column('value', __('amount'))->display(function ($coin) {
            $icon = asset('images/coin.jpg'); // تأكد من وجود الصورة في هذا المسار
            return "
                <div style='display: flex; align-items: center; gap: 5px;'>
                    <span>" . number_format($coin) . "</span>
                    <img src='{$icon}' alt='Coin' width='20' height='20'>

                </div>
            ";
        });

        $grid->column('status', __('Status'))->display(function () {
            if ($this->status == 1) {
                return '<span style="display:inline-block; padding:5px 10px; font-size:12px; font-weight:bold; border-radius:4px; background-color:#28a745; color:white;">Success</span>';
            } elseif ($this->status == 0) {
                return '<span style="display:inline-block; padding:5px 10px; font-size:12px; font-weight:bold; border-radius:4px; background-color:#dc3545; color:white;">Failed</span>';
            }
        });
        $grid->column('created_at', __('shipping date'));


        return $grid;
    }

    private function tabsComponent()
    {
        return view('admin.grid.common.report.charge')->render();

        $content = new Row();

        $box = (new Box(
            title: __('Fields'),
            content: view('admin.grid.common.report.charge')
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
        $grid->disableRowSelector();
        $grid->model()->where('agency_id', $agency_id);

        // Add tabs to the header
        $grid->header(function () {
            $scope = request('scope', 'dash');
            return '
    <style>
        .tab-buttons .tab-button {
            color: black !important;
        }
    </style>
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
            $grid->column('admin.name', __('sender'))->display(function () {
                $name = $this->admin->name ?? '';
                $path = $this->admin->avatar ?? null;
                $id =  $this->admin->id ?? 0;
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
                        <div style='display: flex; flex-direction: column;'>
                            <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                            <span style='font-size: 12px; color: #666;'>ID: $id</span>
                        </div>
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


                    $showUrl = $this->agency ? url("admin/agencies/{$this->agency->id}") : '#';
                    $link = $this->agency ? "
                                                <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                                                    <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                                                </a>
                                            " : "<span style='color: gray;'>No Agency</span>";
                    $image = "<img src='{$url}' style='width: 60px; height: 40px; object-fit: cover;'>";
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
                    $showUrl = $this->user ? url("admin/users/{$this->user->id}") : '#';
                    $image = handleShowImageWithTypes($this->id, $url, 40, 40);

                    return "
                        <div style='display: flex; align-items: center; gap: 10px;'>
                            $image
                            <div style='display: flex; flex-direction: column;'>
                                <a href='{$showUrl}' style='text-decoration: none; color: inherit;'>
                                    <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                                </a>
                                <span style='color: #aaa; font-size: smaller;'>UID: $uid</span>
                            </div>
                        </div>
                    ";
                }
            });
        }

        $grid->column('amount', __('coins'))->display(function ($coin) {
            $icon = asset('images/coin.jpg'); // تأكد من وجود الصورة في هذا المسار
            return "
                <div style='display: flex; align-items: center; gap: 5px;'>
                    <span>" . number_format($coin) . "</span>
                    <img src='{$icon}' alt='Coin' width='20' height='20'>

                </div>
            ";
        });
        $grid->column('usd', __('usd'))->display(function ($coin) {

            $icon = asset('images/dollar.jpg');
            return "
                <div style='display: flex; align-items: center; gap: 5px;'>
                    <span>" . $coin . "</span>
                    <img src='{$icon}' alt='Coin' width='20' height='20'>

                </div>
            ";
        });
        if ($scope === 'not_dash') {
            // dd(123);
            $grid->column('amount_type', __('status'))->display(function () use ($agency_id) {
                return $this->user_id == $agency_id  ?  __('increment') : __('decrement');
            });
        } else {
            $grid->column('amount_type', __('status'))->display(function () {
                return $this->amount < 0 ? __('decrement') : __('increment');
            });
        }
        $grid->column('created_at', __('charge date'));

        // Disable unnecessary buttons
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableActions();

        return $grid;
    }
}
