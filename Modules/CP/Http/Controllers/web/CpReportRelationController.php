<?php

namespace Modules\CP\Http\Controllers\web;

use App\Models\User;

use Encore\Admin\Grid;
use Modules\CP\Entities\Cp;
use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;

class CpReportRelationController extends MainController
{

    public function index(Content $content)
    {
        return $content
            ->title(trans('reports'))
            ->row(function ($row) {
                $row->column(2, view('admin.grid.common.cp-report-actions'));
                $row->column(10, $this->grid());
            });
    }

    protected function grid()
    {
        $name = request('name') ?: 'relations';


        $grid = $name;
        $grid = $this->{$grid}();
        $grid->disableexport();
        $grid->disableActions();
        $grid->disableCreateButton();
        return $grid;
    }

    protected function relations()
    {
        $grid = new Grid(new Cp());

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column('1/2', function ($filter) {
                $filter->where(function ($query) {
                    $input = $this->input;
                    $query->whereHas('fromUser', function ($query) use ($input) {
                        $query->where('uuid',  $input);
                    })->orWhereHas('toUser', function ($query) use ($input) {
                        $query->where('uuid',  $input);
                    });
                }, __('User'))->placeholder(__('Search by  UUID'));
            });

            $filter->column(1 / 2, function ($filter) {
                $filter->equal('cpRelation.type', __("type"))->select(['friend' => __('friend'), 'bro' => __('bro'), 'lovely' => __('lovely'), 'solution' => __('solution') ]);
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('status', __('status'))->select([0 => __('pending'), 1 => __('active'), 2 => __('accepted'), 3 => __('stope'), 4 => __('restored') ,5 => __('restore'),6 => __('restore_binding')]);
            });


            $filter->column(1 / 2, function ($filter) {
                $filter->equal('level.level', __("level"));
            });
        });

        $grid->column('fromUser.name', __('name'))
            ->display(function ($name) {
                $uid = @$this->fromUser->uuid;
                $path = @$this->fromUser->profile->avatar;
                $url = getImagePath($path);
                $image =  handleShowImageWithTypes($this->id, $url, 40, 40);
                return "$image<br>$name <br>
            <span style=\"color: #aaa; font-size: smaller;\">UID: $uid</span>";
            });
        $grid->column('toUser.name', __('name'))
            ->display(function ($name) {
                $uid = @$this->toUser->uuid;
                $path = @$this->toUser->profile->avatar;
                $url = getImagePath($path);
                $image =  handleShowImageWithTypes($this->id, $url, 40, 40);
                return "$image<br>$name <br>
            <span style=\"color: #aaa; font-size: smaller;\">UID: $uid</span>";
            });
        $grid->column("di", __("di"));
        $grid->column("price", __("price"));
        $grid->column("cpRelation.type", __("cpRelation"))->display(function ($type) {
            return __($type);
        });
        $grid->column("level_id", __("level"));
        $grid->column("status", __("status"))
            ->display(function ($status) {
                switch ($status) {
                    case 0:
                        return "<span style='color: blue;'>منتظر</span>";
                    case 1:
                        return "<span style='color: blue;'>موافق</span>";
                    case 2:
                        return "<span style='color: red;'>مرفوض</span>";
                    case 3:
                        return "<span style='color: red;'>علاقة موقوفة</span>";
                    case 4:
                        return "<span style='color: green;'>عودة</span>";
                    case 5:
                        return "<span style='color: blue;'>منتظر العودة</span>";
                    default:
                        return $status; // Display as it is for other cases
                }
            })->style('font-weight: bold;');
        return $grid;
    }

    protected function user()
    {
        $grid = new Grid(new User());
        $grid->model()->whereHas('cps', function ($query) {
            $query->whereIn('status', [1, 4]);
        });

        $grid->column('name', __('name'))
            ->display(function ($name) {
                $uid = @$this->uuid;
                $path = @$this->profile->avatar;
                $url = getImagePath($path);
                $image =  handleShowImageWithTypes($this->id, $url, 40, 40);
                return "$image<br>$name <br>
            <span style=\"color: #aaa; font-size: smaller;\">UID: $uid</span>";
            });
        $grid->column("count", __("count"))
            ->display(function () {
                return Cp::where(function($q){
                    $q->where('user_one_id', $this->id)->orWhere('user_two_id', $this->id);
                })->whereIn('status',[1,4])
                ->whereHas('cpRelation',function($q){
                    $q->where('type', '!=', 'solution');
                })->count();
                return $this->cps()->whereIn('status', [1, 4])->count();
            });


        return $grid;
    }
}
