<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class AddTargetToJsonController extends Controller
{
    public function targetPercentage(Request $request){
   
        $hours =  $request->hours;
        $days =  $request->days;
        $reels =  $request->reels;
        $moments =  $request->moments;

        $total = $hours + $days + $reels + $moments;
        if($total != 50) 
        {

          return   Redirect::back()->withErrors(['msg' => 'يجب المجموع يكون 50']);
            
        }
        settings()->set( "hours" , $hours  );
        settings()->set( "days" , $days  );
        settings()->set( "reels" , $reels  );
        settings()->set( "moments" , $moments  );

        return Redirect::back();
    }
}