<?php

namespace App\Admin\Controllers;

use App\Models\Config;
use App\Models\Setting;
use App\Models\Language;
use Illuminate\Http\Request;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Log;
use App\Admin\Controllers\MainController;

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

        $setting =   Setting::where('key', $keys)->first();
        if ($setting) {
            $setting->value = $request->is_active;
            $setting->save();
        } else {
            Setting::create([
                'key' => $keys,
                'value' => $request->is_active,
            ]);
        }
    }
}
