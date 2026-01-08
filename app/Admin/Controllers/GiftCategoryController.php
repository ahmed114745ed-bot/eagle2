<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;

use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\GiftCategory;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\App;
use App\Admin\Controllers\MainController;

class GiftCategoryController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'GiftCategory';
    public $permission_name = 'gift-categories';




    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('Gift Categories'))
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
            ->title(trans('Gift Categories'))
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
            ->title(trans('Gift Categories'))
            ->body($this->form($id)->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('Gift Categories'))
            ->body($this->form()));
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new GiftCategory());
        $grid->sortable();
        $grid->model()->orderBy('sort', 'asc');
        $grid->column('id', __('Id'));
        $grid->column('title', __('title'))->display(function ($value) {
            $locale = App::getLocale();

            // $value is already an array because of casts
            return $value[$locale] ?? ($value['en'] ?? '');
        });
        $grid->column('type', __('type'));

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
        $show = new Show(GiftCategory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('type', __('Type'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id = null)
    {
        $form = new Form(new GiftCategory());

        // Pass model to view (this makes edit mode show old values)
        $form->html(view('admin.multi_lang_tabs', [

            'model' => $id != null ? GiftCategory::find($id) : [],
        ]));

        // Type field
        $form->select('type', __('Type'))->options([
            'normal'     => __('Normal'),
            'lucky_gift' => __('Lucky gifts'),
            'cp'         => __('CP'),
            'vip'        => __('VIP'),
        ])->required();
        $form->number('sort', __('sort'))
            ->rules('required|integer|min:1')      // minimum value 1
            ->required();

        // Save titles back as array
        $form->saving(function (Form $form) {
            $titles = request()->input('title', []);
            $form->model()->title = $titles;
        });

        return $form;
    }
}
