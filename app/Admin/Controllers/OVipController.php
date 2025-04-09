<?php

namespace App\Admin\Controllers;

use App\Models\Config;
use App\Models\OVip;
use App\Models\Ware;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\VipPrivilege;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use App\Selectables\Privileges;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Services\AppFeatureService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Encore\Admin\Controllers\HasResourceActions;

class OVipController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'ovip';


    public $hiddenColumns = [];
    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable("vips");
    }

    public function vip_settings(Content $content){
        $config = Config::pluck('value', 'name')->toArray();
        return $content->view('vip_settings', compact('config'));
    }

    public function index(Content $content)
    {
        return $content
            ->title(__('vip'))
            ->row(function ($row) {
                $row->column(12, $this->grid());
            });
    }

    public function show($id, Content $content)
    {
        $oVip = OVip::findOrFail($id);
        return $content->title(__('OVip'))->view('ovip_profile', compact('oVip'));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->title(trans('vip'))
            ->body($this->form()->edit($id));
    }

    public function create(Content $content)
    {
        return $content
            ->title(trans('vip'))
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid2()
    {
        $form = new Box();
        $form->view('admin.grid.common.ovip');

        return $form;
    }


    protected function grid()
    {
        //        $arr = [];
        //        $privs = VipPrivilege::all ();
        //        foreach ($privs as $priv){
        //            $arr[$priv->id]=$priv->name;
        //        }
        $grid = new Grid(new OVip);

        $grid->id('ID');
        $grid->column('level', __('level'));
        $grid->column('name', __('name'));
        $grid->column('img', __('img'))->display(function ($path) {
            /** @var OVip $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column('price', __('price'))->display(function ($coin) {
            $icon = asset('images/coin.jpg'); // تأكد من وجود الصورة في هذا المسار
            return "
                <div style='display: flex; align-items: center; gap: 5px;'>
                    <span>" . number_format($coin) . "</span>
                    <img src='{$icon}' alt='Coin' width='20' height='20'>

                </div>
            ";
        });
        $grid->column('expire', __('expire'));
        $grid->column(__('gifts'))->display(function () {
            // توليد الروابط
            $url1 = url('admin/ovip-gift/' . $this->id);

            $button1 = "<a href='{$url1}' class='btn btn-sm btn-info'>" . __('gifts') . "</a>";
            return $button1;
        });
        $this->extendGrid($grid);
        $grid->disableExport();
        Admin::script("
        if (window.innerWidth >= 1024) { // Example threshold for desktop screens
            $('.table-responsive').removeClass('table-responsive');
            }
        ");
        Admin::js('https://cdn.jsdelivr.net/npm/svgaplayerweb@2.3.1/build/svga.min.js');

        Admin::script("
            if (typeof SVGAPlayer !== 'undefined') {
                console.log('✅ SVGA script loaded successfully!');
            } else {
                console.log('❌ Failed to load SVGA script.');
            }
        ");
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(OVip::findOrFail($id));

        //        $show->id('ID');
        //        $show->level('level');
        //        $show->name('name');
        //        $show->img('img');
        //        $show->price('price');
        //        $show->privileges('privileges');
        //        $show->created_at(trans('admin.created_at'));
        //        $show->updated_at(trans('admin.updated_at'));
        $this->extendShow($show);
        return $show;
    }



    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        //        $arr = [];
        //        $privs = VipPrivilege::all ();
        //        foreach ($privs as $priv){
        //            $arr[$priv->id]=$priv->name;
        //        }

        $form = new Form(new OVip);
        if (Session::has('show_alert_vip')) {
            $form->html('<script>
             $(document).ready(function () {
                 alert("___");
             });
         </script>');
        }
        $form->display(__('ID'));
        $form->number('level', __('level'))->creationRules(['required', "unique:o_vips,level,{{id}}"])->updateRules(['required', "unique:o_vips,level,{{id}}"]);;
        $form->text('name', __('name'));
        $form->file('img', __('img'));
        $form->number('exp', __('exp'));
        if (!$form->isEditing()) {
            if (Admin::user()->can('add_vip_price') || Admin::user()->can('*')) {
                $form->currency('price', __('price'))->symbol('💎');
            }
        }
        if ($form->isEditing()) {
            if (Admin::user()->can('edit_vip_price') || Admin::user()->can('*')) {
                $form->currency('price', __('price'))->symbol('💎');
            }
        }
        if (Admin::user()->can('*')) {
            $form->number('expire', __('expire'));
        } else {
            $form->number('expire', __('expire'))->max(30);
        }
        $form->belongsToMany('privilegs', Privileges::class, __('privileges'));

        $form->saving(function (Form $form) {

            $privilegs = request()->all();
            $privilegs = request('privilegs');
            $level = request('level');
            $notActuveAll = Ware::where('level', $level)->update([
                'is_active_for_vip' => false
            ]);

            if ($notActuveAll) {

                foreach ($privilegs as $privileg) {
                    $type_preveleg = VipPrivilege::find($privileg);


                    if (isset($type_preveleg->type)) {

                        $updateActive = Ware::where('type', $type_preveleg->type)->where('level', $level)->update([
                            'is_active_for_vip' => true
                        ]);
                        // if (!$updateActive) {
                        //     session()->flash('show_alert_vip', 'Your alert message');
                        //     return redirect()->back();
                        // }

                    }
                }


                // try {
                //     $updateActive = Ware::where('type', $type_preveleg->type)->where('level', $level)->update([
                //         'is_active_for_vip' => true
                //     ]);

                //     if ($updateActive === false) {
                //         throw new Exception("Error occurred during update");
                //     }
                // } catch (Exception $e) {
                //     // Handle the exception here
                //     dd($e->getMessage());
                // }






                session()->forget('show_alert_vip');
            }
        });

        return $form;
    }
}
