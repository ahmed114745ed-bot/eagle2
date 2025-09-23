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

        $csrf = csrf_token();
        $checked = $settings['enabled'] ? 'checked' : '';
        $interval = $settings['interval_minutes'];
        $statusText = $settings['enabled'] ? 'ON' : 'OFF';

   
        

        return $content
        ->title(__('Room Cup Settings'))
        ->body(view('roomcup::room_cup_settings', compact('settings')));    }

    private function saveUrl()
    {
        return admin_url('room-cup-settings/save');
    }

    public function save()
    {
        $data = [
            'enabled' => request()->has('enabled') ? true : false,
            'interval_minutes' => (int) request('interval_minutes', 60)
        ];

        Storage::disk('local')->put('roomcup_settings.json', json_encode($data, JSON_PRETTY_PRINT));

        admin_success('تم الحفظ بنجاح ✅');
        return redirect()->back();
    }

    private function getSettings()
    {
        if (!Storage::disk('local')->exists('roomcup_settings.json')) {
            $default = ['enabled' => true, 'interval_minutes' => 60];
            Storage::disk('local')->put('roomcup_settings.json', json_encode($default, JSON_PRETTY_PRINT));
            return $default;
        }

        return json_decode(Storage::disk('local')->get('roomcup_settings.json'), true);
    }
   
}
