<?php

namespace Modules\RoomCup\Http\Controllers\web;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Storage;

class RoomCupSettingsController extends AdminController
{
    protected $title = 'Room Cup Settings';

    public function index(Content $content)
    {
        $settings = $this->getSettings();

        return $content
            ->title('Room Cup Settings')
            ->body($this->form()->fill($settings));
    }

    protected function form()
    {
        $form = new Form(new \stdClass());

        $form->switch('enabled', 'Enable Room Cup Feature')->default(1);
        $form->number('interval_minutes', 'Interval (minutes)')
            ->min(1)->default(60);

        $form->setAction(admin_url('room-cup-settings/save'));

        return $form;
    }

    public function save()
    {
        $data = request()->only(['enabled', 'interval_minutes']);

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
