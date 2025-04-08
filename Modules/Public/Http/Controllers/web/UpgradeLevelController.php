<?php

namespace Modules\Public\Http\Controllers\web;

use App\Models\Config;
use App\Admin\Controllers\MainController;
use App\Helpers\Common;
use App\Services\LevelService;
use Database\Seeders\config as SeedersConfig;
use Illuminate\Http\Request;

class UpgradeLevelController extends MainController
{
    protected $levelService;

    public function __construct(LevelService $levelService)
    {
        $this->levelService = $levelService;
    }

    public function ovipConfig(Request $request)
    {
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            Config::updateOrCreate(['name' => $key], ['value' => $value]);
        }

        return redirect()->back()->with('message', __('dashboard.update'));

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
