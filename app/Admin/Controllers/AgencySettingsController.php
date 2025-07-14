<?php

namespace App\Admin\Controllers;

use Encore\Admin\Auth\Permission;
use Encore\Admin\Facades\Admin;
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
        if (!Admin::user()->can('*')) {
            Permission::check('browse-' . $this->permission_name);
        }

        checkAgencyFeature();
        // $hours =  settings()->get('hours');
        // $days =  settings()->get('days');
        // $moments =  settings()->get('moments');
        // $reels = settings()->get('reels');
        // $diamonds = settings()->get('diamonds');

        $hours =  Common::getSettingValue('hours') ?? 0;
        $days =  Common::getSettingValue('days') ?? 0;
        $moments =  Common::getSettingValue('moments') ?? 0;
        $reels = Common::getSettingValue('reels') ?? 0;
        $diamonds = Common::getSettingValue('diamonds');
        $hoursDays = Common::getSettingValue('hours_days');
        $transfer_salary = settings()->get('transfer_salary');
        $stop_invite_code = settings()->get('stop_invite_code');
        $stop_charge = settings()->get('stop_charge');
        $make_rooms_top = settings()->get('make_rooms_top');
        $make_gift_top = settings()->get('close_open_gifts');
        $languages = Language::all();
        $configAll = Config::all();

        $vars = compact(
            'hours', 'days', 'moments', 'reels', 'diamonds', 'transfer_salary',
            'stop_invite_code', 'stop_charge', 'make_rooms_top', 'make_gift_top', 'languages', 'configAll',
            'hoursDays'
        );

        $targetGrid = app(TargetController::class)
            ->gridInstance();

        $targetGrid->resource('targets');

        $targetGridHtml = $targetGrid->render();

           return parent::index(
               $content->title(__('Agency settings'))
                   ->view('agency_settings', array_merge($vars, [
                       'targetGrid' => $targetGridHtml
                   ]))
           );
    }

    public function badges()
    {
        $lang = request()->header('X-localization', 'en');

        $types = [
            'shipping'     => 3,
            'host'         => 2,
            'agency_owner' => 1,
            'bd'           => 4,
        ];

        $suffixes = ['badge', 'intro', 'frame'];
        $configNames = [];

        // Collect all config names needed (localized and English fallback)
        foreach ($types as $type => $id) {
            foreach ($suffixes as $suffix) {
                $localizedName = $suffix === 'badge' ? "{$lang}_{$type}" : "{$lang}_{$type}_{$suffix}";
                $englishName   = $suffix === 'badge' ? "en_{$type}"   : "en_{$type}_{$suffix}";

                $configNames[] = $localizedName;
                $configNames[] = $englishName;
            }
        }

        // Fetch all needed configs in a single query
        $configs = Config::whereIn('name', $configNames)->pluck('value', 'name');

        // Build the final response data
        $data = [];
        foreach ($types as $type => $id) {
            $images = [];

            foreach ($suffixes as $suffix) {
                $localizedName = $suffix === 'badge' ? "{$lang}_{$type}" : "{$lang}_{$type}_{$suffix}";
                $englishName   = $suffix === 'badge' ? "en_{$type}"       : "en_{$type}_{$suffix}";

                $images["image_{$suffix}"] = $configs[$localizedName] ?? $configs[$englishName] ?? null;
            }

            $data[] = array_merge(['type' => $id], $images);
        }

        return response([
            'status' => 'success',
            'data' => $data,
        ]);
    }

}
