<?php

namespace Utd\Room\Http\Controllers\Admin;

use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Utd\Room\Entities\RoomTarget;

class RoomTargetController extends \App\Admin\Controllers\MainController
{
    use HasResourceActions;

    public $permission_name = 'room-target';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('room-target'))
            ->body($this->grid()));
    }

    /**
     * Show interface.
     *
     * @param  mixed  $id
     * @return Content
     */
    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(trans('room-target'))
            ->body($this->detail($id)));
    }

    /**
     * Edit interface.
     *
     * @param  mixed  $id
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(trans('room-target'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('room-target'))
            ->body($this->form()));
    }

    protected function grid()
    {

        $grid = new Grid(new RoomTarget);
        $grid->column('coins', __('coins'));
        $grid->column('usd', __('usd'));

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(RoomTarget::findOrFail($id));
        $this->extendShow($show);

        return $show;
    }

    protected function form()
    {
        $form = new Form(new RoomTarget);
        $form->number('coins', 'coins');
        $form->number('usd', 'usd');

        return $form;
    }
}
