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

        $data = $request->only(['timezone']);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            Cache::put($key, $value);
        }

        return back()->with(['success' => 'App settings updated successfully']);
    }
}
