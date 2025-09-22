<?php

namespace App\Admin\Controllers;

use App\Models\SuperAdmin;
use Illuminate\Http\Request;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Box;

class SuperAdminSelectController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Super Admin';
    public $permission_name = 'superadmin';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('Super Admin'))
            ->body($this->grid2()));
    }

    protected function grid2()
    {
        $box1 = new Box(__('admin.Actions'), view('admin.grid.superadmin.selectPage'));

        return $box1->render() ;
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return \Illuminate\Http\RedirectResponse
     */
     public function makeDefault(Request $request)
    {
        $superAdminId = $request->input('superadmin_id');

        SuperAdmin::query()->update(['default' => false]);

        $superAdmin = SuperAdmin::findOrFail($superAdminId);
        $superAdmin->default = true;
        $superAdmin->save();

        admin_success('Updated', 'Default Super Admin has been set successfully');

        return redirect()->back();
    }

//    public function toggleSalaryTransfer(Request $request)
//    {
//            $enabled = (bool) $request->input('enabled');
//
//            settings()->set("bd_stop_charge", $enabled ? "1" : "0");
//
//            return response()->json([
//                'status'  => 'success',
//                'message' => $enabled
//                    ? __('تم تفعيل تحويل الرواتب بنجاح ✅')
//                    : __('تم إيقاف تحويل الرواتب للجميع 🚫'),
//            ]);
//    }

}
