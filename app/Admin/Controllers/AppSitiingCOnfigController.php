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
        $route = 'admin.postAddSitin';
        $isRTL = app()->getLocale() === 'ar';

        $buttonAlignStyle = $isRTL ? 'text-align: left;' : 'text-align: right;';

        // Get all settings at once for better performance
        $settings = [
            'chat_enable_version' => settings()->get('chat_enable_version'),
            'android_min_version' => settings()->get('android_min_version'),
            'android_current_version' => settings()->get('android_current_version'),
            'android_update_required' => settings()->get('android_update_required'),
            'ios_min_version' => settings()->get('ios_min_version'),
            'ios_current_version' => settings()->get('ios_current_version'),
            'ios_update_required' => settings()->get('ios_update_required'),
            'huawei_min_version' => settings()->get('huawei_min_version'),
            'huawei_current_version' => settings()->get('huawei_current_version'),
            'huawei_update_required' => settings()->get('huawei_update_required'),
            'chat_status' => settings()->get('chat_status'),
            'invitation_code_date' => settings()->get('invitation_code_date'),
            'show_welcom_enmation' => settings()->get('show_welcom_enmation')
        ];

        $form = '<div class="settings-container">';
        $form .= '<form method="POST" action="' . route($route) . '" class="settings-form">';
        $form .= csrf_field();

        $form .= '<style>
            .settings-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
            }

            .settings-form {
                background: #fff;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }

            .form-group {
                margin-bottom: 20px;
            }

            .form-row {
                display: flex;
                gap: 20px;
                margin-bottom: 30px;
            }

            .form-col {
                flex: 1;
                background: #f9f9f9;
                padding: 20px;
                border-radius: 6px;
            }

            .platform-title {
                text-align: center;
                color: #2c3e50;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 1px solid #eee;
            }

            .control-label {
                display: block;
                margin-bottom: 8px;
                font-weight: 600;
                color: #555;
            }

            .inputs_cus_form {
                width: 100%;
                padding: 10px 15px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 14px;
                transition: border-color 0.3s;
            }

            .inputs_cus_form:focus {
                border-color: #3498db;
                outline: none;
                box-shadow: 0 0 0 2px rgba(52,152,219,0.2);
            }

            .button_form_cus {
                background: #3498db;
                color: white;
                border: none;
                padding: 12px 25px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
                transition: background 0.3s;
                display: block;
                width: 100%;
                max-width: 200px;
                margin: 30px auto 0;
            }

            .button_form_cus:hover {
                background: #2980b9;
            }

            /* Switch styles */
            .switch-container {
                display: flex;
                align-items: center;
                margin-bottom: 20px;
            }

            .switch {
                position: relative;
                display: inline-block;
                width: 60px;
                height: 34px;
                margin-left: 15px;
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
                transition: .4s;
                border-radius: 34px;
            }

            .slider:before {
                position: absolute;
                content: "";
                height: 26px;
                width: 26px;
                left: 4px;
                bottom: 4px;
                background-color: white;
                transition: .4s;
                border-radius: 50%;
            }

            input:checked + .slider {
                background-color: #2196F3;
            }

            input:focus + .slider {
                box-shadow: 0 0 1px #2196F3;
            }

            input:checked + .slider:before {
                transform: translateX(26px);
            }
        </style>';

        // Invitation Code Date
        $form .= '<div class="form-group">';
        $form .= '<label for="invitation_code_date" class="control-label">' . __('admin.invitation_code_date') . '</label>';
        $form .= '<input type="number" id="invitation_code_date" name="invitation_code_date" placeholder="' . __('admin.invitation_code_date_placeholder') . '" value="' . $settings['invitation_code_date'] . '" class="inputs_cus_form">';
        $form .= '</div>';

        // Toggle Switches
        $form .= '<div class="form-row">';
        $form .= '<div class="switch-container">';
        $form .= '<label for="show_welcom_enmation" class="control-label">' . __('admin.show_welcome_animation') . '</label>';
        $form .= '<label class="switch">';
        $form .= '<input type="checkbox" name="show_welcom_enmation"' . ($settings['show_welcom_enmation'] ? "checked" : "") . '>';
        $form .= '<span class="slider"></span>';
        $form .= '</label>';
        $form .= '</div>';

        $form .= '<div class="switch-container">';
        $form .= '<label for="chat_status" class="control-label">' . __('admin.enable_chat') . '</label>';
        $form .= '<label class="switch">';
        $form .= '<input type="checkbox" name="chat_status"' . ($settings['chat_status'] ? "checked" : "") . '>';
        $form .= '<span class="slider"></span>';
        $form .= '</label>';
        $form .= '</div>';
        $form .= '</div>';

        // Platform Settings
        $form .= '<div class="form-row">';

        // Android
        $form .= '<div class="form-col">';
        $form .= '<h2 class="platform-title">' . __('admin.android') . '</h2>';
        $form .= '<div class="form-group">';
        $form .= '<label for="android_min_version" class="control-label">' . __('admin.minimum_version') . '</label>';
        $form .= '<input type="text" id="android_min_version" name="android_min_version" placeholder="android_min_version" value="' . $settings['android_min_version'] . '" class="inputs_cus_form">';
        $form .= '</div>';
        $form .= '<div class="form-group">';
        $form .= '<label for="android_current_version" class="control-label">' . __('admin.current_version') . '</label>';
        $form .= '<input type="text" id="android_current_version" name="android_current_version" placeholder="android_current_version" value="' . $settings['android_current_version'] . '" class="inputs_cus_form">';
        $form .= '</div>';
        $form .= '<div class="form-group">';
        $form .= '<label for="android_update_required" class="control-label">' . __('admin.update_required') . '</label>';
        $form .= '<input type="text" id="android_update_required" name="android_update_required" placeholder="android_update_required" value="' . $settings['android_update_required'] . '" class="inputs_cus_form">';
        $form .= '</div>';
        $form .= '</div>';

        // Huawei
        $form .= '<div class="form-col">';
        $form .= '<h2 class="platform-title">' . __('admin.huawei') . '</h2>';
        $form .= '<div class="form-group">';
        $form .= '<label for="huawei_min_version" class="control-label">' . __('admin.minimum_version') . '</label>';
        $form .= '<input type="text" id="huawei_min_version" name="huawei_min_version" placeholder="huawei_min_version" value="' . $settings['huawei_min_version'] . '" class="inputs_cus_form">';
        $form .= '</div>';
        $form .= '<div class="form-group">';
        $form .= '<label for="huawei_current_version" class="control-label">' . __('admin.current_version') . '</label>';
        $form .= '<input type="text" id="huawei_current_version" name="huawei_current_version" placeholder="huawei_current_version" value="' . $settings['huawei_current_version'] . '" class="inputs_cus_form">';
        $form .= '</div>';
        $form .= '<div class="form-group">';
        $form .= '<label for="huawei_update_required" class="control-label">' . __('admin.update_required') . '</label>';
        $form .= '<input type="text" id="huawei_update_required" name="huawei_update_required" placeholder="huawei_update_required" value="' . $settings['huawei_update_required'] . '" class="inputs_cus_form">';
        $form .= '</div>';
        $form .= '</div>';

        // iOS
        $form .= '<div class="form-col">';
        $form .= '<h2 class="platform-title">' . __('admin.ios') . '</h2>';
        $form .= '<div class="form-group">';
        $form .= '<label for="ios_min_version" class="control-label">' . __('admin.minimum_version') . '</label>';
        $form .= '<input type="text" id="ios_min_version" name="ios_min_version" placeholder="ios_min_version" value="' . $settings['ios_min_version'] . '" class="inputs_cus_form">';
        $form .= '</div>';
        $form .= '<div class="form-group">';
        $form .= '<label for="ios_current_version" class="control-label">' . __('admin.current_version') . '</label>';
        $form .= '<input type="text" id="ios_current_version" name="ios_current_version" placeholder="ios_current_version" value="' . $settings['ios_current_version'] . '" class="inputs_cus_form">';
        $form .= '</div>';
        $form .= '<div class="form-group">';
        $form .= '<label for="ios_update_required" class="control-label">' . __('admin.update_required') . '</label>';
        $form .= '<input type="text" id="ios_update_required" name="ios_update_required" placeholder="ios_update_required" value="' . $settings['ios_update_required'] . '" class="inputs_cus_form">';
        $form .= '</div>';
        $form .= '</div>';

        $form .= '</div>'; // Close form-row
        $form .= '<div class="col-sm-12" style="'.$buttonAlignStyle.'">';
        $form .= '<button type="submit" class="btn btn-primary">' . __('admin.submit') . '</button>';
        $form .= '</div>';

        $form .= '</form>';
        $form .= '</div>';

        return parent::index($content
            ->title(trans('Updates'))
            ->body(new HtmlString($form)));
    }
}
