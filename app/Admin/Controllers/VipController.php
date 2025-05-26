<?php

namespace App\Admin\Controllers;

use App\Models\Vip;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Auth\Permission;

use App\Services\AppFeatureService;
use App\Admin\Controllers\MainController;

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
        if (!Admin::user()->can('*')) {
            Permission::check('browse-' . $this->permission_name);
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
        $grid->column('img', __('Image'))->display(function ($path) {
            /** @var Ware $this */
            $defaultImage = asset("images/image.png");
            $url = getImagePath($path) ?? $defaultImage;
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
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
        return parent::show($id, $content
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
        return parent::edit($id, $content
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
        $grid->model()->when(request('tab', 'Appsender'), function ($query, $tab) {
            switch ($tab) {
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

        /* $grid->column('id', __('Id'));

        $grid->column('type', __('Type'))->select([
            1 => __('broadcaster'),
            2 => __('honor'),
            3 => __('cp'),
            4 => __('room'),
            5 => __('charge'),
        ]); */

        $grid->column('level', __('Level'))->editable();

        $grid->column('exp', __('Exp'))->display(function ($column, Grid\Column $value) {
            return number_format($value->getOriginal());
        })->editable();

        $grid->column('img', __('Image'))->display(function ($img) {
            if (!$img) return '';
            $url = getImagePath($img);
            return "<a href='{$url}' target='_blank'><img src='{$url}' style='width:50px'/></a>";
        });

        // Any custom grid extensions
        $this->extendGrid($grid);

        // No export button
        $grid->disableExport();
        $currentTab = request('tab', 'Appsender');
        $grid->disableCreateButton();
        $grid->tools(function (Grid\Tools $tools) use ($currentTab) {
            $tools->append('<a href="' . admin_url('vips/create?tab=' . $currentTab) . '" class="btn btn-sm btn-success">
            <i class="fa fa-plus"></i>&nbsp;' . trans('admin.new') . '</a>');
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

        $tabToTypeMap = [
            'Appsender' => 2,   // honor
            'Appreceived' => 1, // broadcaster
            'Appcp' => 3,       // cp
            'Approom' => 4,     // room
            'Appcharge' => 5,   // charge
        ];

        $currentTab = request('tab', 'Appsender');
        $currentType = $tabToTypeMap[$currentTab] ?? 2; // Default to 2 if tab not found

        if ($form->isCreating()) {
            $form->hidden('type')->default($currentType);

            $typeLabels = [
                1 => __('broadcaster'),
                2 => __('honor'),
                3 => __('cp'),
                4 => __('room'),
                5 => __('charge'),
            ];
            $form->display('type_display', __('Type'))->default($typeLabels[$currentType]);
        } else {
            $form->select('type', __('Type'))->options([
                1 => __('broadcaster'),
                2 => __('honor'),
                3 => __('cp'),
                4 => __('room'),
                5 => __('charge'),
            ]);
        }

        //        $form->textarea('name_ar', __('name_ar'));
        //        $form->textarea('name_en', __('name_en'));
        $form->number('level', __('Level'))->required();
        $form->number('exp', __('Exp'))->help(__('sender: 1 coin = 1 exp -- receiver: 1 coin = 1 exp'));
        //        $form->number('di', __('Diamonds'));
        //        $form->number('co', __('Coins'));
        $form->image('img', __('Image'))->name(function ($file) {
            return now()->timestamp . rand(0, 999) . '.' . $file->guessExtension();
        });

        $form->footer(function ($footer) {
            $footer->disableReset();        // Disables the "Reset" button
            $footer->disableViewCheck();    // Disables the "View" checkbox
            $footer->disableEditingCheck(); // Disables the "Continue editing" checkbox
            $footer->disableCreatingCheck(); // Disables the "Continue creating" checkbox
        });

        return $form;
    }
}
