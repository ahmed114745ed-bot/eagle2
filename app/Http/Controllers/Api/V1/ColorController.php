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
            'image1'=>Cache::get('image1') ??'',
            'image2'=>Cache::get('image2') ??'',
            'image3'=>Cache::get('image3') ??'',
        ];
        settings()->set('colors_updated_at', false);
        return Common::apiResponse (true,'',$data,200);
    }
}