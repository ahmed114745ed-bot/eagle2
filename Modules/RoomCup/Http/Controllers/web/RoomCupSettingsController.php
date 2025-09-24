<?php

namespace Modules\RoomCup\Http\Controllers\web;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Storage;

class RoomCupSettingsController extends AdminController
{
    protected $title = '';

    public function index(Content $content)
    {
        $settings = $this->getSettings();
    
        return $content
            ->title(__('Room Cup Settings'))
            ->body(view('roomcup::room_cup_settings', [
                'settings' => $settings,
                'saveUrl'  => $this->saveUrl(),
            ]));
    }
    
    private function saveUrl()
    {
        return admin_url('room-cup-settings/save');
    }
    
    private function getSettings()
    {
        $default = [
            'enabled'          => true,
            'interval_minutes' => 60,
            'type'             => 'daily', 
        ];
    
        if (!Storage::disk('local')->exists('roomcup_settings.json')) {
            Storage::disk('local')->put('roomcup_settings.json', json_encode($default, JSON_PRETTY_PRINT));
            return $default;
        }
    
        $settings = json_decode(Storage::disk('local')->get('roomcup_settings.json'), true);
    
        return array_merge($default, $settings);
    }
    



    public function save()
    {
        $data = [
            'enabled'   => request()->has('enabled'),
            'type'      => request('type', 'daily'),
            'time'      => request('time', '23:59'),
            'day'       => (int) request('day', 0),
            'interval'  => (int) request('interval', 1),
        ];
    
        Storage::disk('local')->put('roomcup_settings.json', json_encode($data, JSON_PRETTY_PRINT));
    
        \Cache::put('roomcup:reward_schedule', $data, now()->addDays(30));
    
        admin_success('تم الحفظ بنجاح ✅');
        return redirect()->back();
    }


   
}
