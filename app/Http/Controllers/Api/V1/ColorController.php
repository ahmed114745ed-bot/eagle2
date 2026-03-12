<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Color;
use App\Http\Controllers\Controller;
use App\Helpers\Common;
use Illuminate\Support\Facades\Cache;
use App\Models\Setting;

class ColorController extends Controller
{

    public function index()
    {

        $settingBackGroundType  = Common::getSettingValue('background_type');
        $data = [

            'main_color' => Cache::get('app_primary_color') ?? '',
            'secondary_colors' => Cache::get('app_second_color') ?? '',
            'white_color' => Cache::get('app_white_color') ?? '',
            'black_color' => Cache::get('app_black_color') ?? '',
            'grey_color' => Cache::get('app_grey_color') ?? '',
            'yellow_color' => Cache::get('app_yellow_color') ?? '',
            'background_color' => $settingBackGroundType == 'color' ? (Cache::get('background_color') ?? '') : "",
            'background_image' => $settingBackGroundType == 'image' ? (Cache::get('app_background') ?? '') : "",
            'gradient_1' => $settingBackGroundType == 'gradient' ? (Cache::get('gradient_1') ?? '') : "",
            'gradient_2' => $settingBackGroundType == 'gradient' ? (Cache::get('gradient_2') ?? '') : "",
            'gradient_3' => $settingBackGroundType == 'gradient' ? (Cache::get('gradient_3') ?? '') : "",
        ];




        return Common::apiResponse(true, '', $data, 200);
    }


    public function appCollor()
    {

        $settingBackGroundType  = Common::getSettingValue('background_type');
        $data = [
            "primary_color" => Cache::remember('app_primary_color', 3600, function () {
                return Setting::where('key', 'app_primary_color')->value('value');
            }),
            "background" => [
                "value" => $settingBackGroundType == 'color'
                    ? Cache::remember('background_color', 3600, function () {
                        return Setting::where('key', 'background_color')->value('value');
                    })
                    : Cache::remember('app_background', 3600, function () {
                        return Setting::where('key', 'app_background')->value('value');
                    }),
                "type" => $settingBackGroundType
            ],
            "bottom_nav" => [
                "bottom_color" => Cache::remember('bottom_nav_bottom_color', 3600, function () {
                    return Setting::where('key', 'bottom_nav_bottom_color')->value('value');
                }),
                "active_color" => Cache::remember('bottom_nav_active_color', 3600, function () {
                    return Setting::where('key', 'bottom_nav_active_color')->value('value');
                }),
                "inactive_color" => Cache::remember('bottom_nav_inactive_color', 3600, function () {
                    return Setting::where('key', 'bottom_nav_inactive_color')->value('value');
                }),
            ],
            "text_header_color" => Cache::remember('text_header_color', 3600, function () {
                return Setting::where('key', 'text_header_color')->value('value');
            }),
            "button_text_color" => Cache::remember('button_text_color', 3600, function () {
                return Setting::where('key', 'button_text_color')->value('value') ?? '';
            }),

            'body_color' => [
                "dark_mode_color" => Cache::remember('dark_mode_color', 3600, function () {
                    return Setting::where('key', 'dark_mode_color')->value('value');
                }),
                "light_mode_color" => Cache::remember('light_mode_color', 3600, function () {
                    return Setting::where('key', 'light_mode_color')->value('value') ?? '';
                }),
            ],
        ];

        return Common::apiResponse(true, '', $data, 200);
    }
}
