<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\EmojiCategory;
use App\Models\CountryCategory;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\App;
use App\Admin\Controllers\MainController;

class CountryCategoryController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'CountryCategory';
    public $permission_name = 'country-categories';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('Country Categories'))
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
            ->title(trans('Country Categories'))
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
            ->title(trans('Country Categories'))
            ->body($this->form($id)->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('Country Categories'))
            ->body($this->form()));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CountryCategory());
        $grid->sortable();
        $grid->model()->orderBy('sort', 'asc');
        $grid->column('id', __('Id'));
        $grid->column('title', __('title'))->display(function ($value) {
            $locale = App::getLocale();

            // $value is already an array because of casts
            return $value[$locale] ?? ($value['en'] ?? '');
        });
        $grid->column('type', __('Type'));

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
        $show = new Show(CountryCategory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('type', __('Type'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id = null)
    {
        $form = new Form(new CountryCategory());

        $form->html(view('admin.multi_lang_tabs', [

            'model' => $id != null ? CountryCategory::find($id) : [],
        ]));

        $form->text('type', __('Type'));
        $form->number('sort', __('sort'))
            ->rules('required|integer|min:1')      // minimum value 1
            ->required();

        $form->saving(function (Form $form) {
            $titles = request()->input('title', []);
            $form->model()->title = $titles;
        });

        return $form;
    }
}
