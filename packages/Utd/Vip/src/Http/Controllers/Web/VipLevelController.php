<?php

namespace Utd\Vip\Http\Controllers\Web;

use App\Admin\Controllers\MainController;
use App\Services\AppFeatureService;
use App\Support\PackageHelper;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Utd\Vip\Entities\Vip;

class VipLevelController extends MainController
{
    use HasResourceActions;

    public $permission_name = 'level';

    public $hiddenColumns = [];

    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable('vips');
    }

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('charge level'))
            ->body($this->grid()));
    }

    public function senderIndex(Content $content)
    {
        if (! Admin::user()->can('*')) {
            Permission::check('browse-'.$this->permission_name);
        }

        return parent::index($content
            ->title(trans('charge level'))
            ->body($this->senderGrid()));
    }

    public function receiverIndex(Content $content)
    {
        return $content
            ->title(trans('charge level'))
            ->body($this->receiverGrid());
    }

    public function cpIndex(Content $content)
    {
        if (! PackageHelper::isInstalled('cp')) {
            abort(404);
        }

        return $content
            ->title(trans('charge level'))
            ->body($this->cpGrid());
    }

    public function roomIndex(Content $content)
    {
        return $content
            ->title(trans('charge level'))
            ->body($this->roomGrid());
    }

    public function chargeIndex(Content $content)
    {
        return $content
            ->title(trans('charge level'))
            ->body($this->chargeGrid());
    }

    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(trans('charge level'))
            ->body($this->detail($id)));
    }

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

    protected function getVipTypeOptions(): array
    {
        $options = [
            1 => __('broadcaster'),
            2 => __('honor'),
            4 => __('room'),
            5 => __('charge'),
        ];

        if (PackageHelper::isInstalled('cp')) {
            $options[3] = __('cp');
        }

        ksort($options);

        return $options;
    }

    protected function senderGrid()
    {
        $grid = new Grid(new Vip());
        $grid->model()->where('type', 2)->orderByDesc('type')->orderBy('exp');
        $grid->quickSearch();
        $grid->column('id', __('Id'));
        $grid->column('type', __('Type'))->select($this->getVipTypeOptions());
        $grid->column('level', __('Level'))->editable();
        $grid->column('exp', __('Exp'))->display(function ($column, Grid\Column $value) {
            $value = $value->getOriginal();

            return number_format($value);
        })->editable();
        $grid->column('img', __('Image'))->display(function ($path) {
            $defaultImage = asset('images/image.png');
            $url = getImagePath($path) ?? $defaultImage;
            if (! isImageExists($url)) {
                $url = $defaultImage;
            }

            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $this->extendGrid($grid);
        $grid->disableExport();
        $grid->setResource('vips');

        return $grid;
    }

    protected function receiverGrid()
    {
        $grid = new Grid(new Vip());
        $grid->model()->where('type', 1)->orderByDesc('type')->orderBy('exp');
        $grid->quickSearch();
        $grid->column('id', __('Id'));
        $grid->column('type', __('Type'))->select($this->getVipTypeOptions());
        $grid->column('level', __('Level'))->editable();
        $grid->column('exp', __('Exp'))->display(function ($column, Grid\Column $value) {
            $value = $value->getOriginal();

            return number_format($value);
        })->editable();
        $grid->column('img', __('Image'))->image('', '30');
        $this->extendGrid($grid);
        $grid->disableExport();
        $grid->setResource('vips');

        return $grid;
    }

    protected function cpGrid()
    {
        $grid = new Grid(new Vip());
        $grid->model()->where('type', 3)->orderByDesc('type')->orderBy('exp');
        $grid->quickSearch();
        $grid->column('id', __('Id'));
        $grid->column('type', __('Type'))->select($this->getVipTypeOptions());
        $grid->column('level', __('Level'))->editable();
        $grid->column('exp', __('Exp'))->display(function ($column, Grid\Column $value) {
            $value = $value->getOriginal();

            return number_format($value);
        })->editable();
        $grid->column('img', __('Image'))->image('', '30');
        $this->extendGrid($grid);
        $grid->disableExport();
        $grid->setResource('vips');

        return $grid;
    }

    protected function roomGrid()
    {
        $grid = new Grid(new Vip());
        $grid->model()->where('type', 4)->orderByDesc('type')->orderBy('exp');
        $grid->quickSearch();
        $grid->column('id', __('Id'));
        $grid->column('type', __('Type'))->select($this->getVipTypeOptions());
        $grid->column('level', __('Level'))->editable();
        $grid->column('exp', __('Exp'))->display(function ($column, Grid\Column $value) {
            $value = $value->getOriginal();

            return number_format($value);
        })->editable();
        $grid->column('img', __('Image'))->image('', '30');
        $this->extendGrid($grid);
        $grid->disableExport();
        $grid->setResource('vips');

        return $grid;
    }

    protected function chargeGrid()
    {
        $grid = new Grid(new Vip());
        $grid->model()->where('type', 5)->orderByDesc('type')->orderBy('exp');
        $grid->quickSearch();
        $grid->column('id', __('Id'));
        $grid->column('type', __('Type'))->select($this->getVipTypeOptions());
        $grid->column('level', __('Level'))->editable();
        $grid->column('exp', __('Exp'))->display(function ($column, Grid\Column $value) {
            $value = $value->getOriginal();

            return number_format($value);
        })->editable();
        $grid->column('img', __('Image'))->image('', '30');
        $this->extendGrid($grid);
        $grid->disableExport();
        $grid->setResource('vips');

        return $grid;
    }

    protected function grid()
    {
        $grid = new Grid(new Vip());

        $grid->model()->orderByDesc('type')->orderBy('exp');

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

        $grid->header(function () {
            $tabs = [
                'Appsender' => __('AppSender'),
                'Appreceived' => __('AppReceived'),
                'Appcharge' => __('AppCharge'),
            ];

            if (PackageHelper::isInstalled('cp')) {
                $tabs['Appcp'] = __('AppCP');
            }

            if (PackageHelper::isInstalled('room')) {
                $tabs['Approom'] = __('AppRoom');
            }

            return view('admin.tabs', compact('tabs'));
        });

        $grid->quickSearch();

        $grid->column('level', __('Level'))->editable();

        $grid->column('exp', __('Exp'))->display(function ($column, Grid\Column $value) {
            return number_format($value->getOriginal());
        })->editable();

        $grid->column('img', __('Image'))->display(function ($img) {
            $defaultImage = asset('images/image.png');
            $url = getImagePath($img) ?? $defaultImage;
            if (! isImageExists($url)) {
                $url = $defaultImage;
            }

            $ext = mb_strtolower(pathinfo($url, PATHINFO_EXTENSION));

            $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

            $inner = handleShowImageWithTypes($this->id, $url, 50, 50);

            if (in_array($ext, $imageTypes)) {
                return "<a href='{$url}' target='_blank'><img src='{$url}' style='width:50px'/></a>";
            }

            return $inner;

        });

        $this->extendGrid($grid);

        $grid->disableExport();
        $currentTab = request('tab', 'Appsender');
        $grid->disableCreateButton();
        $grid->tools(function (Grid\Tools $tools) use ($currentTab) {
            $tools->append('<a href="'.admin_url('vips/create?tab='.$currentTab).'" class="btn btn-sm btn-success">
            <i class="fa fa-plus"></i>&nbsp;'.trans('admin.new').'</a>');
        });

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(Vip::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('type', __('Type'))->number();
        $show->field('level', __('Level'))->number();
        $show->field('exp', __('Exp'))->number();
        $show->field('img', __('Image'))->image();
        $this->extendShow($show);

        return $show;
    }

    protected function form()
    {
        $form = new Form(new Vip());
        $this->disableFormTools($form);

        $tabToTypeMap = [
            'Appsender' => 2,
            'Appreceived' => 1,
            'Appcp' => 3,
            'Approom' => 4,
            'Appcharge' => 5,
        ];

        $currentTab = request('tab', 'Appsender');
        $currentType = $tabToTypeMap[$currentTab] ?? 2;

        if ($form->isCreating()) {
            $form->hidden('type')->default($currentType);

            $typeLabels = $this->getVipTypeOptions();
            $form->display('type_display', __('Type'))->default($typeLabels[$currentType] ?? '');
        } else {
            $form->select('type', __('Type'))->options($this->getVipTypeOptions());
        }

        $form->number('level', __('Level'))->required();
        $form->number('exp', __('Exp'))->help(__('sender: 1 coin = 1 exp -- receiver: 1 coin = 1 exp'));
        $form->file('img', __('Image'))->name(function ($file) {
            return now()->timestamp.rand(0, 999).'.'.$file->guessExtension();
        })->removable()->rules('required');

        $form->footer(function ($footer) {
            $footer->disableReset();
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        return $form;
    }
}
