<?php

namespace App\Admin\Controllers;

use App\Models\Setting;
use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;
use App\Models\Config;
use App\Models\Language;

class ZegoFeatureController extends MainController
{

    public $permission_name = 'zego-feature';
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Zego Feature';

    public function index(Content $content)
    {
        $keys = ['zego_feature'];

        $settings = Setting::whereIn('key', $keys)
            ->pluck('value', 'key')
            ->map(fn($value) => $value == 1);

        return parent::index(
            $content->view('zego_feature', [
                'zegoFeature' => $settings['zego_feature'] ?? 1,
            ])
        );
    }
}
