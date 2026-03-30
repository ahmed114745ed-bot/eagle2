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

        $settingKeys = [
            'wealth_gift_price',
            'attraction_gift_price',
            'charge_gift_price',
            'rooms_gift_price',
            'cp_gift_price',
        ];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $settingKeys)) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            } else {
                Config::updateOrCreate(['name' => $key], ['value' => $value]);
            }
            // Clear old cache first, then set new value
            Cache::forget($key);
            Cache::put($key, $value, now()->addYear());

            if (in_array($key, $Keys)) {
                Cache::forget('exp_percentages');
            }
        }
        
        // Clear all cache including rememberForever keys
        Cache::forget('all_configs');
        Cache::flush();

        // Re-populate exp_percentages cache after flush
        $collection = Common::getConfFromKey($Keys);
        $values = [];
        foreach ($Keys as $key) {
            $config = $collection->where('name', $key)->first();
            $values[$key] = $config ? $config->value : 1;
        }
        Cache::put('exp_percentages', $values, now()->addMinutes(60));
        $redirectUrl = url(config('admin.route.prefix') . '/settings');
        if ($request->has('current_tab')) {
            $redirectUrl .= '?tab=' . $request->current_tab;
            if ($request->has('inner_tab_type')) {
                $redirectUrl .= '&type=' . $request->inner_tab_type;
            }
        }
        
        admin_toastr(__('Settings updated successfully!'), 'success');
        return redirect($redirectUrl);
    }

    public function exchange(Request $request)
    {
        $data = $request->except('_token', 'test_calco', 'current_tab', 'inner_tab_type');
        
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            // Clear old cache first
            Cache::forget($key);
            Cache::put($key, $value, now()->addYear());
        }
        
        // Clear all cache including rememberForever keys
        Cache::forget('all_configs');
        Cache::flush();

        // Re-populate exp_percentages cache after flush
        $expKeys = [
            'exp_sender_percentage',
            'exp_received_percentage',
            'exp_cp_percentage',
            'exp_room_percentage',
            'exp_charge_percentage'
        ];
        $collection = Common::getConfFromKey($expKeys);
        $expValues = [];
        foreach ($expKeys as $expKey) {
            $conf = $collection->where('name', $expKey)->first();
            $expValues[$expKey] = $conf ? $conf->value : 1;
        }
        Cache::put('exp_percentages', $expValues, now()->addMinutes(60));
        
        $redirectUrl = url(config('admin.route.prefix') . '/settings');
        if ($request->has('current_tab')) {
            $redirectUrl .= '?tab=' . $request->current_tab;
            if ($request->has('inner_tab_type')) {
                $redirectUrl .= '&type=' . $request->inner_tab_type;
            }
        }
        
        admin_toastr(__('Settings updated successfully!'), 'success');
        return redirect($redirectUrl);
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
