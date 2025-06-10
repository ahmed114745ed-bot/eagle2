<?php

namespace App\Admin\Controllers;

use App\Helpers\Common;
use App\Models\Config;
use App\Models\Language;
use App\Models\PaymentGateway;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Support\HtmlString;
use Encore\Admin\Form;

use Request;

class MangerSettingController extends MainController
{
    public $permission_name = 'updates_group_chat';
    public $permission_setting = 'agency-manger-setting';



    public function index1(Content $content)
    {
        $route = 'admin.update-config-group-chat';

        $config = Config::where('name', 'system_default_manger')->first();
        $configValue =  $config->value;
        $form = '<form method="POST" action="' . route($route) . '"  >';
        $form .= csrf_field();
        $form .= '<style>
        .switch {
          position: relative;
          display: inline-block;
          width: 60px;
          height: 34px;
        }

        .switch input {
          opacity: 0;
          width: 0;
          height: 0;
        }

        .slider {
          position: absolute;
          cursor: pointer;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background-color: #ccc;
          -webkit-transition: .4s;
          transition: .4s;
        }

        .slider:before {
          position: absolute;
          content: "";
          height: 26px;
          width: 26px;
          left: 4px;
          bottom: 4px;
          background-color: white;
          -webkit-transition: .4s;
          transition: .4s;
        }

        input:checked + .slider {
          background-color: #2196F3;
        }

        input:focus + .slider {
          box-shadow: 0 0 1px #2196F3;
        }

        input:checked + .slider:before {
          -webkit-transform: translateX(26px);
          -ms-transform: translateX(26px);
          transform: translateX(26px);
        }

        /* Rounded sliders */
        .slider.round {
          border-radius: 34px;
        }

        .slider.round:before {
          border-radius: 50%;
        }
        </style>';
        $form .= '<input type="hidden" name="id" value="' . ($config->id ?? '') . '">';
        $form .= '<label for="android_min_version" class="control-label">' . __('admin.value') . ' :</label>';
        $form .= '<input type="text" id="android_min_version" name="value" placeholder="android_min_version" value="' . $configValue . '" min="1"  class="inputs_cus_form">';

        $form .= '<div style="display: flex; flex-direction: row;">';

        $form .= '<div style="display: flex; justify-content: flex-end; width: 70%;">
        <button type="submit" class="button_form_cus">' . __('admin.submit') . '</button>
      </div>';

        $form .= '</form>';



        return parent::index($content
            ->title(trans('Settings'))
            ->body(new HtmlString($form)));
    }



    public function index(Content $content)
    {
        if (!Admin::user()->can('*')) {
            Permission::check('browse-' . $this->permission_setting);
        }

        $config = Config::where('name', 'system_default_manger')->first();
        $configAll = Config::all();
        $languages = Language::all();
        $configValue = $config->value ?? '';

        $payment_gateways = PaymentGateway::all();
        return  $content
            ->title(title: trans('Payment Gateways'))
            ->view('mangerSetting', compact('config', 'configValue', 'languages', 'configAll', 'payment_gateways'));
    }

    public function deletePaymentGateway($id)
    {
        $result = PaymentGateway::findOrFail($id);
        $result->delete();
        return back();
    }
    public function editPaymentGateway($id,Content $content)
    {
        $gateway = PaymentGateway::findOrFail($id);

        return parent::edit($id,$content
        ->body($this->editForm()->edit($id)));
        // view('paymentGatewayEdit', compact('gateway'));
    }
    
    protected function editForm()
    {
        $form = new Form(new PaymentGateway);
        // $form->setMethod('PUT');
        $form->setAction(route('admin.update-payment-gateway', ['id' => request()->route('id')]));
        
        $form->display(__('ID'));
        $form->text('title', __('title'));
        $form->image('photo', trans('image'))->name(function ($file) {
            return now()->timestamp . rand(0, 999) . '.' . $file->guessExtension();
        });
    
        return $form;
    }

    public function updatePaymentGateway($id)
    {
        $gateway = PaymentGateway::findOrFail($id);

        $title = request('title');

        if (request()->hasFile('photo')) {
            $uploaded = Common::upload('images', request('photo'));
            $gateway->update([
                'photo' => $uploaded
            ]);
        }
        $gateway->update([
            'title' => $title
        ]);

        return redirect('/admin/agency-setting-manger');
    }

    public function createPaymentGateway(Content $content){
        return $content
        ->title(title: trans('Payment Gateways'))
        ->body($this->form());
        // ->view('paymentGatewayCreate');
    }
    protected function form()
    {
        $form = new Form(new PaymentGateway);
        $form->setAction(route('admin.store-payment-gateway'));

        $form->display(__('ID'));
        $form->text('title', __('title'));
        $form->image('photo', trans('image'))->name(function ($file) {
            return now()->timestamp . rand(0, 999) . '.' . $file->guessExtension();
        });
    
        return $form;
    }

    public function storePaymentGateway(){

        $title = request('title');
        $photo = Common::upload('images', request('photo'));

        PaymentGateway::create([
            'title' => $title,
            'photo' => $photo
        ]);

        return redirect('/admin/agency-setting-manger');
    }

 
}
