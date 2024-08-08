<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\PaySalariesAction;
use App\Admin\Actions\SalariesAction;
use App\Models\Agency;
use App\Models\SalaryTrx;
use App\Models\User;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Exception;
use Illuminate\Support\Facades\DB;
use function request;

class SallariesController extends MainController
{

    public function index(Content $content)
    {
        return $content->title(trans('Sallaries'))->description(__(request('desc') ?: 'users'))->row(function ($row) {
                $row->column(2, view('admin.grid.common.sallaries'));
                $row->column(10, $this->grid());
            });
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

    public function cashing000()
    {
        $amount = request('amount');
        try {
            DB::beginTransaction();
            if (request('id') && request('type') == 'agency') {
                $agency = Agency::query()->find(request('id'));
                if ($agency) {
                    $prev = $agency->old_usd;
                    if ($amount) {
                        $agency->old_usd -= $amount;
                    } else {
                        $agency->old_usd = 0;
                    }
                    $agency->save();
                    $m = $amount ?: $agency->old_usd;
                    if ($m > 0) {
                        SalaryTrx::query()->create([
                                                       'type'       => 1, 'oid' => $agency->id,
                                                       'amount'     => $amount ?: $agency->old_usd,
                                                       't_no'       => rand(11111111, 99999999),
                                                       'note'       => 'paid via admin', 'before_pay' => $prev,
                                                       'after_pay'  => $prev - $m, 'payer_id' => auth()->id(),
                                                       'payer_type' => 0
                                                   ]);
                    }
                }
            } elseif (request('id') && request('type') == 'user') {
                $user = User::query()->find(request('id'));
                if ($user) {
                    $prev = $user->old_usd;
                    $m    = $amount ?: $user->old_usd;
                    if ($amount) {
                        $user->old_usd -= $amount;
                    } else {
                        $user->old_usd = 0;
                        $user->coins   = 0;
                    }
                    $user->save();
                    if ($m > 0) {
                        SalaryTrx::query()->create([
                                                       'type'       => 0, 'oid' => $user->id,
                                                       'amount'     => $amount ?: $user->old_usd,
                                                       't_no'       => rand(11111111, 99999999),
                                                       'note'       => 'paid via admin', 'before_pay' => $prev,
                                                       'after_pay'  => $prev - $m, 'payer_id' => auth()->id(),
                                                       'payer_type' => 0
                                                   ]);
                    }
                }
            } elseif (request('id') && request('type') == 'agency_users') {
                $agency = Agency::query()->find(request('id'));
                if ($agency) {
                    $prev            = $agency->old_usd;
                    $m               = $agency->old_usd;
                    $agency->old_usd = 0;
                    if ($m > 0) {
                        SalaryTrx::query()->create([
                                                       'type'       => 0, 'oid' => $agency->id,
                                                       'amount'     => $agency->old_usd,
                                                       't_no'       => rand(11111111, 99999999),
                                                       'note'       => 'paid via admin', 'before_pay' => $prev,
                                                       'after_pay'  => $prev - $m, 'payer_id' => auth()->id(),
                                                       'payer_type' => 0
                                                   ]);
                    }
                    $users = $agency->users;
                    foreach ($users as $user) {
                        $m             = $user->old_usd;
                        $user->old_usd = 0;
                        $user->coins   = 0;
                        if ($m > 0) {
                            SalaryTrx::query()->create([
                                                           'type'       => 0, 'oid' => $user->id,
                                                           'amount'     => $user->old_usd,
                                                           't_no'       => rand(11111111, 99999999),
                                                           'note'       => 'paid via admin for agency , contact your agent for your salary',
                                                           'before_pay' => $prev, 'after_pay' => $prev - $m,
                                                           'payer_id'   => auth()->id(), 'payer_type' => 0
                                                       ]);
                        }
                        $user->save();
                    }
                    $agency->save();
                }
            }
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->response()->error(__('un known error'))->refresh();
        }

        return $this->response()->success('success')->refresh();

    }

    protected function users_grid()
    {
        $grid = new Grid(new User());
        $grid->column('id', __('id'));
        $grid->column('agency', __('agency'))->display(function () { return @$this->agency->name; });
        // $grid->column ('uuid',__ ('uuid'));
        $grid->column('name', __('name'));
        $grid->column('old_usd', __('old usd'));
        $grid->column('target_usd', __('target usd'));
        $grid->column('target_token_usd', __('target token usd'));
        $grid->column('due', __('due'))->display(function () {
            return $this->old_usd + $this->target_usd - $this->target_token_usd;
        });
        $grid->column('cashing', __('cashing'))->display(function () {
            $options = ['user' => __('user')];
            return (new SalariesAction($this->id, 'user'))->render();
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

    protected function agencies_grid()
    {
        $grid = new Grid(new Agency());
        $grid->model()->where('target_usd', '>', 0);
        $grid->column('id', __('id'));
        $grid->column('name', __('name'));
        $grid->column('phone', __('phone'));
        $grid->column('old_usd', __('old usd'));
        $grid->column('target_usd', __('target usd'));
        $grid->column('target_token_usd', __('target token usd'));
        $grid->column('due', __('due'))->display(function () {
            return $this->old_usd + $this->target_usd - $this->target_token_usd;
        });
        $grid->column('users', __('users'))->display(function () {
            return '<a href="?name=users&desc=' . $this->name . '&aid=' . $this->id . '">' . $this->users()->count() . '</a>';
        });
        $grid->column('cashing', __('cashing'))->display(function () {
            return (new SalariesAction($this->id, 'agency'))->render();
        });

        return $grid;
    }

    protected function users()
    {
        $grid  = new Grid(new User());
        $model =
            $grid->model()->where('agency_id', '!=', 0)->LeftJoin('user_sallaries', 'users.id', '=', 'user_sallaries.user_id');
        if (request('salary_only') == 1) {
            $model->having('total', '>', 0);
        }
        $model->select('users.id', 'users.name', 'users.uuid', DB::raw('SUM(user_sallaries.sallary - user_sallaries.cut_amount) AS total'))->groupBy('users.id', 'users.name', 'users.uuid')->orderByRaw('total DESC');
        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('uuid', __('uuid'));
            });
        });
        $grid->column('id', __('id'));
        $grid->column('uuid', __('uuid'));
        $grid->column('name', __('name'));
        $grid->column('total', __('salary'))->default(0);
        $grid->column('cashing', __('cashing'))->display(function () {
            return (new SalariesAction($this->id, 'user'))->render();
        });
        $grid->column('pay', __('pay'))->display(function () {
            return (new PaySalariesAction($this->id, 'user',$this->salary))->render();
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append('<a href="' . url('/admin/sallaries_history?type=0') . '"  class="btn btn-sm btn-success">' . __('admin.history') . '</a>');

        });
        return $grid;
    }

