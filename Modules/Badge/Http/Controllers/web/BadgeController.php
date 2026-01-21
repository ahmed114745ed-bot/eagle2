<?php

namespace Modules\Badge\Http\Controllers\web;


use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Enums\BadgeType;
use App\Enums\ImageType;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Modules\Badge\Entities\Badge;
use App\Admin\Controllers\MainController;

class BadgeController extends MainController
{
    public $permission_name = 'badges';

    protected $title = 'Badges';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('badges'))
            ->body($this->grid()));
    }

    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(trans('badges'))
            ->body($this->detail($id)));
    }

    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(trans('badges'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('badges'))
            ->body($this->form()));
    }


    protected function grid()
    {
        $grid = new Grid(new Badge());

        $grid->model()->orderBy('priority', 'desc');

        $grid->column('id', __('ID'));
        $grid->column('name', __('name'));
        if (!request()->filled('_export_')) {
            $grid->column('image', __('image'))->display(function ($path) {
                /** @var Ware $this */
                $url = getImagePath($path);
                return handleShowImageWithTypes($this->id, $url, 50, 50);
            });
        }
        $grid->column('priority', __('Priority'))->sortable();

        $grid->filter(function ($filter) {
            $filter->expand();
            $filter->like('name', 'name');
            $filter->equal('priority', 'Priority');
        });
        Admin::script("
        if (window.innerWidth >= 1024) { // Example threshold for desktop screens
            $('.table-responsive').removeClass('table-responsive');
            }
        ");
        $this->extendGrid($grid);
        return $grid;
    }

    /**
     * Make a form builder.
     */
    protected function form()
    {
        $form = new Form(new Badge());

        // if (!$form->isEditing()) {
        //     $form->setAction(admin_url('badges'));
        // }

        $form->text('name', __('Name'))
            ->rules('required|unique:badges,name,{{id}}');

        // $form->image('show_image', trans('img'))->name(function ($file) {
        //     return now()->timestamp . rand(0, 999) . '.' . $file->guessExtension();
        // });

        $form->file('image', __('Default Image'))->name(function ($file) {
            return now()->timestamp . rand(0, 999) . '.' . $file->getClientOriginalExtension();
        })->required();
        $form->select('type', __('Type'))
            ->options(BadgeType::options())
            ->default(BadgeType::Regular->value)
            ->rules('required|in:' . implode(',', array_keys(BadgeType::options())));
        $form->select('image_type', __('Image Type'))
            ->options(ImageType::options())
            ->default(ImageType::Image->value)
            ->rules('required|in:' . implode(',', array_keys(ImageType::options())));

        $form->number('priority', __('Priority'))->min(0)->default(0)->required();


        return $form;
    }

    protected function detail($id)
    {
        $show = new Show(Badge::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('name', __('name'));
        $show->field('image', __('image'))->display(function ($path) {
            /** @var Ware $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $show->field('image_type', __('image_type'));
        $show->field('type', __('type'));
        $show->field('priority', __('Priority'));
        return $show;
    }
}
