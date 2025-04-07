<?php

namespace App\Http\Controllers\Api\V1;
use App\Models\Color;
use App\Http\Controllers\Controller;
use App\Helpers\Common;
use Illuminate\Support\Facades\Cache;

class ColorController extends Controller
{

    public function index()
    {
        $data =[

            'main_color' => Cache::get('app_primary_color', 'Default app_primary_color') ??'',
            'secondary_colors' => Cache::get('app_second_color', 'Default app_second_color')??'',
            'app_background'=>Cache::get('app_background', 'Default app_background') ??'',
            'image1'=>Cache::get('image1', 'Default image1') ??'',
            'image2'=>Cache::get('image2', 'Default image2') ??'',
            'image3'=>Cache::get('image3', 'Default image3') ??'',

            // 'main_color' => Color::where('status',0)->select('id','color')->get(),
            // 'button_colors' => Color::where('status',1)->select('id','color')->get(),
            // 'secondary_colors' => Color::where('status',2)->select('id','color')->get(),
            // 'app_background'=>Cache::get('app_background', 'Default app_background') ??'',
            // 'app_primary_color'=>Cache::get('app_primary_color', 'Default app_primary_color') ??'',
            // 'app_second_color'=>Cache::get('app_second_color', 'Default app_second_color')??'',
        ];
        return Common::apiResponse (true,'',$data,200);
    }
}