<?php

namespace App\Admin\Controllers;

use Exception;
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
    public $permission_name = 'vips';
    public $hiddenColumns = [

    ];
    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable("vips");
    }

    public function index(Content $content)
    {
        return $content
            ->title(__($this->title))
            ->row(function (Row $row) {
                $row->column(12, $this->grid2());
            })
            ->row(function ($row) {
                $row->column(12, $this->grid());
            });
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
        $grid->column('level',__ ('level'));
        $grid->column('name',__ ('name'));
        $grid->column('img',__ ('img'))->image ('',30);
        $grid->column('price',__ ('price'));
        $grid->column('expire',__ ('expire'));
//        $grid->created_at(trans('admin.created_at'));
//        $grid->updated_at(trans('admin.updated_at'));
        $this->extendGrid ($grid);
        $grid->disableExport();
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
        $this->extendShow ($show);
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
        $form->display(__ ('ID'));
        $form->hidden('level', __('level'));
        $form->text('name', __('name'));
        $form->file('img', __('img'));
        $form->number('exp', __('exp'));
        $form->currency('price', __('price'));
        if (Admin::user()->can('*')) {
            $form->number('expire', __('expire'));
        } else {
            $form->number('expire', __('expire'))->max(30);
        }
        $form->belongsToMany ('privilegs', Privileges::class, __ ('privileges'));

        $form->saving(function (Form $form) {

            $privilegs = request()->all();
            $privilegs = request('privilegs');
            $level = request('level');
            $notActuveAll =Ware::where('level', $level)->update([
                'is_active_for_vip'=>false
            ]);

            if ($notActuveAll) {

                foreach ($privilegs as $privileg){
                    $type_preveleg = VipPrivilege::find($privileg);


                    if(isset($type_preveleg->type)){

                    $updateActive = Ware::where('type', $type_preveleg->type)->where('level',$level)->update([
                        'is_active_for_vip'=>true
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

//        $form->display(trans('admin.created_at'));
//        $form->display(trans('admin.updated_at'));






        return $form;
    }
}
