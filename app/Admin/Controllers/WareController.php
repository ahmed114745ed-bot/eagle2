<?php

namespace App\Admin\Controllers;

use App\Models\Ware;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Modules\Public\Http\Services\UserCounterServices;

class WareController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'wares';
    public $hiddenColumns = [];

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Ware);
        $grid->model()->whereNot('get_type', 1);
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('level', __('level'));
            });

            $filter->column(1 / 2, function ($filter) {

                $filter->equal('type', __('type'))->select([
                    1 => trans('Gemstone'),
                    3 => trans('Card Scroll'),
                    4 => trans('Avatar Frame'),
                    5 => trans('Bubble Frame'),
                    6 => trans('Entering Special Effects'),
                    7 => trans('Microphone Aperture'),
                    8 => trans('Badge'),
                    9 => trans('NoKick'),
                    10 => trans('Icon'),
                    11 => trans('intro animation'),
                    12 => trans('wapel'),
                    13 => trans('hide country and last login'),
                    14 => trans('vip gifts'),
                    15 => trans('no pan'),
                    16 => trans('hidden room'),
                    17 => trans('anonymous man'),
                    18 => trans('colored name'),
                    19 => trans('profile visitors hide in'),
                    20 => trans('hide last active')
                ]);
            });
        });

        $grid->id(__('ID'));
        $grid->column('name', __('name'))->editable();
        $grid->column('price', __('price'))->editable();

        $grid->column('show_img', __('show_img'))->image('', 30);
        $grid->column('img2', __('show_img'))->display(function ($path) {
            /** @var Ware $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column('get_type', __('get_type'))->select(
            [
                //  1=>trans ('vip level automatic acquisition'),
                //               2=>trans ('activity'),
                //               3=>trans ('treasure box'),
                4 => trans('purchase'),
                //               5=>trans ('background modification'),
                6 => trans('limited time purchase'),
                //               7=>trans ('treasure box point exchange'),
                //               8=>trans ('cp level unlock'),
            ]
        );
        $grid->column('type', __('type'))->select(
            [
                1 => trans('Gemstone'),
                3 => trans('Card Scroll'),
                4 => trans('Avatar Frame'),
                5 => trans('Bubble Frame'),
                6 => trans('Entering Special Effects'),
                

            ]
        );

        $grid->title(__('title'));
        $states = [
            'on' => ['value' => 1, 'text' => 'open', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => 'close', 'color' => 'default'],
        ];
        //        $grid->score('score');
        $grid->level(__('level'));

        $grid->column('color', __('color'));
        $grid->expire(__('expire'));
        $grid->column('is_active_for_vip', __("active vip"))->switch($states);
        $grid->column('enable', __('enable'))->switch(Common::getSwitchStates());
        $grid->sort(__('sort'), __('sort'));
        $this->extendGrid($grid);
        $grid->disableExport();

        Admin::script("
        if (window.innerWidth >= 1024) { // Example threshold for desktop screens
            $('.table-responsive').removeClass('table-responsive');
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
        $show = new Show(Ware::findOrFail($id));

        //        $show->id('ID');
        //        $show->get_type('get_type');
        //        $show->type('type');
        //        $show->name('name');
        //        $show->title('title');
        //        $show->price('price');
        //        $show->score('score');
        //        $show->level('level');
        //        $show->show_img('show_img');
        //        $show->img1('img1');
        //        $show->img2('img2');
        //        $show->img3('img3');
        //        $show->color('color');
        //        $show->expire('expire');
        //        $show->enable('enable');
        //        $show->sort('sort');
        //        $show->created_at(trans('admin.created_at'));
        //        $show->updated_at(trans('admin.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Ware());

        $form->display('ID');
        $form->select('get_type', trans('get_type'))->options(
            translate(GET_TYPE_WARE)
        )->default(4);
        $form->select('type', trans('type'))->options(
            translate(TYPE_WARE)
        )->rules('required');
        //        ->rules (function ($form){
        //            if (!$id = $form->model()->id) {
        //                return 'required';
        //            }
        //        });
        $form->text('name', trans('name'));
        $form->text('name_en', trans('Name en'));
        $form->text('title', trans('title'));
        $form->text('title_en', trans('Title en'));
        $form->number('price', trans('price'))/*->symbol ('💰')*/;
        //        $form->number('score', trans('score'));
        $form->number('level', trans('level'));
        $states = [
            'on' => ['value' => 1, 'text' => 'open', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => 'close', 'color' => 'default'],
        ];
        $form->switch('is_active_for_vip', __("active vip"))->states($states);
        $form->number('exp', __('exp'));
        $form->image('show_img', trans('img'))->name(function ($file) {
            return now()->timestamp . rand(0, 999) . '.' . $file->guessExtension();
        })->default('1.png');
        //        $form->image('img1', trans('img'));
        $form->file('img2', trans('svg'))->name(function ($file) {
            return now()->timestamp . rand(0, 999) . '.' . $file->guessExtension();
        });
        $form->select('image_type', __('image_type'))->options(
            [
                'svga' => __('svga'),
                'alpha' => __('alpha'),
                'mp4' => __('mp4'),
            ]
        )->required();
        //        $form->file('img3', trans('video'));
        $form->color('color', trans('color'));
        $form->number('expire', trans('expire(in days)'))->placeholder(trans('0 if permanent'));
        $form->switch('enable', trans('enable'))->states(Common::getSwitchStates());
        //        $form->number('sort', 'sort');
        $form->number('num', __('num'));

        $form->saving(function (Form $form) {
            (new UserCounterServices)->eventUsers('ware');
        });


        return $form;
    }
}
