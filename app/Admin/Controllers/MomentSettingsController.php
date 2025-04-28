<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;

class MomentSettingsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Moment Settings';
    public $permission_name = 'moment-settings';

    public function index(Content $content)
    {
        return parent::index($content
        ->title(__('Moment settings'))
        ->view('moment_settings'));
    }

}
