<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\Route;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form as AdminForm;
use Encore\Admin\Layout\Content;

class AppSitiingCOnfigController extends MainController
{
    public $permission_name = 'updates';
    public function index(Content $content)
    {
        // return view('admin/updatePage');
        $route = 'postAddSitin';

        $chat_enable_version=  settings()->get('chat_enable_version');
        $android_min_version=  settings()->get('android_min_version');
        $android_current_version=  settings()->get('android_current_version');
        $android_update_required= settings()->get('android_update_required');


        $ios_min_version=  settings()->get('ios_min_version');
        $ios_current_version=  settings()->get('ios_current_version');
        $ios_update_required= settings()->get('ios_update_required');

        $huawei_min_version=  settings()->get('huawei_min_version');
        $huawei_current_version=  settings()->get('huawei_current_version');
        $huawei_update_required= settings()->get('huawei_update_required');

        $chat_status= settings()->get('chat_status');
        $invitation_code_date= settings()->get('invitation_code_date');
        $show_welcom_enmation= settings()->get('show_welcom_enmation');

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
        // $form .= '<label for="chat_enable_version" class="control-label">Chat enable version:</label>';
        // $form .= '<input type="text" id="chat_enable_version" name="chat_enable_version" placeholder="chat_enable_version" value="' . $chat_enable_version .'"  class="inputs_cus_form">';

        $form .= '<label for="chat_enable_version" style="    margin-top: 36px;" class="control-label">show wellcom animation : </label>';
        $form .= '<label class="switch">
                    <input type="checkbox" name="show_welcom_enmation"'. ($show_welcom_enmation ? "checked" : "").'>
                    <span class="slider round"></span>
                </label><br><br>';

        $form .= '<label for="chat_enable_version" style="    margin-top: 36px;" class="control-label">enable chat : </label>';
        $form .= '<label class="switch">
                    <input type="checkbox" name="chat_status"'. ($chat_status ? "checked" : "").'>
                    <span class="slider round"></span>
                </label><br><br>';

        $form .= '<label for="android_min_version" class="control-label">تاريخ انتهاء كود الدعوه:</label>';
        $form .= '<input type="integer" id="invitation_code_date" name="invitation_code_date" placeholder="القيمه المؤخوذه ب الشهر " value="' . $invitation_code_date .'"  class="inputs_cus_form">';

        $form .= '<h1 class="control-label text-center">Android</h1>';
        $form .= '<label for="android_min_version" class="control-label">Minimum Version:</label>';
        $form .= '<input type="text" id="android_min_version" name="android_min_version" placeholder="android_min_version" value="' . $android_min_version .'"  class="inputs_cus_form">';
        $form .= '<label for="android_current_version" class="control-label">Current Version:</label>';
        $form .= '<input type="text" id="android_current_version" name="android_current_version" placeholder="android_current_version" value="' . $android_current_version .'" class="inputs_cus_form">';
        $form .= '<label for="android_update_required" class="control-label">Update Required:</label>';
        $form .= '<input type="text" id="android_update_required" name="android_update_required" placeholder="android_update_required" value="' . $android_update_required .'" class="inputs_cus_form">';
        $form .= '<div style="display: flex; flex-direction: row;">';

        $form .= '<div style="flex: 1; margin-right: 10px;">';
        $form .= '<h1  class="control-label text-center">Huawei</h1>';
        $form .= '<label for="huawei_min_version" class="control-label">Minimum Version:</label>';
        $form .= '<input type="text" id="huawei_min_version" name="huawei_min_version" placeholder="huawei_min_version" value="' . $huawei_min_version .'"  class="inputs_cus_form">';
        $form .= '<label for="huawei_current_version" class="control-label">Current Version:</label>';
        $form .= '<input type="text" id="huawei_current_version" name="huawei_current_version" placeholder="huawei_current_version"  value="' . $huawei_current_version .'" class="inputs_cus_form">';

        $form .= '<label for="huawei_update_required" class="control-label">Update Required:</label>';
        $form .= '<input type="text" id="huawei_update_required" name="huawei_update_required" placeholder="huawei_update_required" value="' . $huawei_update_required .'" class="inputs_cus_form">';

       
        $form .= '</div>';
        
        $form .= '<div style="flex: 1; margin-left: 10px;">';
        $form .= '<h1 class="control-label text-center">iOS</h1>';
        $form .= '<label for="ios_min_version" class="control-label">Minimum Version:</label>';
        $form .= '<input type="text" id="ios_min_version" name="ios_min_version" placeholder="ios_min_version" value="' . $ios_min_version .'" class="inputs_cus_form">';
        $form .= '<label for="ios_current_version" class="control-label">Current Version:</label>';
        $form .= '<input type="text" id="ios_current_version" name="ios_current_version" placeholder="ios_current_version" value="' . $ios_current_version .'" class="inputs_cus_form">';
        $form .= '<label for="ios_update_required" class="control-label">Update Required:</label>';
        $form .= '<input type="text" id="ios_update_required" name="ios_update_required" placeholder="ios_update_required" value="' . $ios_update_required .'" class="inputs_cus_form">';
        $form .= '</div>'; 
        $form .= '</div>';
       

        $form .= '<button type="submit" class="button_form_cus">Submit</button>';

        $form .= '</form>';


        return $content
            ->header('Custom Page')
            ->description('This is a custom page')
            ->body(new HtmlString($form));
    }

}
