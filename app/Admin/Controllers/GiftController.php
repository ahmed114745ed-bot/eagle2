<?php

namespace App\Admin\Controllers;

use App\Helpers\Common;
use App\Models\Gift;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class GiftController extends MainController
{
    use HasResourceActions;

    public $permission_name = 'gift';
    public $hiddenColumns = [];

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new Gift);
        $grid->model()->orderBy("use_count", "desc");
        $grid->id(__('ID'));
        $grid->name(__('name'));
        $grid->e_name(__('e_name'));
        $grid->type(__('type'));
        $grid->column("use_count", __('use count'));
        $grid->vip_level(__('vip_level'));
        $grid->column('hot', trans('hot'));
        $grid->column('is_play', trans('is_play'))->switch(Common::getSwitchStates());
        $grid->price(__('price'));
        $grid->column('img', trans('image'))->image('', '30');
        $grid->column('show_img2', trans('show_img'))->image('', '30')->display(function($data){
            /** @var Gift $this*/

                $path = $this->show_img;

            if ($this->image_type == 'svga') {
                $model = 'this' . $this->id;
                $model2 = 'this2' . $this->id;
                $url = getImagePath($path);
                Admin::script(
                    script: "
                var $model = new SVGA.Player('#$model');
               $model.loops = 1;
               $model.clearsAfterStop = false;
           var $model2 = new SVGA.Parser('#$model');
           function pauseAnimation(){
                $model.pauseAnimation();
           }

            function stopAnimation(){
                $model2.stopAnimation();
            }
           ");
                Admin::script(
                    script: "
           $model2.load('$url', function(videoItem) {

               $model.setVideoItem(videoItem);
               $model.startAnimation();
           $model.onFinished(function(){


           });
           })

           ");
                return "<div id='$model' style='width: 50px; height: 50px'> </div>";
            } elseif ($this->image_type == 'mp4') {
                return "<video href='$path' style='height: 50px; width: 50px'  />";

            }

            return "<img href='$path' style='height: 50px; width: 50px' alt='' />";
        });
        // $grid->column('show_img2',trans ('show_img2'))->image ('','30');
        $grid->column('enable', trans('enable'))->switch(Common::getSwitchStates());
        $grid->column('music_gift', trans('music_gift'))->switch(Common::getSwitchStatesGiftMucic());
        $grid->sort(__('sort'))->editable();
        $grid->model()->where('type', '!=', 8)->orderBy('type')->orderByRaw('ISNULL(`sort`), `sort`')->orderBy('price');
        //        $grid->column('international_gift',trans ('international_gift'))->switch (Common::getSwitchStatesGiftINtrnahional());
        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            // $filter->like('type', __('type'));
            $filter->in('type', __('type'))->multipleSelect(
                [
                    1 => __('normal'),
                    2 => __('hot'),
                    3 => __('country'),
                    4 => __('Moment'),
                    5 => __('Famous gifts'),
                    6 => __('Lucky gifts'),
                    7 => __('events'),


                ]
            );

            $filter->expand();
        });


        $this->extendGrid($grid);
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
        $show = new Show(Gift::findOrFail($id));
        // $show->id('ID');
        // $show->name(__('name'));
        // $show->e_name(__('e_name'));
        // $show->type(__('type'));
        // $show->vip_level(__('vip_level'));
        // $show->hot(__('hot'));
        // $show->is_play('is_play');
        // $show->price(__('price'));
        // $show->img(__('img'));
        // $show->show_img(('show_img'));
        // $show->show_img2('show_img2');
        // $show->sort('sort');
        // $show->enable('enable');
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
        $form = new Form(new Gift);
        $form->display(__('ID'));
        $form->text('name', __('name'));
        $form->text('e_name', __('e_name'));
        $form->select('type', __('type'))->options(
            [
                1 => __('normal'),
                2 => __('hot'),
                3 => __('country'),
                4 => __('Moment'),
                5 => __('Famous gifts'),
                6 => __('Lucky gifts'),
                7 => __('events'),

            ]
        )->attribute(['id' => 'type'])->required();

        $form->number('luckyGift.win_probability', __('win probability'))
            ->min(10)
            ->max(100)
            ->placeholder(__('Enter win probability'));

        $form->number('luckyGift.min_percentag', __('min percentage'))
            ->min(0)
            ->max(100)
            ->placeholder(__('Enter min_percentage'))
            ->default(function ($form) {
                $value = @$form->model()->luckyGift->min_percentage;
                return @explode(',', $value)[0] ?? 0;
            })
            ->attribute(['id' => 'min_percentage']);
        $form->number('luckyGift.mid_percentag', __('mid percentage'))
            ->min(0)
            ->max(100)
            ->placeholder(__('Enter mid_percentage'))
            ->default(function ($form) {
                $value = @$form->model()->luckyGift->min_percentage;
                return @explode(',', $value)[1] ?? 0;
            })
            ->attribute(['id' => 'mid_percentage']);
        $form->number('luckyGift.max_percentag', __('max percentage'))
            ->min(0)
            ->max(100)
            ->placeholder(__('Enter max_percentage'))
            ->default(function ($form) {
                $value = @$form->model()->luckyGift->min_percentage;
                return @explode(',', $value)[2] ?? 0;
            })
            ->attribute(['id' => 'max_percentage']);

        // Add custom JS
        $script = <<<SCRIPT
             $(document).ready(function() {
                 function toggleWinProbability() {
                     var type = $('#type').val();
                     if(type == '6') {
                         $('#win_probability').closest('.form-group').show();
                         $('#min_percentage').closest('.form-group').show();
                         $('#mid_percentage').closest('.form-group').show();
                         $('#max_percentage').closest('.form-group').show();
                     } else {
                         $('#win_probability').closest('.form-group').hide();
                         $('#min_percentage').closest('.form-group').hide();
                         $('#mid_percentage').closest('.form-group').hide();
                         $('#max_percentage').closest('.form-group').hide();
                     }
                 }
                 toggleWinProbability();

                 $('#type').change(function() {
                     toggleWinProbability();
                 });
             });
             SCRIPT;
        Admin::script($script);
        $form->number('vip_level', __('vip_level'))->min(0)->placeholder(__('less than 256'));
        $form->currency('price', __('price'))->symbol('💎');
        $form->file('img', __('img'));
        $form->file('show_img', __('show_img'))->required();
        $form->select('image_type', __('image_type'))->options(
            [
                'svga' => __('svga'),
                'alpha' => __('alpha'),
                'mp4' => __('mp4'),
            ]
        )->required();
        $form->file('show_img2', __('show_img2'));
        $form->number('sort', __('sort'));
        $form->switch('enable', __('enable'))->states(Common::getSwitchStates());
        $form->switch('music_gift', trans('music_gift'))->states(Common::getSwitchStatesGiftMucic());
        $form->saving(function (Form $form) {
            if ($form->model()->type != "6") {
                $type = $form->input('type');
                $win_probability = $form->input('luckyGift.win_probability');
                $min_percentag = $form->input('luckyGift.min_percentag');
                $mid_percentage = $form->input('luckyGift.mid_percentag');
                $max_percentage = $form->input('luckyGift.max_percentag');
                if (($min_percentag + $mid_percentage + $max_percentage) != 100) {
                    $error = new \Illuminate\Support\MessageBag([
                        'title'   => 'Error',
                        'message' => 'The sum of percentages must be equal to 100.',
                    ]);
                }

                if ($form->model()->type == "6" || request()->type == 6) {
                    $type = $form->input('type');
                    $win_probability = $form->input('luckyGift.win_probability');
                    $min_percentag = $form->input('luckyGift.min_percentag');
                    $mid_percentage = $form->input('luckyGift.mid_percentag');
                    $max_percentage = $form->input('luckyGift.max_percentag');
                    if (($min_percentag + $mid_percentage + $max_percentage) != 100) {
                        $error = new \Illuminate\Support\MessageBag([
                            'title'   => 'Error',
                            'message' => 'The sum of percentages must be equal to 100.',
                        ]);

                        return back()->with(compact('error'))->withInput();
                    }
                }
            }
        });
        return $form;
    }
}
