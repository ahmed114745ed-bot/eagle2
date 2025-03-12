<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Timezone;
use Cache;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(){
        $timezones = Timezone::all();
        $settings = Setting::all();

        return view('admin.settings', compact('timezones', 'settings'));
    }


    public function update(Request $request){


        $data = $request->all();
        foreach ($data as $key => $value) {
            if ($request->hasFile($key)) {
                $file = $request->file($key);
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->move('uploads/settings', $fileName);
                $value = $fileName;

            }
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            Cache::put($key, $value);
        }


        admin_toastr('تم تحديث الإعدادات بنجاح!', 'success');

        return back();
        }
}
