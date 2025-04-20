<?php

namespace App\Admin\Controllers;

use App\Helpers\Common;
use App\Models\Setting;
use App\Models\Target;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use App\Models\Admin as AdminModel;
use App\Services\AppFeatureService;
use Illuminate\Support\Facades\Auth;
use App\Admin\Actions\DenyDeleteAction;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Encore\Admin\Controllers\HasResourceActions;

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
        $grid->model()->orderBy('diamonds');

        $grid->id(__('ID'));
        $grid->level(__('target no'));

        $grid->diamonds(__('diamonds'))->display(function ($column, Grid\Column $value) {
            $value = $value->getOriginal();
            return number_format($value);
        })->editable();
        $grid->usd(__('User Percentage'));

        $grid->hours(__('hours'))->editable();
        $grid->days(__('days'))->editable();
        $grid->column(('reel'), __('real'))->display(function ($value) {

            $reel = explode(',', $this->reel);

            $update =  $reel[0] != '' ||  $reel[0] != null ? $reel[0] : 0;
            $like = $reel[1] ?? 0;
            $commit = $reel[2] ?? 0;

            return "<span style=\"color: var(--inverse-box-color);\"> " .   __('admin.update')  . "$update</span>
            <br>
             <span style=\"color:var(--inverse-box-color) ;\">" .   __('admin.like')  . "$like</span>
             <br>
             <span style=\"color: var(--inverse-box-color) ;\">" .   __('admin.comment')  . "$commit </span>
             ";
        });
        $grid->column(('moment'), __('Moment'))->display(function ($value) {

            $moment = explode(',', $this->moment);

            $update =  $moment[0] != '' ||  $moment[0] != null ? $moment[0] : 0;
            $like = $moment[1] ?? 0;
            $commit = $moment[2] ?? 0;

            return "<span style=\"color: var(--inverse-box-color);\"> " .   __('admin.update')  . "$update</span>
            <br>
             <span style=\"color: var(--inverse-box-color) ;\">" .   __('admin.like')  . "$like </span>
             <br>
             <span style=\"color: var(--inverse-box-color) ;\"> " .   __('admin.comment')  . "$commit </span>
             ";
        });

        //        $grid->img('img');
        $grid->agency_share(__('agency share') . '(%)')->display(function ($column, Grid\Column $value) {
            $value = $value->getOriginal();

            return number_format($value, 2);
        });
        $this->extendGrid($grid);
        $grid->disableExport();
        $grid->actions(function ($actions) {
            $model = $actions->row;
            $admin = Auth::user();
            $created = AdminModel::find($model->created_by);

           // dd( $admin ,$created);
           if ((!$admin->isRole('developer')) && $created && ($created->isRole('developer'))) {
                $actions->disableDelete();
                $actions->add(new DenyDeleteAction());
            }
        });
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
        $form->number('level', __('target no'));
        $form->number('diamonds', __('diamonds'));
        $initialDiamonds = $form->model()->diamonds ?? 0;
        // $initialUsd = $initialDiamonds / $coins;
        $form->decimal('usd', __('Percentage'))
            ->help('<span id="usd_amount">' . __('Amount will be: ')  .' USD</span>');
        $form->decimal('agency_share',  __('agency share') . '(%)')
            ->help('<span id="agency_amount">' . __('Amount will be: ')  .' USD</span>');


        $form->decimal('zone_percentage', __('app profit Percentage'))
            ->help('<span id="zone_amount">' . __('Amount will be: ')  .' USD</span>');
        
        $form->decimal('super_admin_percentage',  __('DB  Percentage') . '(%)')
            ->help('<span id="super_admin_amount">' . __('Amount will be: ')  .' USD</span>');
        
        $form->html('
        <script>
            $(document).ready(function () {
                var coins = ' . $coins . ';
        
                function calculateUsdAmount() {
                    var diamonds = parseFloat($("input[name=\'diamonds\']").val()) || 0;
                    var usd = parseFloat($("input[name=\'usd\']").val()) || 0;
                    var totalUsd = diamonds / coins;
        
                    var userAmount = (totalUsd * usd / 100).toFixed(2);
                    var agencyAmount = (totalUsd * (parseFloat($("input[name=\'agency_share\']").val()) || 0) / 100).toFixed(2);
                    var zoneAmount = (totalUsd * (parseFloat($("input[name=\'zone_percentage\']").val()) || 0) / 100).toFixed(2);
                    var superAdminAmount = (totalUsd * (parseFloat($("input[name=\'super_admin_percentage\']").val()) || 0) / 100).toFixed(2);
        
                    $("#usd_amount").text("' . __('Amount will be: ') . '" + userAmount + " USD");
                    $("#agency_amount").text("' . __('Amount will be: ') . '" + agencyAmount + " USD");
                    $("#zone_amount").text("' . __('Zone amount will be: ') . '" + zoneAmount + " USD");
                    $("#super_admin_amount").text("' . __('Super Admin amount will be: ') . '" + superAdminAmount + " USD");
                }
        
                function enforceTotalPercentageLimit(changedField) {
                    var fields = ["usd", "agency_share", "zone_percentage", "super_admin_percentage"];
                    var values = {};
                    var total = 0;
        
                    fields.forEach(function (field) {
                        values[field] = parseFloat($("input[name=\'" + field + "\']").val()) || 0;
                        total += values[field];
                    });
        
                    if (total > 100) {
                        var excess = total - 100;
                        var currentValue = values[changedField];
                        var newValue = Math.max(0, currentValue - excess);
                        $("input[name=\'" + changedField + "\']").val(newValue.toFixed(2));
                    }
                }
        
                ["usd", "agency_share", "zone_percentage", "super_admin_percentage"].forEach(function (field) {
                    $("input[name=\'" + field + "\']").on("input", function () {
                        enforceTotalPercentageLimit(field);
                        calculateUsdAmount();
                    });
                });
        
                $("input[name=\'diamonds\']").on("input", function () {
                    calculateUsdAmount();
                });
            });
        </script>');
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


        if ($form->isCreating()) {
            $form->model()->created_by = auth()->id();
        }
        $form->model()->updated_by = auth()->id();

        return $form;
    }

    public function update($id)
    {
        $banner = Target::find($id);
        $admin = Auth::user();
        $created = AdminModel::find($banner->created_by);
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


        if ((!$admin->isRole('developer')) && $created && ($created->isRole('developer'))) {
            admin_info(trans('messages.denyDelete'));
            return redirect()->route('admin.targets.index');
        } else {
            Request::replace($data);
            return $this->form()->update($id);
        }
        
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
}
