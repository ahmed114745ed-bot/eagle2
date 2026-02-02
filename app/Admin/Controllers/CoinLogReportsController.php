<?php

namespace App\Admin\Controllers;

use App\Models\CoinLog;
use App\Models\User;
use Utd\Agency\Entities\ShippingAgency;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class CoinLogReportsController extends AdminController
{

  

    protected function grid()
    {
        $grid = new Grid(new CoinLog());

        $grid->model()->orderBy('id', 'desc');

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
                $user = User::find($modelId);
                return $user ? "{$user->name} ({$user->email})" : '-';
            } elseif ($this->user_type === 'shipping_agency') {
                $agency = ShippingAgency::find($modelId);
                return $agency ? "{$agency->name}" : '-';
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
