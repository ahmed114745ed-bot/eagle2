<?php

namespace Modules\CP\Http\Controllers\web;

use App\Models\Setting;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CpSettingsController
{
    public function index(Content $content)
    {
        $enableGifts = $this->getSetting('cp_enable_all_gifts', 0);

        return $content
            ->title(__('CP Settings'))
            ->body(view('cp::settings.index', compact('enableGifts')));
    }

   
    public function update(Request $request)
    {
        $value = (int)$request->cp_enable_all_gifts;

        $this->setSetting('cp_enable_all_gifts', $value);

        return response()->json(['success' => true]);
    }


private function getSetting($key, $default = null)
{
   return  getCpGiftsStatus('cp_enable_all_gifts') ?? 1 ;
   
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
