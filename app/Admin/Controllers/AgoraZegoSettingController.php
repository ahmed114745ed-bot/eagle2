<?php

namespace App\Admin\Controllers;

use App\Helpers\Common;
use Illuminate\Support\HtmlString;
use Encore\Admin\Layout\Content;

class AgoraZegoSettingController extends MainController
{
    public $permission_name = 'agora-zego';

    public function index2(Content $content){
        $agora_app_id = Common::getConfig('app_id');
        $zego_server_secret = Common::getConfig('zego_server_secret');
        $zego_app_id = Common::getConfig('zego_app_id');
        $app_sign = Common::getConfig('app_sign');
        $library = Common::getConfig('library');

        return $content->view('agora_zego_settings',compact('agora_app_id',
         'zego_server_secret', 'zego_app_id','app_sign','library'));
    }

    public function index(Content $content)
    {
        // return view('admin/updatePage');
        $route = 'admin.update-agora-zego';

        $agora_app_id = Common::getConfig('app_id');
        $zego_server_secret = Common::getConfig('zego_server_secret');
        $zego_app_id = Common::getConfig('zego_app_id');
        $app_sign = Common::getConfig('app_sign');
        $library = Common::getConfig('library');


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


        $form .= '<label for="library">' . __('admin.Choose Library') . ':</label>';
        $form .= '<select name="library" id="library" class="inputs_cus_form">
            <option value="0" ' . ($library == "0" ? "selected" : "") . '>' . __('admin.Agora') . '</option>
            <option value="1" ' . ($library == "1" ? "selected" : "") . '>' . __('admin.Zego') . '</option>
          </select>';

        $form .= '<div style="display: flex; flex-direction: row;">';
        $form .= '<div style="flex: 1; margin-right: 10px;">';
        $form .= '<h1  class="control-label text-center">' . __('admin.Agora') . '</h1>';
        $form .= '<label for="ios_min_version" class="control-label">' . __('admin.app_id') . ':</label>';
        $form .= '<input type="text" id="huawei_min_version" name="app_id" placeholder="app_id" value="' . $agora_app_id . '"  class="inputs_cus_form">';
        $form .= '</div>';

        $form .= '<div style="flex: 1; margin-left: 10px;">';
        $form .= '<h1 class="control-label text-center">' . __('admin.Zego') . '</h1>';
        $form .= '<label for="ios_min_version" class="control-label">' . __('admin.server_secret') . ':</label>';
        $form .= '<input type="text" id="ios_min_version" name="zego_server_secret" placeholder="ios_min_version" value="' . $zego_server_secret . '" class="inputs_cus_form">';
        $form .= '<label for="ios_current_version" class="control-label">' . __('admin.app_id') . ':</label>';
        $form .= '<input type="text" id="ios_current_version" name="zego_app_id" placeholder="ios_current_version" value="' . $zego_app_id . '" class="inputs_cus_form">';
        $form .= '<label for="ios_update_required" class="control-label">' . __('admin.app_sign') . ':</label>';
        $form .= '<input type="text" id="ios_update_required" name="app_sign" placeholder="ios_update_required" value="' . $app_sign . '" class="inputs_cus_form">';
        $form .= '</div>';
        $form .= '</div>';

        $form .= '<div style="display: flex; justify-content: flex-end; width: 70%;">
        <button type="submit" class="button_form_cus">' . __('admin.submit') . '</button> </div>';

        $form .= '</form>';



        return parent::index($content
            ->title(trans('Setting'))
            ->body(new HtmlString($form)));
    }
}
