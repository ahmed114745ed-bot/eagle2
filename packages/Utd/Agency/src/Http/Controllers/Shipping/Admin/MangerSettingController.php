<?php

namespace Utd\Agency\Http\Controllers\Shipping\Admin;

use App\Admin\Controllers\MainController;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Layout\Content;
use Illuminate\Support\HtmlString;
use Utd\Agency\Traits\ResolvesExternalDependencies;

class MangerSettingController extends MainController
{
    use ResolvesExternalDependencies;

    public $permission_name = 'updates_group_chat';

    public $permission_setting = 'agency-manger-setting';

    public function index1(Content $content)
    {
        $route = 'admin.update-config-group-chat';

        $config = Config::where('name', 'system_default_manger')->first();
        $configValue = $config->value;
        $form = '<form method="POST" action="'.route($route).'"  >';
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
        $form .= '<input type="hidden" name="id" value="'.($config->id ?? '').'">';
        $form .= '<label for="android_min_version" class="control-label">'.__('admin.value').' :</label>';
        $form .= '<input type="text" id="android_min_version" name="value" placeholder="android_min_version" value="'.$configValue.'" min="1"  class="inputs_cus_form">';

        $form .= '<div style="display: flex; flex-direction: row;">';

        $form .= '<div style="display: flex; justify-content: flex-end; width: 70%;">
        <button type="submit" class="button_form_cus">'.__('admin.submit').'</button>
      </div>';

        $form .= '</div>';
        $form .= '</form>';

        return $content->title(__('Agency Manger Setting'))
            ->body($form);
    }

    public function index(Content $content)
    {
        Permission::check($this->permission_setting);

        $form = $this->buildForm();

        return $content->title(__('Agency Manger Setting'))
            ->body(new HtmlString($form));
    }

    public function save()
    {
        $languages = request('languages', []);
        Language::query()->update(['status' => 0]);
        Language::whereIn('id', $languages)->update(['status' => 1]);

        $gateways = request('gateways', []);
        PaymentGateway::query()->update(['status' => 0]);
        PaymentGateway::whereIn('id', $gateways)->update(['status' => 1]);

        admin_toastr(__('admin.save_succeeded'));

        return redirect()->back();
    }

    protected function buildForm(): string
    {
        $languages = Language::all();
        $gateways = PaymentGateway::all();

        $form = '<form method="POST" action="'.route('admin.agency-manger-setting.save').'">';
        $form .= csrf_field();
        $form .= $this->buildLanguageSection($languages);
        $form .= $this->buildGatewaySection($gateways);
        $form .= '<button type="submit" class="btn btn-primary">'.__('admin.submit').'</button>';
        $form .= '</form>';

        return $form;
    }

    protected function buildLanguageSection($languages): string
    {
        $html = '<div class="form-group"><label>'.__('Languages').'</label><div class="row">';

        foreach ($languages as $lang) {
            $checked = $lang->status ? 'checked' : '';
            $html .= '<div class="col-md-3"><label><input type="checkbox" name="languages[]" value="'.$lang->id.'" '.$checked.'> '.$lang->name.'</label></div>';
        }

        $html .= '</div></div>';

        return $html;
    }

    protected function buildGatewaySection($gateways): string
    {
        $html = '<div class="form-group"><label>'.__('Payment Gateways').'</label><div class="row">';

        foreach ($gateways as $gateway) {
            $checked = $gateway->status ? 'checked' : '';
            $html .= '<div class="col-md-3"><label><input type="checkbox" name="gateways[]" value="'.$gateway->id.'" '.$checked.'> '.$gateway->title.'</label></div>';
        }

        $html .= '</div></div>';

        return $html;
    }
}
