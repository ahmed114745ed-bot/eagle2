<?php

namespace App\Admin\Controllers;

use App\Helpers\Common;
use App\Models\Target;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request as HttpRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\MessageBag;


class TargetController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'target';


    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('targets'))
            ->body($this->grid()));
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(trans('targets'))
            ->body($this->detail($id)));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(trans('targets'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('targets'))
            ->body($this->form()));
    }



    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Target);
        $grid->model()->orderBy('diamonds', 'asc');

        $coins = Common::getMaxCoins();
        // $grid->id(__('ID'));

        $grid->column(__('target no'))->display(function () {
            $TargetCount = Target::where('diamonds', '<', $this->diamonds)->count();

            return  $TargetCount + 1;
        });

        $grid->diamonds(__('diamonds'))
            ->display(function ($value) use ($coins) {
                $endFormatted = $coins ? ($value / $coins) : 0;
                $endFormatted = common::roundToTwoDecimalPlaces($endFormatted);
                return "
                <div style='display: flex; flex-direction: column;'>
                    <span style='font-weight: bold;'>💎 {$value}</span>
                    <span style='color: #888; font-size: smaller;'>\$ {$endFormatted}</span>
                </div>
            ";
            });

        $grid->usd(__('Host Percentage'))
            ->display(function ($value) use ($coins) {
                $endFormatted = $this->diamonds / $coins;

                $endFormatted = is_numeric($endFormatted) ? floatval($endFormatted) : 0;
                $value = is_numeric($value) ? floatval($value) : 0;
                $userUsd = $endFormatted * $value / 100;
                $userUsd = common::roundToTwoDecimalPlaces($userUsd);

                $userPercentage = number_format($value);
                return "
                <div style='display: flex; flex-direction: column;'>
                    <span style='font-weight: bold;'>% {$userPercentage}</span>
                    <span style='color: #888; font-size: smaller;'>\$ {$userUsd}</span>
                </div>
            ";
            });






        // $grid->usd(__('User Percentage'));

        $grid->hours(__('hours'))->editable();
        $grid->days(__('days'))->editable();
        $grid->column(('reel'), __('real'))->display(function ($value) {

            $reel = explode(',', $this->reel);

            $update =  $reel[0] != '' ||  $reel[0] != null ? $reel[0] : 0;
            $like = $reel[1] ?? 0;
            $commit = $reel[2] ?? 0;

            return "<span>" . __('admin.update') . "$update</span>
                    <br>
                    <span>" . __('admin.like') . "$like</span>
                    <br>
                    <span>" . __('admin.comment') . "$commit</span>";
        });
        $grid->column(('moment'), __('Moment'))->display(function ($value) {

            $moment = explode(',', $this->moment);

            $update =  $moment[0] != '' ||  $moment[0] != null ? $moment[0] : 0;
            $like = $moment[1] ?? 0;
            $commit = $moment[2] ?? 0;

            return "<span>" . __('admin.update') . " $update</span>
                    <br>
                    <span>" . __('admin.like') . " $like</span>
                    <br>
                    <span>" . __('admin.comment') . " $commit</span>";
        });

        //        $grid->img('img');
        // $grid->agency_share(__('agency share') . '(%)')->display(function ($column, Grid\Column $value) {
        //     $value = $value->getOriginal();

        //     return number_format($value, 2);
        // });
        $grid->agency_share(__('agency share'))
            ->display(function ($value) use ($coins) {
                $endFormatted = $this->diamonds / $coins;

                $endFormatted = is_numeric($endFormatted) ? floatval($endFormatted) : 0;
                $value = is_numeric($value) ? floatval($value) : 0;
                $userUsd = $endFormatted * $value / 100;
                $userUsd = common::roundToTwoDecimalPlaces($userUsd);

                $userPercentage = number_format($value);
                return "
                <div style='display: flex; flex-direction: column;'>
                    <span style='font-weight: bold;'>% {$userPercentage}</span>
                    <span style='color: #888; font-size: smaller;'>\$ {$userUsd}</span>
                </div>
            ";
            });
        $grid->db_percentage(__('DB  Percentage'))
            ->display(function ($value) use ($coins) {
                $endFormatted = $this->diamonds / $coins;

                $endFormatted = is_numeric($endFormatted) ? floatval($endFormatted) : 0;
                $value = is_numeric($value) ? floatval($value) : 0;
                $userUsd = $endFormatted * $value / 100;
                $userUsd = common::roundToTwoDecimalPlaces($userUsd);

                $userPercentage = number_format($value);
                return "
                <div style='display: flex; flex-direction: column;'>
                    <span style='font-weight: bold;'>% {$userPercentage}</span>
                    <span style='color: #888; font-size: smaller;'>\$ {$userUsd}</span>
                </div>
            ";
            });
        $grid->tools(function (Grid\Tools $tools) {
            $url = '/admin/download-target-pdf';
            $create_new = __('export pdf');
            $button = '<a href="' . $url . '" class="btn btn-sm btn-success" target="_blank">
              <i class="fa fa-download"></i>&nbsp;&nbsp;' . $create_new . '</a>';
            $tools->append($button);
        });
        // $grid->tools(function (Grid\Tools $tools) {
        //     $url = route('download.target.pdf');
        //     $button = <<<HTML
        //         <a href="{$url}" class="btn btn-sm btn-success" target="_blank">
        //             <i class="fa fa-file-pdf-o"></i> {{ __('Export PDF') }}
        //         </a>
        //     HTML;

        //     $tools->append($button);
        // });

        $this->extendGrid($grid);
        $grid->disableExport();
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
        $show = new Show(Target::findOrFail($id));

        $show->id('ID');
        $show->level('target no');
        $show->diamonds('diamonds');
        //        $show->minuts('minuts');
        $show->hours('hours');
        $show->days('days');
        //        $show->img('img');
        //        $show->usd('usd');
        //        $show->coin('coin');
        //        $show->gold('gold');
        //        $show->created_at(trans('admin.created_at'));
        //        $show->updated_at(trans('admin.updated_at'));
        $this->extendShow($show);
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Target);
        $coins = Common::getMaxCoins();



        $form->display(__('ID'));

        $form->hidden('level', __('target no'))->default(function () {
            return Target::max('level') + 1;
        });




        $form->decimal('diamonds', __('diamonds'))
            ->help('
                <span id="total_usd_amount" style="font-weight:bold;color:green">' . __('Total USD: ') . '0.00 USD</span>')
            ->rules('min:0')
            ->default(0)
            ->required();

        $form->decimal('usd', __('Host Percentage') . '(%)')
            ->help('<span id="usd_amount">' . __('Amount will be: ')  . ' USD</span>')
            ->rules('min:0')
            ->default(0)
            ->required();

        $form->decimal('agency_share',  __('agency share') . '(%)')
            ->help('<span id="agency_amount">' . __('Amount will be: ')  . ' USD</span>')
            ->rules('min:0')
            ->default(0)
            ->required();

        $form->decimal('db_percentage',  __('DB  Percentage') . '(%)')
            ->help('<span id="super_admin_amount">' . __('Amount will be: ')  . ' USD</span>')
            ->rules('min:0')
            ->default(0)
            ->required();

        $form->decimal('app_profit_percentage', __('app profit Percentage') . '(%)')
            ->help('<span id="zone_amount">' . __('Amount will be: ')  . ' USD</span>')
            ->rules('min:0')
            ->default(100)
            ->disable();
        $form->html('
        <script>
            $(document).ready(function () {
                var debounceTimer;
                var coins = ' . $coins . ';
        
                function floor2(num) {
                    return Math.floor(num * 100) / 100;
                }
        
                function calculateUsdAmount() {
                    var diamonds = parseFloat($("input[name=\'diamonds\']").val()) || 0;
                    var usd = parseFloat($("input[name=\'usd\']").val()) || 0;
                    var agency = parseFloat($("input[name=\'agency_share\']").val()) || 0;
                    var db = parseFloat($("input[name=\'db_percentage\']").val()) || 0;
                    var app = parseFloat($("input[name=\'app_profit_percentage\']").val()) || 0;
        
                    var totalUsd = diamonds / coins;
                    var userAmount = floor2(totalUsd * usd / 100);
                    var agencyAmount = floor2(totalUsd * agency / 100);
                    var zoneAmount = floor2(totalUsd * app / 100);
                    var superAdminAmount = floor2(totalUsd * db / 100);
        
                    $("#usd_amount").text("' . __('Amount will be: ') . '" + userAmount + " USD");
                    $("#agency_amount").text("' . __('Amount will be: ') . '" + agencyAmount + " USD");
                    $("#zone_amount").text("' . __('Amount will be: ') . '" + zoneAmount + " USD");
                    $("#super_admin_amount").text("' . __('Amount will be: ') . '" + superAdminAmount + " USD");
                    $("#total_usd_amount").text("' . __('Total USD: ') . '" + floor2(totalUsd) + " USD");
                }
        
                function enforceTotalPercentageLimit(changedField) {
                    var fields = ["usd", "agency_share", "db_percentage"];
                    var values = {};
                    var total = 0;
        
                    fields.forEach(function (field) {
                        values[field] = parseFloat($("input[name=\'" + field + "\']").val()) || 0;
                        total += values[field];
                    });
        
                    var remaining = floor2(100 - total);
                    if (remaining < 0) {
                        // لو المجموع أكبر من 100، نقص القيمة المدخلة نفسها
                        var currentValue = values[changedField];
                        var newValue = Math.max(0, currentValue + remaining);
                        $("input[name=\'" + changedField + "\']").val(floor2(newValue));
                        remaining = 0;
                    }
        
                    $("input[name=\'app_profit_percentage\']").val(remaining);
                }
        
                var allFields = ["diamonds", "usd", "agency_share", "app_profit_percentage", "db_percentage"];
                allFields.forEach(function (field) {
                    $(document).on("input", "input[name=\'" + field + "\']", function () {
                        var val = parseFloat($(this).val());
        
                        // منع القيم السالبة
                        if (val < 0) {
                            $(this).val(0);
                            val = 0;
                        }
        
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(function () {
                            if (["usd", "agency_share", "db_percentage"].includes(field)) {
                                enforceTotalPercentageLimit(field);
                            }
                            calculateUsdAmount();
                        }, 500);
                    });
                });
        
                calculateUsdAmount();
            });
        </script>');
        $form->html('<h1>' . __('days and hours') . '</h1>');

        $form->number('hours', __('hours'))->default(function ($form) {
            $hours = $form->model()->hours;
            return $hours == null || $hours == '' ? 0 : $hours;
        });
        $form->number('days', __('days'))->default(function ($form) {
            $days = $form->model()->days;
            return $days == null || $days == '' ? 0 : $days;
        });


        $form->html('<h1>' . __('Reel') . '</h1>');

        $form->hidden('reel', 'reel');
        $form->number('reel1', __('uploadReel'))->default(function ($form) {
            $reel = $form->model()->reel;
            $str    = @explode(',', $reel)[0];
            return $str == null || $str == '' ? 0 : $str;
        });
        $form->number('reel2', __('LikeReel'))->default(function ($form) {
            $reel = $form->model()->reel;

            return @explode(',', $reel)[1] ?? 0;
        });;
        $form->number('reel3', __('commentReel'))->default(function ($form) {
            $reel = $form->model()->reel;

            return @explode(',', $reel)[2] ?? 0;
        });
        $form->html('<h1>' . __('Moment') . '</h1>');
        $form->hidden('moment', 'moment');

        $form->number('moment1', __('uploadMoment'))->default(function ($form) {
            $moment = $form->model()->moment;
            $str    = @explode(',', $moment)[0];
            return $str == null || $str == '' ? 0 : $str;
        });
        $form->number('moment2', __('likeMoment'))->default(function ($form) {
            $moment = $form->model()->moment;

            return @explode(',', $moment)[1] ?? 0;
        });
        $form->number('moment3', __('commentMoment'))->default(function ($form) {
            $moment = $form->model()->moment;

            return @explode(',', $moment)[2] ?? 0;
        });

        $form->saving(function (Form $form) {
            $fields = [
                'usd' => request()->usd,
                'agency_share' => request()->agency_share,
                'app_profit_percentage' => request()->app_profit_percentage,
                'db_percentage' => request()->db_percentage,
            ];

            $total = 0;
            foreach ($fields as $key => $value) {
                if ($value < 0) {
                    $error = new MessageBag(
                        [
                            'title'   => 'forbidden',
                            'message' => __('The field :field must be a positive number.', ['field' => $key]),
                        ]
                    );
                    return back()->with(compact('error'));
                }

                $total += $value;
            }
            if ($total > 100) {
                $error = new MessageBag(
                    [
                        'title'   => 'forbidden',
                        'message' => __('The total percentage must be 100%.'),
                    ]
                );
                return back()->with(compact('error'));
            }
        });

        return $form;
    }

    public function update($id)
    {
        $data   = \request()->all();
        if (isset($data['reel1'])) {
            $reel1  = $data['reel1'];
            $reel2  = $data['reel2'];
            $reel3  = $data['reel3'];
            $values = [
                $reel1,
                $reel2,
                $reel3,
            ];

            $data = array_merge($data, ['reel' => implode(" ,", $values)]);
            unset($data['reel1']);
            unset($data['reel2']);
            unset($data['reel3']);
        }

        if (isset($data['moment1'])) {
            $moment1 = $data['moment1'];
            $moment2 = $data['moment2'];
            $moment3 = $data['moment3'];
            $values2 = [
                $moment1,
                $moment2,
                $moment3,
            ];

            $data = array_merge($data, ['moment' => implode(" ,", $values2)]);
            unset($data['moment1']);
            unset($data['moment2']);
            unset($data['moment3']);
        }



        Request::replace($data);
        return $this->form()->update($id);
    }

    public function store()
    {

        $data = \request()->all();
        $values = [
            $data['reel1'],
            $data['reel2'],
            $data['reel3'],
        ];

        $values2 = [
            $data['moment1'],
            $data['moment2'],
            $data['moment3'],
        ];

        $data = array_merge($data, ['reel' => implode(" ,", $values), 'moment' => implode(" ,", $values2)]);

        unset($data['reel1']);
        unset($data['reel2']);
        unset($data['reel3']);
        unset($data['moment1']);
        unset($data['moment2']);
        unset($data['moment3']);
        Request::replace($data);

        //        Target::create($data);
        return $this->form()->store();
    }


    public function downloadTargetPdf()
    {
        try {
            $targets = Target::orderByDesc('diamonds')->get();
            $pdf = Pdf::loadView('target_pdf', compact('targets'));
            return $pdf->download('target_data_' . now()->format('Y_m_d') . '.pdf');
        } catch (\Exception $e) {

            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }
}
