<?php

namespace App\Admin\Controllers;

use App\Models\Target;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Tab;

class  AgencySettingController extends MainController
{

   // public $permission_name = 'agency-setting';
    public $permission_name = 'agency-settings';
    public function index(Content $content)
    {
        $tab = new Tab();
        
        $targets = Target::orderBy('diamonds')->get();

        

        $tab->add(__("targets"), view('admin.t  argets.targets', ["targets" => $targets]));
        $tab->add('Settings', "هنا هيكون حاجه جميله انتظر");

        return parent::index($content
            ->header('Dynamic Tabs Page')
            ->description('Tabs displaying dynamic data.')
            ->body($tab));
    }
}
