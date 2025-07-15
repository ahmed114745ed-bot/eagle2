<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;

class DeepLinkController extends Controller
{
    public function index(?string $target = null)
    {
        return view('general.deeplink', [
            'androidLink' => 'https://play.google.com/store/apps/details?id=com.yourapp',
            'iosLink' => 'https://apps.apple.com/app/id1234567890',
            'huaweiLink' => 'https://appgallery.huawei.com/#/app/C123456',
        ]);
    }
}
