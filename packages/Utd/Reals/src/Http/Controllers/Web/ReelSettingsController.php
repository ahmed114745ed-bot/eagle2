<?php

namespace Utd\Reals\Http\Controllers\Web;

use App\Admin\Controllers\MainController;
use Encore\Admin\Layout\Content;

class ReelSettingsController extends MainController
{
    public $permission_name = 'reel-settings';

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Reel Settings';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(__('reel settings'))
            ->view('reel_settings'));
    }
}
