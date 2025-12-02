<?php

namespace Modules\HostLevel\Http\Controllers\web;

use App\Models\Setting;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Cache;
use App\Admin\Controllers\MainController;
use Encore\Admin\Controllers\AdminController;


class HostLevelSettingController extends MainController
{
    protected $title = '';
    public $permission_name = 'host_level_setting';

    public function index(Content $content)
    {
        $settings = $this->getSettings();
    
        return parent::index($content
            ->title(__('host level Settings'))
            ->body(view('hostlevel::setting', [
                'settings' => $settings,
                'saveUrl'  => $this->saveUrl(),
            ])));
    }
    
    private function saveUrl()
    {
        return admin_url('host-level-settings/save');
    }
    
    private function getSettings()
    {
        $default = [
             'enabled'  => true,
            'type'     => 'daily',
            // 'time'     => '00:00',
            // 'day'      => 0,
            // 'interval' => 1,
        ];
    
        $settings = [];
    
        foreach ($default as $key => $defaultValue) {
            $cacheKey = 'host_level_' . $key;
            $value = Cache::get($cacheKey);
    
            if ($value === null) {
                $setting = Setting::where('key', $cacheKey)->first();
                $value = $setting ? $setting->value : $defaultValue;
    
                Cache::put($cacheKey, $value, now()->addDays(30));
            }
    
            if ($key === 'enabled') {
                $value = (bool) $value;
            } 
            // elseif (in_array($key, ['day', 'interval'])) {
            //     $value = (int) $value;
            // }
    
            $settings[$key] = $value;
        }
    
        return $settings;
    }
    



    public function save()
    {
        $data = [
             'enabled'  => request()->has('enabled'),
            'type'     => request('type', 'daily'),
            // 'time'     => request('time', '00:00'),
            // 'day'      => (int) request('day', 0),
            // 'interval' => (int) request('interval', 1),
        ];
      
    
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => 'host_level_' . $key], 
                ['value' => $value]
            );
    
            Cache::put('host_level_' . $key, $value, now()->addDays(30));
        }
    
        admin_success('تم الحفظ بنجاح ✅');
        return redirect()->back();
    }


   
}
