<?php

namespace App\Admin\Controllers;

use App\Models\Ban;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\BanType;
use Encore\Admin\Layout\Content;
use App\Admin\Actions\DeleteBans;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;


class BanController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'bans';

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header(trans('admin.index'))
            ->description(trans('admin.description'))
            ->row(function ($row) {
                $row->column(10, $this->grid());
                $row->column(2, view('admin.grid.users.ban'));
            });
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header(trans('admin.detail'))
            ->description(trans('admin.description'))
            ->body($this->detail($id));
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
            ->header(trans('admin.edit'))
            ->description(trans('admin.description'))
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header(trans('admin.create'))
            ->description(trans('admin.description'))
            ->body($this->form());
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $now = now();
        $grid = new Grid(new Ban);
        $grid->model()->whereHas('user')
            ->whereRaw("DATE_ADD(created_at, INTERVAL duration HOUR) > '$now'")
             ->select('uid', 'duration', 'type', 'device_number', 'staff_id', 'description_ar', 'img', DB::raw('(SELECT created_at FROM bans AS b WHERE b.uid = bans.uid AND b.type = bans.type ORDER BY b.id DESC LIMIT 1) AS created_at'),'ban_type_id')
             ->groupBy(['uid', 'type', 'duration', 'device_number', 'staff_id', 'description_ar', 'img','ban_type_id'])->orderByDesc('created_at');
        //        $grid->id(__ ('ID'));
        $grid->uid(__('uuid'));
        //        $grid->user_type(__('user_type'));
        $grid->duration(__('duration'));
        $grid->type(__('type'));
        $grid->column('description_ar', __('reason'));
        $grid->column('ban_type_id',__ ('ban_type'))->display(function ($row){

            $banType = BanType::find($this->ban_type_id);
            $name_ar =$banType->name_ar ??'';
            $name_en = $banType->name_en?? '';

         return "$name_ar <br>
         <span style=\"color: #aaa; font-size: smaller;\">  $name_en</span>" ?? "";

    });
        $grid->column('img', trans('image'))->image('', 30);

        //        $grid->ip(__ ('ip'));
        $grid->device_number(__('device_number'));
        $grid->staff_id(__('staff_id'));

        $grid->column('created_at', __('created'))->display(function () {
            return \Carbon\Carbon::createFromTimestamp(strtotime($this->created_at))
                                 ->timezone(auth()->user()->time_zone)->format("Y-m-d h:i A");
        });

        $grid->column ('return',__ ('delete'))->display (function (){
            return (new \App\Admin\Actions\DeleteBans($this->uid, $this->type,$this->ban_type_id))->render () ;
        });

        $grid->disableExport();
        $grid->disableRowSelector();
        $grid->disableActions();
        $grid->disableCreateButton();

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('uid', __('uuid'));

            });
        });
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
        //        $show = new Show(Ban::findOrFail($id));
        //
        //        $show->id('ID');
        //        $show->uid('uid');
        //        $show->user_type('user_type');
        //        $show->duration('duration');
        //        $show->type('type');
        //        $show->ip('ip');
        //        $show->device_number('device_number');
        //        $show->staff_id('staff_id');
        //        $show->created_at(trans('admin.created_at'));
        //        $show->updated_at(trans('admin.updated_at'));
        //
        //        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        //        $form = new Form(new Ban);
        //
        //        $form->display('ID');
        //        $form->text('uid', __('uid'));
        ////        $form->text('user_type', __('user_type'));
        //        $form->number('duration', __('duration(hours)'));
        ////        $form->text('type', __('type'));
        //        $form->switch('ban_ip', 'ip_ban')->states ([0=>'off',1=>'on']);
        //        $form->switch('device_ban', __('device_ban'))->states ([0=>'off',1=>'on']);
        //        $form->display(trans('admin.created_at'));
        //
        //
        //        return $form;
    }
}
