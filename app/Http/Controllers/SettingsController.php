<?php

namespace App\Http\Controllers;

use Cache;
use App\Helpers\Common;
use App\Models\Setting;
use App\Models\Timezone;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\NotificationTranslation;

class SettingsController extends Controller
{
    public function index()
    {
        $timezones = Timezone::all();
        $settings = Setting::all();

        return view('admin.settings', compact('timezones', 'settings'));
    }


    public function update(Request $request)
    {


        $data = $request->except('_token');
        unset($data['background_type']);
        foreach ($data as $key => $value) {
            if ($request->hasFile($key)) {
                $image = Common::upload('images', $request->file($key));
                $value = $image;
            }
          
            
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            Cache::put($key, $value);
        }

        admin_toastr('تم تحديث الإعدادات بنجاح!', 'success');

        return back();
    }

    public function store_notification_templates(Request $request)
    {

        $validated = $request->validate([
            'key' => 'required|unique:notifications,key',
        ]);

        $template = Notification::create(['key' => $validated['key']]);

        $languages = ['ar', 'en', 'tr', 'hi'];

        foreach ($languages as $code) {
            if ($request->has("title_{$code}") && $request->has("message_{$code}")) {
                NotificationTranslation::updateOrCreate(

                    [
                        'notification_id' => $template->id,
                        'language' => $code
                    ],
                    [
                        'title' => $request->input("title_{$code}"),
                        'message' => $request->input("message_{$code}"),
                    ]

                );
            }
        }



        admin_toastr('تم تحديث الإعدادات بنجاح!', 'success');

        return back();
    }

    public function edit_notification_templates(Request $request)
    {

        $template = Notification::findOrFail($request->id);

        $languages = ['ar', 'en', 'tr', 'hi'];

        foreach ($languages as $code) {
            if ($request->filled("title_{$code}") && $request->filled("message_{$code}")) {
                NotificationTranslation::updateOrCreate(
                    [
                        'notification_id' => $template->id,
                        'language' => $code
                    ],
                    [
                        'title'   => $request->input("title_{$code}"),
                        'message' => $request->input("message_{$code}"),
                    ]
                );
            }
        }

        admin_toastr('تم تحديث الإعدادات بنجاح!', 'success');

        return back();
    }
}
