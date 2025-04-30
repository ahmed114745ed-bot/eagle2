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
        $data =[

            'main_color' => Cache::get('app_primary_color') ??'',
            'secondary_colors' => Cache::get('app_second_color')??'',
            'white_color' => Cache::get('app_white_color')??'',
            'black_color' => Cache::get('app_black_color')??'',
            'grey_color' => Cache::get('app_grey_color')??'',
            'yellow_color' => Cache::get('app_yellow_color')??'',
            'background_color' => $settingBackGroundType == 'color' ?( Cache::get('background_color') ?? '') :"",
            'background_image' =>$settingBackGroundType == 'image' ?( Cache::get('app_background') ?? '') : "",
            'gradient_1' => $settingBackGroundType == 'gradient' ? (Cache::get('gradient_1') ?? ''):"",
            'gradient_2' => $settingBackGroundType == 'gradient' ? (Cache::get('gradient_2') ?? ''): "",
        ];
        settings()->set('colors_updated_at', false);
        return Common::apiResponse (true,'',$data,200);
    }
}
