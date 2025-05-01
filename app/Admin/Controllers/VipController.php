<?php

namespace App\Admin\Controllers;

use App\Models\Vip;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Services\AppFeatureService;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Facades\Admin;

use Encore\Admin\Layout\Content;

class VipController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */

    public $permission_name = 'level';
    public $hiddenColumns = [];

    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable("vips");
    }

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('charge level'))
            ->body($this->grid()));
    }

    
    public function senderIndex(Content $content)
    {
        if (!Admin::user()->can('*')){
            Permission::check('browse-'.$this->permission_name);
        }
        return parent::index($content
            ->title(trans('charge level'))
            ->body($this->senderGrid()));
    }
    protected function senderGrid()
    {
        $grid = new Grid(new Vip());
        $grid->model()->where('type', 2)->orderByDesc('type')->orderBy('exp');
        $grid->quickSearch();
        $grid->column('id', __('Id'));
        $grid->column('type', __('Type'))->select(
            [
                1 => __('broadcaster'),
                2 => __('honor'),
                3 => __('cp'),
                4 => __('room'),
                5 => __('charge'),
            ]
        );
        $grid->column('level', __('Level'))->editable();
        $grid->column('exp', __('Exp'))->display(function ($column, Grid\Column $value) {
            $value = $value->getOriginal();
            return number_format($value);
        })->editable();
        //        $grid->column('di', __('Diamonds'));
        //        $grid->column('co', __('Coins'));
        $grid->column('img', __('Image'))->image('', '30');
        $this->extendGrid($grid);
        $grid->disableExport();
        $grid->setResource('vips');

        return $grid;
    }

    public function receiverIndex(Content $content)
    {
        return $content
            ->title(trans('charge level'))
            ->body($this->receiverGrid());
    }
    protected function receiverGrid()
    {
        $grid = new Grid(new Vip());
        $grid->model()->where('type', 1)->orderByDesc('type')->orderBy('exp');
        $grid->quickSearch();
        $grid->column('id', __('Id'));
        $grid->column('type', __('Type'))->select(
            [
                1 => __('broadcaster'),
                2 => __('honor'),
                3 => __('cp'),
                4 => __('room'),
                5 => __('charge'),
            ]
        );
        $grid->column('level', __('Level'))->editable();
        $grid->column('exp', __('Exp'))->display(function ($column, Grid\Column $value) {
            $value = $value->getOriginal();
            return number_format($value);
        })->editable();
        //        $grid->column('di', __('Diamonds'));
        //        $grid->column('co', __('Coins'));
        $grid->column('img', __('Image'))->image('', '30');
        $this->extendGrid($grid);
        $grid->disableExport();
        $grid->setResource('vips');

        return $grid;
    }
    public function cpIndex(Content $content)
    {
        return $content
            ->title(trans('charge level'))
            ->body($this->cpGrid());
    }

    protected function cpGrid()
    {
        $grid = new Grid(new Vip());
        $grid->model()->where('type', 3)->orderByDesc('type')->orderBy('exp');
        $grid->quickSearch();
        $grid->column('id', __('Id'));
        $grid->column('type', __('Type'))->select(
            [
                1 => __('broadcaster'),
                2 => __('honor'),
                3 => __('cp'),
                4 => __('room'),
                5 => __('charge'),
            ]
        );
        $grid->column('level', __('Level'))->editable();
        $grid->column('exp', __('Exp'))->display(function ($column, Grid\Column $value) {
            $value = $value->getOriginal();
            return number_format($value);
        })->editable();
        //        $grid->column('di', __('Diamonds'));
        //        $grid->column('co', __('Coins'));
        $grid->column('img', __('Image'))->image('', '30');
        $this->extendGrid($grid);
        $grid->disableExport();
        $grid->setResource('vips');

        return $grid;
    }


    public function roomIndex(Content $content)
    {
        return $content
            ->title(trans('charge level'))
            ->body($this->roomGrid());
    }

    protected function roomGrid()
    {
        $grid = new Grid(new Vip());
        $grid->model()->where('type', 4)->orderByDesc('type')->orderBy('exp');
        $grid->quickSearch();
        $grid->column('id', __('Id'));
        $grid->column('type', __('Type'))->select(
            [
                1 => __('broadcaster'),
                2 => __('honor'),
                3 => __('cp'),
                4 => __('room'),
                5 => __('charge'),
            ]
        );
        $grid->column('level', __('Level'))->editable();
        $grid->column('exp', __('Exp'))->display(function ($column, Grid\Column $value) {
            $value = $value->getOriginal();
            return number_format($value);
        })->editable();
        //        $grid->column('di', __('Diamonds'));
        //        $grid->column('co', __('Coins'));
        $grid->column('img', __('Image'))->image('', '30');
        $this->extendGrid($grid);
        $grid->disableExport();
        $grid->setResource('vips');

        return $grid;
    }

    public function chargeIndex(Content $content)
    {
        return $content
            ->title(trans('charge level'))
            ->body($this->chargeGrid());
    }

    protected function chargeGrid()
    {
        $grid = new Grid(new Vip());
        $grid->model()->where('type', 5)->orderByDesc('type')->orderBy('exp');
        $grid->quickSearch();
        $grid->column('id', __('Id'));
        $grid->column('type', __('Type'))->select(
            [
                1 => __('broadcaster'),
                2 => __('honor'),
                3 => __('cp'),
                4 => __('room'),
                5 => __('charge'),
            ]
        );
        $grid->column('level', __('Level'))->editable();
        $grid->column('exp', __('Exp'))->display(function ($column, Grid\Column $value) {
            $value = $value->getOriginal();
            return number_format($value);
        })->editable();
        //        $grid->column('di', __('Diamonds'));
        //        $grid->column('co', __('Coins'));
        $grid->column('img', __('Image'))->image('', '30');
        $this->extendGrid($grid);
        $grid->disableExport();
        $grid->setResource('vips');

        return $grid;
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
        return parent::show($id,$content
            ->title(trans('charge level'))
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
        return parent::edit($id,$content
            ->title(trans('charge level'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('charge level'))
            ->body($this->form()));
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Vip());

        // Sort by type desc then exp asc
        $grid->model()->orderByDesc('type')->orderBy('exp');

        // Filter model by selected tab
        $grid->model()->when(request('tab'), function ($query) {
            switch (request('tab')) {
                case 'Appsender':
                    $query->where('type', 2);
                    break;
                case 'Appreceived':
                    $query->where('type', 1);
                    break;
                case 'Appcp':
                    $query->where('type', 3);
                    break;
                case 'Approom':
                    $query->where('type', 4);
                    break;
                case 'Appcharge':
                    $query->where('type', 5);
                    break;
            }
        });

        // Tabs at top rendered from the Blade view
        $grid->header(function () {
            $tabs = [
                '' => __('All'),
                'Appsender' => __('AppSender'),
                'Appreceived' => __('AppReceived'),
                'Appcp' => __('AppCP'),
                'Approom' => __('AppRoom'),
                'Appcharge' => __('AppCharge'),
            ];

            // Render the Blade view with tabs data
            return view('admin.tabs', compact('tabs'));
        });

        // Other grid settings
        $grid->quickSearch();

        $grid->column('id', __('Id'));

        $grid->column('type', __('Type'))->select([
            1 => __('broadcaster'),
            2 => __('honor'),
            3 => __('cp'),
            4 => __('room'),
            5 => __('charge'),
        ]);

        $grid->column('level', __('Level'))->editable();

        $grid->column('exp', __('Exp'))->display(function ($column, Grid\Column $value) {
            return number_format($value->getOriginal());
        })->editable();

        $grid->column('img', __('Image'))->image('', '30');

        // Any custom grid extensions
        $this->extendGrid($grid);

        // No export button
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
        $show = new Show(Vip::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('type', __('Type'))->number();
        $show->field('level', __('Level'))->number();
        $show->field('exp', __('Exp'))->number();
        //        $show->field('di', __('Diamonds'))->number ();
        //        $show->field('co', __('Coins'))->number ();
        $show->field('img', __('Image'))->image();
        //        $show->field('created_at', __('Created at'));
        //        $show->field('updated_at', __('Updated at'));
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
        $form = new Form(new Vip());

        $form->select('type', __('Type'))->options(
            [
                1 => __('broadcaster'),
                2 => __('honor'),
                3 => __('cp'),
                4 => __('room'),
                5 => __('charge'),
            ]
        )->default(2);
        $form->textarea('name_ar', __('name_ar'));
        $form->textarea('name_en', __('name_en'));
        $form->number('level', __('Level'))->required();
        $form->number('exp', __('Exp'))->help(__('sender: 1 coin = 1 exp -- receiver: 1 coin = 1 exp'));
        //        $form->number('di', __('Diamonds'));
        //        $form->number('co', __('Coins'));
        $form->image('img', __('Image'))->name(function ($file) {
            return now()->timestamp . rand(0, 999) . '.' . $file->guessExtension();
        });

        return $form;
    }
}
