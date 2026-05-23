<?php

namespace App\Admin\Controllers;

use App\Models\CoinLog;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;

class CoinLogReportsController extends AdminController
{

  

    protected function grid()
    {
        $grid = new Grid(new CoinLog());

        $grid->model()->with(['user:id,name', 'shippingAgency:id,name'])->orderBy('id', 'desc');

        $grid->filter(function($filter) {
            $filter->disableIdFilter();
            $filter->equal('user_type', __('type'))->select([
                'user' => __('User'),
                'shipping_agency' => __('Shipping'),
            ]);
        });

        $grid->column('id', 'ID')->sortable();
        $grid->column('user_type', __('Type'))->label([
            'user' => 'success',
            'shipping_agency' => 'primary',
        ]);

        $grid->column('model_id', __('owner'))->display(function ($modelId) {
            if ($this->user_type === 'user') {
                return $this->user->name ?? '-';
            } elseif ($this->user_type === 'shipping_agency') {
                return $this->shippingAgency->name ?? '-';
            }
            return '-';
        });

        $grid->column('obtained_coins', __('obtained coins'))->sortable();
        $grid->column('trx', __('Transaction Ref'));
        $grid->column('method', __('payment method'));
        $grid->column('status', __('status'))->using([
            0 => __('pending'),
            1 => __('completed'),
        ])->label([
            0 => __('warning'),
            1 => __('success'),
        ]);

        $grid->column('created_at', __('Created At'))->date('Y-m-d H:i');

        $grid->disableCreateButton();
        $grid->disableActions();

        return $grid;
    }
}
