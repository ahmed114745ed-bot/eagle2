<?php

namespace App\Admin\Controllers;

use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;
use App\Models\Config;
use App\Models\Language;
use App\Models\Setting;
use Encore\Admin\Controllers\AdminController;

class AgencySettingsController extends MainController
{

    public $permission_name = 'agency-settings';
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Agency settings';

    public function index(Content $content){
        $hours =  settings()->get('hours');
        $days =  settings()->get('days');
        $moments =  settings()->get('moments');
        $reels = settings()->get('reels');
        $diamonds = settings()->get('diamonds');
        $languages = Language::all();
        $configAll = Config::all();
        return parent::index($content
        ->view('agency_settings',compact('hours', 'days', 'moments', 'reels','diamonds', 'languages', 'configAll')));
    }

    public function badges(){
        $lang = request()->header('X-localization', 'en');
        $host = Config::where('name', $lang . '_'. 'host')->first();
        $shipping = Config::where('name', $lang . '_'. 'shipping')->first();
        $agency_owner = Config::where('name', $lang . '_'. 'agency_owner')->first();

        return response([
            'status' => 'success',
            'data' => [
                [
                    'type' => 3,
                    'image' => $shipping?->value
                ],
                [
                    'type' => 2,
                    'image' => $host?->value
                ],
                [
                    'type' => 1,
                    'image' => $agency_owner?->value
                ]
            ]
        ]);
    }
}
