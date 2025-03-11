<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;

class AgencySettingsController extends AdminController
{
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

        return $content
        ->view('agency_settings',compact('hours', 'days', 'moments', 'reels'));
    }


}
