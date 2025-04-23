<?php

namespace App\Admin\Controllers;

use App\Helpers\Common;
use App\Models\Admin;
use App\Models\Agency;
use App\Models\Country;
use App\Models\Target;
use App\Models\AgencySallary;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Encore\Admin\Facades\Admin as LaravelAdmin;
use function Doctrine\Common\Cache\Psr6\get;
use Encore\Admin\Widgets\InfoBox;
use Encore\Admin\Widgets\Tab;

class  AgencySettingController extends MainController
{

   // public $permission_name = 'agency-setting';
    public $permission_name = 'settings';
    public function index(Content $content)
    {
        $tab = new Tab();
        
        $targets = Target::orderBy('diamonds')->get();

        

        $tab->add(__("targets"), view('admin.targets.targets', ["targets" => $targets]));
        $tab->add('Settings', "هنا هيكون حاجه جميله انتظر");

        return parent::index($content
            ->header('Dynamic Tabs Page')
            ->description('Tabs displaying dynamic data.')
            ->body($tab));
    }
}
