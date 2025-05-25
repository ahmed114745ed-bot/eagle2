<?php

namespace App\Admin\Controllers;

use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;
use App\Helpers\Common;
use App\Models\Config;
use App\Models\Language;

class AgencySettingsController extends MainController
{

    public $permission_name = 'agency-settings';
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Agency settings';

    public function index(Content $content)
    {
        checkAgencyFeature();

        $hours =  Common::getSettingValue('hours') ?? 0;
        $days =  Common::getSettingValue('days') ?? 0;
        $moments =  Common::getSettingValue('moments') ?? 0;
        $reels = Common::getSettingValue('reels') ?? 0;
        $diamonds = Common::getSettingValue('diamonds') ?? 0;
        $languages = Language::all();
        $configAll = Config::all();
        return parent::index($content
            ->view('agency_settings', compact('hours', 'days', 'moments', 'reels', 'diamonds', 'languages', 'configAll')));
    }

    public function badges()
    {
        $lang = request()->header('X-localization', 'en');

        $types = ['shipping', 'host', 'agency_owner', 'bd'];
        $typeIds = ['shipping' => 3, 'host' => 2, 'agency_owner' => 1, 'bd' => 4]; // example IDs
        $suffixes = ['badge', 'intro', 'frame'];

        $data = [];

        foreach ($types as $type) {
            $images = [];
            foreach ($suffixes as $suffix) {
                $name = $suffix === 'badge'
                    ? $lang . '_' . $type
                    : $lang . '_' . $type . '_' . $suffix;

                $config = Config::where('name', $name)->first();
                $images['image_' . $suffix] = $config?->value ?? null;
            }

            $data[] = array_merge(
                ['type' => $typeIds[$type]],
                $images
            );
        }
        settings()->set('badges-agency', false);
        return response([
            'status' => 'success',
            'data' => $data
        ]);
    }
}
