<?php

namespace Utd\CP\Http\Controllers\web;

use App\Models\Setting;
use Illuminate\Http\Request;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Admin\Controllers\MainController;

class CpSettingsController extends MainController
{
    public $permission_name = 'cp-setting';
    public function index(Content $content)
    {
        $enableGifts = (bool)$this->getSetting('cp_enable_all_gifts', 0);

        return $content
            ->title(__('CP Settings'))
            ->body(view('cp::settings.index', compact('enableGifts')));
    }


    public function updateCp(Request $request)
    {
        $value = $request->cp_enable_all_gifts == 'false' ? 0 : 1;

        $this->setSetting('cp_enable_all_gifts', $value);

        admin_success(__("Saved successfully ✅"));
        return redirect()->back();
    }


    private function getSetting($key, $default = null)
    {
        return  getCpGiftsStatus('cp_enable_all_gifts') ?? 1;
    }

    private function setSetting($key, $value)
    {

        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::put($key, $value);
    }
}
