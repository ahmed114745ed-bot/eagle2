<?php

namespace App\Http\Controllers\General;

use App\Helpers\Common;
use App\Http\Controllers\Controller;

class DeepLinkController extends Controller
{
    public function index(?string $target = null)
    {
        return view('general.deeplink', [
            'androidLink' => Common::getSettingValue('android_link'),
            'iosLink' => Common::getSettingValue('ios_link'),
            'huaweiLink' => Common::getSettingValue('huawei_link'),
            'appName' => Common::getSettingValue('app_title_en'),
        ]);
    }
}
