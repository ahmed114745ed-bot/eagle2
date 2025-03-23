<?php

namespace App\Http\Controllers\Api\V1;
use App\Models\Color;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class ColorController extends Controller
{

    public function index()
    {
        $data =[
            'main_color' => Color::where('status',0)->select('id','color')->get(),
            'button_colors' => Color::where('status',1)->select('id','color')->get(),
            'secondary_colors' => Color::where('status',2)->select('id','color')->get(),
        ];
        return Common::apiResponse (true,'',$data,200);
    }
}