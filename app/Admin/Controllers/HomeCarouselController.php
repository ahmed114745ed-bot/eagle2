<?php

namespace App\Admin\Controllers;

use App\Tik\Services\Files\ImageConverter;
use Carbon\Carbon;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\HomeCarousel;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Layout\Content;
use Illuminate\Validation\Rule;

class HomeCarouselController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'banner';
    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('Banner'))
            ->body($this->grid()));
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
        return parent::show($id, $content
            ->title(trans('HomeCarousel'))
            ->body($this->detail($id)));
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
        return parent::edit($id, $content
            ->title(trans('HomeCarousel'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('HomeCarousel'))
            ->body($this->form()));
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new HomeCarousel);

        $grid->id(__('ID'));
        $grid->column('img', trans('img'))->image('', 235, 77);
        $grid->column('url', trans('url'))->url();
        $grid->column('enable', trans('enable'))->switch(Common::getSwitchStates())->display(function ($enable, $column) {
            if ($this->duration > Carbon::now()->timestamp || $this->duration == null) {
                return $enable;
            }
            return null;
        });
        $grid->column('sort', trans('sort'))->editable();
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
        $show = new Show(HomeCarousel::findOrFail($id));

        //        $show->id('ID');
        //        $show->img('img');
        //        $show->contents('contents');
        //        $show->url('url');
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
        $form = new Form(new HomeCarousel);
        $this->disableFormTools($form);

        $form->display(__('admin.ID'));
        $form->number('sort', __('sort'));
        $form->imagePath('img', trans('img'))->setResolution(80)->required();
        $form->switch('enable', trans('enable'))->states(Common::getSwitchStates())->default(true);
        $form->select('form', trans('time view type'))->options([0 => __(''), 1 => __('hours'), 2 => __('days'), 3 => __('month')])
            ->when(1, function (Form $form) {
                $form->text('input', trans('input'));
            })->when(2, function (Form $form) {
                $form->text('input', trans('input'));
            })->when(3, function (Form $form) {
                $form->text('input', trans('input'));
            });

        $form->select('type', trans('type'))
            ->options(['room' => __('Room'), 'normal' => __('normal'), 'link' => __('url'), 'event' => __('events'),'live' => __('Live')])
            ->when('room', function (Form $form) {
                $form->select('owner_id', __('owner'))->options('/api/search/users2')->ajax('/api/search/users2', 'id', 'name');
            })->when('link', function (Form $form) {
                $form->url('url', trans('url'))->rules('required|url');
            })->when('event', function (Form $form) {
                $form->select('event_type', trans('events'))->options(['event' => __('events'), 'pk_event' => __('pk_event'), 'weekly_star' => __('weekly_star'), 'charge_event' => __('charge_event'), 'event_period' => __('event_period'), 'weekly_cp' => __('weekly_cp')])->when('event', function (Form $form) {
                    $form->url('url', trans('url'));
                });
            });

        $form->select('display_at', __('Display At'))
            ->options([
                'discover' => __('Discover'),
                'home_top' => __('Home Top'),
                'home_middle' => __('Home Middle'),
            ])
            ->default('discover')->rules(['required', Rule::in(['home_top', 'home_middle', 'discover'])]);

        $form->saving(function (Form $form) {
            /*if (request()->hasFile('img')) {
                $file = request()->file('img');
                $toWebpAndUpload = ImageConverter::toWebpAndUpload($file, 'banners');
                if ($toWebpAndUpload) {
                    $form->img = $toWebpAndUpload;
                }

            }*/
        });

        return $form;
    }
}
