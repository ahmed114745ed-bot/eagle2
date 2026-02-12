<?php

namespace Utd\Moments\Http\Controllers\web;

use App\Admin\Controllers\MainController;
use Encore\Admin\Layout\Content;

class MomentSettingsController extends MainController
{
    public $permission_name = 'moment-settings';

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Moment Settings';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(__('Moment settings'))
            ->view('moment_settings'));
    }
}
