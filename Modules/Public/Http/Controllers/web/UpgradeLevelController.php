<?php

namespace Modules\Public\Http\Controllers\web;

use App\Models\Config;
use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Services\LevelService;
use Illuminate\Support\Facades\Cache;
use App\Admin\Controllers\MainController;
use App\Models\Setting;
use Database\Seeders\config as SeedersConfig;

class UpgradeLevelController extends MainController
{
    protected $levelService;

    public function __construct(LevelService $levelService)
    {
        $this->levelService = $levelService;
    }

    public function ovipConfig(Request $request)
    {
        $data = $request->except('_token', 'test_calco', 'current_tab', 'inner_tab_type');
        $Keys = [
            'exp_sender_percentage',
            'exp_received_percentage',
            'exp_cp_percentage',
            'exp_room_percentage',
            'exp_charge_percentage'
        ];
        
        foreach ($data as $key => $value) {
            Config::updateOrCreate(['name' => $key], ['value' => $value]);
            Cache::forever($key, $value);
            
            if (in_array($key, $Keys)) {
                Cache::forget('exp_percentages');
            }
        }
        
        // Clear cache to sync with all Octane workers
        \Artisan::call('cache:clear');
        
        // بناء رابط الرجوع مع التاب الصحيح
        $redirectUrl = url(config('admin.route.prefix') . '/settings');
        if ($request->has('current_tab')) {
            $redirectUrl .= '?tab=' . $request->current_tab;
            if ($request->has('inner_tab_type')) {
                $redirectUrl .= '&type=' . $request->inner_tab_type;
            }
        }
        
        return redirect($redirectUrl)->with('message', __('dashboard.update'));
    }

    public function exchange(Request $request)
    {
        $data = $request->except('_token', 'test_calco', 'current_tab', 'inner_tab_type');
        
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            Cache::forever($key, $value);
        }
        
        // Clear cache to sync with all Octane workers
        \Artisan::call('cache:clear');
        
        $redirectUrl = url(config('admin.route.prefix') . '/settings');
        if ($request->has('current_tab')) {
            $redirectUrl .= '?tab=' . $request->current_tab;
            if ($request->has('inner_tab_type')) {
                $redirectUrl .= '&type=' . $request->inner_tab_type;
            }
        }
        
        return redirect($redirectUrl)->with('message', __('dashboard.update'));
    }
    public function group_chat_config(Request $request)
    {
        $conf = Config::where('name','send_world_chat')->first();
        if(!$conf)
        {
            config::create([
                'name'  => 'send_world_chat',
                'value' => $request->number,
            ]);
        }else{
            $conf->value = $request->number;
            $conf->save();
        }

        return redirect()->back()->with('message', __('dashboard.update'));

    }

    public function reelConfig(Request $request)
    {
        $conf = Config::where('name','upload_reel')->first();
        if(!$conf)
        {
            config::create([
                'name'  => 'upload_reel',
                'value' => $request->number,
            ]);
        }else{
            $conf->value = $request->number;
            $conf->save();
        }

        return redirect()->back()->with('message', __('dashboard.update'));

    }

    public function momentConfig(Request $request)
    {
        $conf = Config::where('name','upload_moment')->first();
        if(!$conf)
        {
            config::create([
                'name'  => 'upload_moment',
                'value' => $request->number,
            ]);
        }else{
            $conf->value = $request->number;
            $conf->save();
        }

        return redirect()->back()->with('message', __('dashboard.update'));

    }



    public function getLevelsRange()
    {
        $data = $this->levelService->getAllLevelsRanges();
        return Common::apiResponse(true, 'success', $data);
    }



}
