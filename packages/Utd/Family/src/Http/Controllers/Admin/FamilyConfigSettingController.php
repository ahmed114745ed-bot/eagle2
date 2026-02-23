<?php

namespace Utd\Family\Http\Controllers\Admin;

use Encore\Admin\Layout\Content;

class FamilyConfigSettingController extends MainController
{
    public $permission_name = 'updates_family-config';

    public function index(Content $content)
    {

        $config = family_model('config')::where('name', 'family_price')->first();
        $configValue = $config->value ?? '';

        return parent::index($content
            ->view('family::familySetting', compact('config', 'configValue')));
    }
}
