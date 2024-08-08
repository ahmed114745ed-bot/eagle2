<?php

namespace App\Admin\Controllers;


use Encore\Admin\Grid;
use App\Models\Commission;

use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\HasResourceActions;


class CommissionController extends MainController
{
    use HasResourceActions;
    //public $permission_name = 'charge';
    public $hiddenColumns = [];
   

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Commission());

        $grid->filter(function (Grid\Filter $filter) {

            $filter->disableIdFilter();
            $filter->where(function ($query) {
                $datt = \App\Helpers\UserCommon::arabicToEnglishNumbers($this->input);
                $query->whereDate('created_at', '>=', $datt);
            }, __('from_date'), 'from_date')->date();

            $filter->where(function ($query) {
                $datt = \App\Helpers\UserCommon::arabicToEnglishNumbers($this->input);

                $query->whereDate('created_at', '<=', $datt);
            }, __('to_date'), 'to_date')->date();

        });
        $grid->column('amount', __('amount'));
        $grid->column('created_at', __('date'))->display(function () {
            return \Carbon\Carbon::createFromTimestamp(strtotime($this->created_at))
                ->timezone(auth()->user()->time_zone)->format("Y-m-d h:i A");
        });
        return $grid;
       
    }
}