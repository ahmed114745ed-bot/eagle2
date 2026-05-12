<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\Country;
use App\Models\CountryCategory;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\App;
use App\Admin\Controllers\MainController;


use App\Admin\Actions\Grid\MoveGroupCountry;
use App\Admin\Actions\MoveCountryCategoryAction;
use Encore\Admin\Controllers\HasResourceActions;


class CountryController extends MainController
{
    use HasResourceActions;

    public $permission_name = 'country';

    protected $filterId;

    public function __construct()
    {
        $this->filterId = request('filter');
    }

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('countries'))
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
            ->title(trans('countries'))
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
            ->title(trans('countries'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('countries'))
            ->body($this->form()));
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Country);
        $filterType = request()->get('filter', 'all');
        $grid->model()->when($filterType !== 'all', function ($q) use ($filterType) {
            $q->where('country_category_id', $filterType);
        })->orderByDesc('status');

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();

            $filter->equal('e_name', __('name'));
        });

        $grid->header(function () use ($filterType) {
            $locale = App::getLocale();

            $tabs = ['all' => __('All')];
            $categories = CountryCategory::orderBy('id')->get();
            foreach ($categories as $category) {
                $title = $category->title[$locale] ?? $category->title['en'] ?? '';
                $tabs[$category->id] = $title;
            }

            // Pre-warm the row action cache so MoveCountryCategoryAction doesn't re-query
            MoveCountryCategoryAction::warmCategoryCache($categories, $locale);

            $html = '<div class="nav-tabs-custom"><ul class="nav nav-tabs">';
            foreach ($tabs as $key => $label) {
                $active = $filterType == $key ? 'active' : '';
                $url = request()->fullUrlWithQuery(['filter' => $key]);
                $html .= "<li class='{$active}'><a href='{$url}'>{$label}</a></li>";
            }
            $html .= '</ul></div>';

            return $html;
        });

        $grid->id(__('ID'));
        $grid->column('e_name', __('name'))->display(function ($value) {
            return __("countries.$value");
        });
        $grid->phone_code(trans('phone code'));
        $grid->column('flag', trans('flag'))->image('', 30);
        if (Admin::user()->can('status-switch-' . $this->permission_name) || Admin::user()->can('*')) {
            $grid->column('status', trans('status'))->switch(Common::getSwitchStates());
        }
        $this->extendGrid($grid);
        $grid->disableExport();
         $permission    = $this->permission_name;
        $grid->actions(function ($actions)  use ($permission) {
            $actions->disableView();
            $actions->disableDelete();
            if ((Admin::user()->can('move-switch-' . $permission) || Admin::user()->can('*'))) {
                $actions->add(new MoveCountryCategoryAction());
            }
        });
        $grid->disableCreateButton();

       
       


        $grid->batchActions(function ($batch) use ($permission) {
            $batch->disableDelete();
            if ((Admin::user()->can('move-switch-' . $permission) || Admin::user()->can('*'))) {
                $batch->add(new MoveGroupCountry());
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
        $show = new Show(Country::findOrFail($id));

        $show->id('ID');
        $show->name(trans('name'));
        $show->e_name(trans('english name'));
        $show->phone_code(trans('phone code'));
        // $show->language(trans('language'));
        // $show->iso(trans('iso'));
        // $show->iso3(trans('iso3'));
        // $show->continent_name(trans('continent name'));
        // $show->e_continent_name(trans('english continent name'));

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
        $form = new Form(new Country);
        $this->disableFormTools($form);

        $form->display(__('ID'));
        $form->text('name', trans('name'))->rules('required');;
        $form->text('e_name', trans('english name'))->rules('required');;
        $form->image('flag', trans('flag'))->rules('required');
        $form->switch('status', trans('	status'))->states(Common::getSwitchStates());



        return $form;
    }
}