    protected function agencies()
    {
        $grid = new Grid(new Agency());

        $model = $grid->model()
                 ->LeftJoin('agency_sallaries', 'agencies.id', '=', 'agency_sallaries.agency_id')
                 ->select('agencies.id', 'agencies.name', DB::raw('SUM(agency_sallaries.sallary - agency_sallaries.cut_amount) AS total'))
//            ->where('agencies.id', request('id'))
                 ->orderByRaw('total desc')
                 ->groupBy('agencies.id', 'agencies.name');

        if (request('salary_only') == 1) {
            $model->having('total', '>', 0);
        }
        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->expand();

            $filter->column(12, function(Grid\Filter $filter) {
                $filter->where( function ($q) {
                    $q->where('agencies.id', '=', $this->input);
                } , 'id');

            });
        });

        $grid->column('id', __('id'));
        $grid->column('name', __('name'));
        $grid->column('total', __('salary'))->default(0);

        $grid->column('cashing', __('cashing'))->display(function () {
            $options = ['agency' => __('agency')];
            return (new SalariesAction($this->id, 'agency'))->render();
        });
        $grid->column('pay', __('pay'))->display(function () {
            return (new PaySalariesAction($this->id, 'agency',$this->salary))->render();
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append('<a href="' . url('/admin/sallaries_history?type=1') . '"  class="btn btn-sm btn-success">' . __('admin.history') . '</a>');
        });
        return $grid;
    }


}
