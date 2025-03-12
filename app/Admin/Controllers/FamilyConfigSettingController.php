<?php

namespace App\Admin\Controllers;

use App\Models\Config;
use Encore\Admin\Layout\Content;

class FamilyConfigSettingController extends MainController
{
    public $permission_name = 'updates_family-config';



    public function index(Content $content){

        $config = Config::where('name', 'family_price')->first();
    $configValue = $config->value ?? '';
        return $content
        ->view('familySetting',compact('config', 'configValue'));
    }
    
   
}
