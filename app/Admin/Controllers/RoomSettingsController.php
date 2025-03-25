<?php

namespace App\Admin\Controllers;

use App\Models\Setting;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Request;

class RoomSettingsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Room-setting';

    public function index(Content $content)
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return $content
            ->header(__('Settings'))
            ->description('')

            ->body(view('admin.room_settings', compact('settings')));
    }

}
