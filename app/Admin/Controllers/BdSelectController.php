<?php

namespace App\Admin\Controllers;

use App\Models\Bd;
use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\BDSallary;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use App\Admin\Actions\MakeBdDefultAction;
use App\Admin\Controllers\MainController;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use App\Admin\Widgets\InfoBox;




class BdSelectController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'BD';
    public $permission_name = 'BD';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('BD'))
            ->body($this->grid2()));
        
      
    }


  
 

    protected function grid2()
    {
     
        return new Box(
            __('admin.Actions'),
            view('admin.grid.bd.selectPage') 
        );
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
   
     public function makeDefault(Request $request)
{
    $bdId = $request->input('bd_id');

    Bd::query()->update(['default' => false]);

    $bd = Bd::findOrFail($bdId);
    $bd->default = true;
    $bd->save();

    admin_success('تم التحديث', 'تم تعيين BD الافتراضي بنجاح');

    // return redirect()->back();
    return redirect()->route('admin.usersBd.index');

}

}
