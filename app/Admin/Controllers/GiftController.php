<?php

namespace App\Admin\Controllers;

use App\Models\Gift;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use Encore\Admin\Admin;
use Illuminate\Support\Str;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\HasResourceActions;

class GiftController extends MainController
{
    use HasResourceActions;

    public $permission_name = 'gift';
    public function index(Content $content)
    {
        return $content
            ->title(trans('Gifts'))
            ->body($this->grid());
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
            ->title(trans('Gifts'))
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
            ->title(trans('Gifts'))
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
            ->title(trans('Gifts'))
            ->body($this->form());
    }



    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Gift);
        $grid->model()->orderBy("use_count", "desc");

        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            // $filter->like('type', __('type'));
            $filter->in('type', __('type'))->multipleSelect(
                translate(TYPE_GIFT) 
            );

            $filter->expand();
        });
        $grid->id(__('ID'));
        $grid->column('name', __('name'))->editable();
        $grid->column('e_name', __('e_name'))->editable();
        $grid->column('price', __('price'))->editable();
        $grid->column('img', trans('image'))->image('', '50', 50);
        $grid->column('show_img', trans('show_img'))->display(function ($path) {
            /** @var Gift $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column("use_count", __('use count'));
        $grid->column('type', __('type'))->select(translate(TYPE_GIFT) );
        $grid->vip_level(__('vip_level'));
        $grid->column('hot', trans('hot'));
        $grid->column('is_play', trans('is_play'))->switch(Common::getSwitchStates());

        // $grid->column('show_img2',trans ('show_img2'))->image ('','30');
        $grid->column('enable', trans('enable'))->switch(Common::getSwitchStates());
        $grid->column('music_gift', trans('music_gift'))->switch(Common::getSwitchStatesGiftMucic());
        $grid->sort(__('sort'))->editable();
        $grid->model()->where('type', '!=', 8)->orderBy('type')->orderByRaw('ISNULL(`sort`), `sort`')->orderBy('price');
        //        $grid->column('international_gift',trans ('international_gift'))->switch (Common::getSwitchStatesGiftINtrnahional());



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
            translate(TYPE_GIFT) 
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
        $form->file('show_img', __('show_img'))->name(function ($file) {
             return 'svga_' . Str::random(6). '.' . $file->getClientOriginalExtension();
        })->required();
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
                        'title' => 'Error',
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
                            'title' => 'Error',
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
