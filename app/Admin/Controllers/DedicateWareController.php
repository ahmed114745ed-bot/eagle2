<?php

namespace App\Admin\Controllers;

use App\Models\Ware;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use Encore\Admin\Admin;
use Illuminate\Support\Str;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Admin\Actions\DedicateAction;
use App\Admin\Controllers\MainController;
use Encore\Admin\Controllers\HasResourceActions;

class DedicateWareController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'vips-dedicate';
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
         $typeSpecial = false;
        $request = request('type');
        if ($request !=null && ($request == 25)) {
            $typeSpecial = true;
        }
         $grid = new Grid(new Ware);
        $grid->model()->orderByDesc('created_at');
        if ($typeSpecial) {
            $grid->model()->whereNotNull('get_type')->where('type', '=', 25);
        } else {
            $grid->model()->whereNotNull('get_type')->where('type', '!=', 25);
        }
         $grid->id('ID');
        $grid->column('name', __('name'));
        $grid->column('price', __('price'))->currency();
        $grid->column('show_img', __('show_img'))->image('', 30);
        $grid->column('img2',__ ('show_img'))->display(function ($path){
            /** @var Ware $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column('get_type', __('get_type'))->select(
            [
                1 => trans('vip level automatic acquisition'),
                //    2=>trans ('activity'),
                //    3=>trans ('treasure box'),
                4 => trans('purchase'),
                // 5=>trans ('background modification'),
                6 => trans('limited time purchase'),
                // 7=>trans ('treasure box point exchange'),
                // 8=>trans ('cp level unlock'),
            ]
        );
        if (!$typeSpecial) {
            $grid->column('type', __('type'))->select(
                [
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
                    25 => trans('Special Id'),
                    21 => trans('sound effect'),
                    22 => trans('upload GIF image')
                ]
            );
        }

        if (!$typeSpecial) {
            $grid->title(__('title'));
        } else {
            $grid->value(__('value'));
        }

        $grid->level(__('level'));

        $grid->column('color', __('color'));
        $grid->expire(__('expire'));
        if (\Encore\Admin\Facades\Admin::user()->can('*')) {
            $grid->column('enable', __('enable'))->switch(Common::getSwitchStates());
        }
        $grid->sort(__('sort'), __('sort'));
        $grid->disableExport();

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
            $actions->add(new DedicateAction());
        });
        $grid->disableCreateButton();

        $grid->tools(function (Grid\Tools $tools) use($typeSpecial) {
            $url = '/admin/wares/create';
            if ($typeSpecial) {
                $url = "/admin/special-wares/create";
            }
            $button = '<a href="' . $url . '" class="btn btn-sm btn-success"><i class="fa fa-plus"></i>&nbsp;&nbsp;Create New</a>';
            $tools->append($button);
        });
        Admin::script("
        if (window.innerWidth >= 1024) { // Example threshold for desktop screens
            $('.table-responsive').removeClass('table-responsive');
            }
        ");
        return $grid;
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
            [
                1 => trans('vip level automatic acquisition'),
                //               2=>trans ('activity'),
                //               3=>trans ('treasure box'),
                4 => __('purchase'),
                //               5=>trans ('background modification'),
                //    6=>trans ('limited time purchase'),
                //               7=>trans ('treasure box point exchange'),
                //               8=>trans ('cp level unlock'),
            ]
        )->default(4);
        $form->select('type', trans('type'))->options(
            [
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
                13 => trans('hide country'),
                14 => trans('vip gifts'),
                15 => trans('no pan'),
                16 => trans('hidden room'),
                17 => trans('anonymous man'),
                18 => trans('colored name'),
                19 => trans('profile visitors hide in'),
                20 => trans('hide last active'),
                21 => trans('sound effect'),
                22 => trans('upload GIF image')
            ]
        )->rules('required');
        //        ->rules (function ($form){
        //            if (!$id = $form->model()->id) {
        //                return 'required';
        //            }
        //        });
        $form->text('name', trans('name'));
        $form->text('title', trans('title'));
        $form->currency('price', trans('price'))->symbol('💰');
        //        $form->number('score', trans('score'));
        $form->number('level', trans('level'));
        $form->image('show_img', trans('img'))->default('1.png')->rules('required');

        //        $form->image('img1', trans('img'));
        $form->file('img2', trans('svg'))->name(function () {
            return 'svga_' . Str::random(6);
       });
        $form->select('image_type', __('image_type'))->options (
            [
                'svga'=>__ ('svga'),
                'alpha'=>__ ('alpha'),
                'mp4'=>__ ('mp4'),
            ]
        )->required();
        //        $form->file('img3', trans('video'));
        $form->color('color', trans('color'));
        $form->number('expire', trans('expire(in days)'))->placeholder(trans('0 if permanent'));
        $form->switch('enable', trans('enable'))->states(Common::getSwitchStates());
        //        $form->number('sort', 'sort');
        $form->number('num', __('num'));

        // $form->image('show_img', trans('img'))->default('1.png')->rules(function ($rules) {
        //     $rules->required(); // Add a custom rule to ensure a file is uploaded
        //     $rules->image(); // Add the image validation rule to check if the uploaded file is an image
        //     return $rules;
        // });

        return $form;
    }
}
