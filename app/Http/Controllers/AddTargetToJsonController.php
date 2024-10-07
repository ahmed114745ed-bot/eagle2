<?php

namespace App\Http\Controllers;

use App\Helpers\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class AddTargetToJsonController extends Controller
{
    public function targetPercentage(Request $request)
    {

        $hours =  $request->hours;
        $days =  $request->days;
        $reels =  $request->reels;
        $moments =  $request->moments;

        $total = $hours + $days + $reels + $moments;
        if ($total != 50) {

            return   Redirect::back()->withErrors(['msg' => 'يجب المجموع يكون 50']);
        }
        settings()->set("hours", $hours);
        settings()->set("days", $days);
        settings()->set("reels", $reels);
        settings()->set("moments", $moments);

        return Redirect::back();
    }

    public function create(Request $request)
    {
        $hours =  $request->hours;
        $days =  $request->days;
        $reels =  $request->reels;
        $moments =  $request->moments;

        $total = $hours + $days + $reels + $moments;
        if ($total != 50) {
            return Common::apiResponse(0, 'يجب المجموع يكون 50');
        }
        settings()->set("hours", $hours);
        settings()->set("days", $days);
        settings()->set("reels", $reels);
        settings()->set("moments", $moments);
        return Common::apiResponse(1, 'created successfully');
    }

    public function show()
    {
        $hours =  settings()->get('hours');
        $days =  settings()->get('days');
        $moments =  settings()->get('moments');
        $reels = settings()->get('reels');
        $data = [
            'hours'  => $hours,
            'days' => $days,
            'reels' => $reels,
            'moments' => $moments,
        ];
        return Common::apiResponse(1, '', $data);
    }
}
