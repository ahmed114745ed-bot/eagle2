<?php

namespace App\Admin\Controllers;

use App\Models\PaymentMethodHistory;
use App\Services\FawryPaymentService;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class PaymentMethodController extends AdminController
{
    protected function grid()
    {
        \Admin::js('js/admin/fawry.js');
        $grid = new Grid(new PaymentMethodHistory());
        $grid->model()->orderByDesc('id');

        if (!request()->has('status')) {
            $grid->model()->where('status', 'paid');
        }
        $grid->filter(function($filter) {
            $filter->expand();
            $filter->equal('status')->select(['pending' => 'pending', 'paid' => 'paid','error' => 'error'])->default("paid");
        });

        $grid->column('id', __('Id'));
        $grid->column('amount', __('amount'));
        $grid->column('payment_method', __('payment method'));
        $grid->column('status', __('status'));
        $grid->disableActions();

        return $grid;


    }


    protected function detail($id)
    {
        $show = new Show(PaymentMethodHistory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name_ar', __('Name ar'));
        $show->field('name_en', __('Name en'));
        $show->field('route', __('URL'));

        return $show;
    }

    public function create(Content $content)
    {

        \Admin::js('js/admin/fawry.js');
         $content
            ->header(trans('admin.create'))
            ->description(trans('admin.description'))
            ->view("admin.views.payment");


        return $content;
    }

    public function customStore(Request $request)
    {
        $trx = PaymentMethodHistory::create([
            "amount" => $request->amount,
            "type" => $request->type,
        ]);

        $trxId = $trx->id;

        $fawryService = new FawryPaymentService();
        $exterData = ["type"=>$request->type,'paymentType' => "revenue"];

        //  get url
        $paymentUrl = $fawryService->makePayment($trxId, $request->amount,$exterData);

        if(isset($response['status']) && $paymentUrl['status']  == 0){
            return $paymentUrl;
        }

        return response()->json($paymentUrl, 200);
    }

}
