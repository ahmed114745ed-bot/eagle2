<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class ReelSettingsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Reel Settings';
    public $permission_name = 'reel-settings';

    public function index(Content $content)
    {
        return parent::index($content
        ->title(__('reel settings'))
        ->view('reel_settings'));
    }
}
