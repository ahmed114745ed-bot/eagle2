<?php

namespace App\Admin\Controllers;

use App\Models\Setting;
use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;
use App\Models\Config;
use App\Models\Language;

class FeatureAppController extends MainController
{

    public $permission_name = 'app-feature';
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App Feature';

    public function index(Content $content){
        $hostAgencySetting = Setting::where('key', 'host_agency')->first();
        $reelSettings = Setting::where('key', 'reel_status')->first();
        $hostAgencyStatus = ($hostAgencySetting && $hostAgencySetting->value == 1);
        $reelSettings = ($reelSettings && $reelSettings->value == 1);
        return parent::index($content
        ->view('app_feature',compact(['hostAgencyStatus', 'reelSettings'])));
    }
}
