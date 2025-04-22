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
        $data =[

            'main_color' => Cache::get('app_primary_color') ??'',
            'secondary_colors' => Cache::get('app_second_color')??'',
            'app_background'=>Cache::get('app_background') ??'',
            'image1'=>Cache::get('image1') ??'',
            'image2'=>Cache::get('image2') ??'',
            'image3'=>Cache::get('image3') ??'',



            'app_coin_color' => Cache::get('app_coin_color') ?? '',
            'app_main_color' => Cache::get('app_main_color') ?? '',
            'app_selected_color' => Cache::get('app_selected_color') ?? '',
            'app_unselected_color' => Cache::get('app_unselected_color') ?? '',
            'app_warning_color' => Cache::get('app_warning_color') ?? '',
            'app_charmpink_color' => Cache::get('app_charmpink_color') ?? '',
            'app_heartpink_color' => Cache::get('app_heartpink_color') ?? '',
            'app_date_color' => Cache::get('app_date_color') ??'',
        ];
        settings()->set('colors_updated_at', false);
        return Common::apiResponse (true,'',$data,200);
    }
}