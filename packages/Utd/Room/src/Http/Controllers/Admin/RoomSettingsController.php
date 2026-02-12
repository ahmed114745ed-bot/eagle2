<?php

namespace Utd\Room\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Config;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoomSettingsController extends Controller
{
    public $permission_name = 'room-settings';

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Room-setting';

    public function index(Content $content)
    {
        if (! Admin::user()->can('*')) {
            Permission::check('browse-'.$this->permission_name);
        }
        $settings = Config::pluck('value', 'name')->toArray();

        return $content
            ->header(__('Settings'))
            ->description('')
            ->body(view('admin.room_settings', compact('settings')));
    }

    public function store(Request $request): RedirectResponse
    {
        if (! Admin::user()->can('*')) {
            Permission::check('edit-'.$this->permission_name);
        }
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            Config::updateOrCreate(['name' => $key], ['value' => $value]);
        }

        admin_toastr('تم تحديث الإعدادات بنجاح!', 'success');

        return back();
    }
}
