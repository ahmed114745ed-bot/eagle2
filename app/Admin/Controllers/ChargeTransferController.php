<?php

namespace App\Admin\Controllers;

use App\Models\Setting;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\AdminController;

class ChargeTransferController extends AdminController
{
    public $permission_name = 'settings';

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return $content
            ->title(__('Charge Transfer Settings'))
            ->description(__('Control charging permissions for different transfer scenarios'))
            ->body(view('admin.settings.charge_transfer', compact('settings')));
    }
}