<?php

namespace App\Admin\Controllers;

use App\Models\Page;
use App\Http\Controllers\Controller;
use App\Models\SalaryTrx;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class SallariesHistoryController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header(trans('admin.index'))
            ->description(trans('admin.description'))
            ->body($this->grid());
    }


    protected function grid()
    {
        $grid = new Grid(new SalaryTrx);
        $grid->model()->where("type",\request("type"));
        $grid->id(__('Id'));
        if(\request("type") == 0){
            $grid->column ('user.uuid',__ ('uuid'));
        }else{
            $grid->column ('agency.owner_id',__ ('uuid') . __("owner_room_id"));
        }
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
        });
        $grid->type()->display(function ($type) {
            if($type == 0){
                return "User";
            }else{
                return "Agency";
            }
        });
        $grid->amount()->display(function ($num) {
            if($num > 0){
                return "<span class='text-primary '>$num</span>";
            }else{
                $num *= -1;
                return "<span class='text-danger '>$num</span>";
            }
        });
        $grid->disableExport();
        $grid->disableCreateButton();


        return $grid;
    }


}
