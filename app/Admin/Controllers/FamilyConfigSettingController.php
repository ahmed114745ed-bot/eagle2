<?php

namespace App\Admin\Controllers;

use App\Models\Config;
use Illuminate\Support\HtmlString;

use Encore\Admin\Layout\Content;

class FamilyConfigSettingController extends MainController
{
    public $permission_name = 'updates_family-config';


    
    public function index(Content $content)
    {
        $route = 'admin.update-config-group-chat';

        $config = Config::where('name', 'family_price')->first();
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
        $form .= '<label for="android_min_version" class="control-label">' . __('admin.price') . ' :</label>';
        $form .= '<input type="number" id="android_min_version" name="value" placeholder="android_min_version" value="' . $configValue . '" min="1"  class="inputs_cus_form">';

        $form .= '<div style="display: flex; flex-direction: row;">';

        $form .= '<div style="display: flex; justify-content: flex-end; width: 70%;"> 
        <button type="submit" class="button_form_cus">' . __('admin.submit') . '</button>
      </div>';

        $form .= '</form>';



        return parent::index($content
            ->title(trans('Settings'))
            ->body(new HtmlString($form)));
    }
}
