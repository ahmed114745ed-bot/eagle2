<?php

namespace App\helper;

use App\Models\HomeCarousel;
use App\Models\SuperAdmin;
use Exception;
use App\Helpers\Common;

class SuperAdminHelper
{

    public static function addCoins($userID,  $coins_deducted)
    {
        $user = SuperAdmin::find($userID);
        $user->di -=  $coins_deducted;
        $user->save();

    }
    public static function bannerDeductAmount(HomeCarousel $banner)
    {
        $hourlyPrice = 10; 
    
        $form  = $banner->form;  
        $input = $banner->input; 
    
        switch ($form) {
            case 1:
                $hours = $input;
                break;
            case 2: 
                $hours = $input * 24; 
                break;
            case 3: 
                $hours = $input * 24 * 30; 
                break;
            default:
                $hours = 0;
        }
    
        return $hours * $hourlyPrice;
    }

}

