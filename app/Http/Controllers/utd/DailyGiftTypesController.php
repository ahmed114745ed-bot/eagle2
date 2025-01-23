<?php

namespace App\Http\Controllers\utd;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\DailyPrize\Entities\DailyGiftType;

class DailyGiftTypesController extends Controller
{
    public function index(){
        $search = request('search');

        $dailyPrices = DailyGiftType::when($search,function($q)use($search){
            $q->where('id', $search);
        })->paginate(10);

        return Common::apiResponse(true, 'Success', $dailyPrices);
    }
}
