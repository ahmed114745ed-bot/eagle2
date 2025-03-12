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

    public function index(Content $content)
    {
        return $content
        ->title(__('Moment settings'))
        ->view('moment_settings');
    }

}
