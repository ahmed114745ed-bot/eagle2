<?php

namespace App\Admin\Controllers;

use Carbon\Carbon;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\HomeCarousel;
use Encore\Admin\Layout\Content;
use App\Models\Admin as AdminModel;
use Illuminate\Support\Facades\Auth;
use App\Admin\Actions\DenyDeleteAction;
use Encore\Admin\Controllers\HasResourceActions;

class HomeCarouselController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'carousel';
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

    public function update($id)
    {
        // $id = request()->route('id');
        $banner = HomeCarousel::find($id);
        $admin = Auth::user();
        $created = AdminModel::find($banner->created_by);
        if ((!$admin->isRole('developer')) && $created && ($created->isRole('developer'))) {
            admin_info(trans('messages.denyDelete'));
            return redirect()->route('admin.home_carousels.index');
        } else {
            return $this->form()->update($id);
        }
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
        $grid->actions(function ($actions) {
            $model = $actions->row;
            $admin = Auth::user();
            $created = AdminModel::find($model->created_by);

           // dd( $admin ,$created);
           if ((!$admin->isRole('developer')) && $created && ($created->isRole('developer'))) {
                $actions->disableDelete();
                $actions->add(new DenyDeleteAction());
            }
        });
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

        $form->display(__('admin.ID'));
        $form->number('sort', __('sort'));
        $form->image('img', trans('img'))->required();
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
            ->options(['room' => __('Room'), 'normal' => __('normal'), 'link' => __('url'), 'event' => __('events')])
            ->when('room', function (Form $form) {
                $form->select('owner_id', __('owner'))->options('/api/search/users2')->ajax('/api/search/users2', 'id', 'name');
            })->when('link', function (Form $form) {
                $form->url('url', trans('url'))->rules('required|url');
            })->when('event', function (Form $form) {
                $form->select('event_type', trans('events'))->options(['event' => __('events'), 'pk_event' => __('pk_event'), 'weekly_star' => __('weekly_star'), 'charge_event' => __('charge_event'), 'event_period' => __('event_period')])->when('event', function (Form $form) {
                    $form->url('url', trans('url'));
                });
            });
            if ($form->isCreating()) {
                $form->model()->created_by = auth()->id();
            }
            $form->model()->updated_by = auth()->id();

        return $form;
    }
}
