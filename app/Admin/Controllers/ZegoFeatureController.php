<?php

namespace App\Admin\Controllers;

use App\Events\ZegoFeatureEvent;
use App\Models\Setting;
use Illuminate\Http\Request;
use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;
use Cache;

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


    public function zegoKey(Request $request)
    {
        $keys = 'zego_feature';
        $setting = Setting::where('key', $keys)->first();
        if ($setting) {
            if ($request->is_active == 1){
                $zegoFeature = [
                    'zego_feature' => (bool)0,
                ];
                event(new ZegoFeatureEvent($zegoFeature));
            }
            $setting->value = $request->is_active;
            $setting->save();

        } else {
            Setting::create([
                'key' => $keys,
                'value' => $request->is_active,
            ]);
        }
        Cache::put('zego_feature', $request->is_active);
    }
}
