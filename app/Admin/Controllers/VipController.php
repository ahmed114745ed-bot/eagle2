<?php

namespace App\Admin\Controllers;

use Admin;
use App\Models\Vip;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use App\Models\Admin as AdminModel;
use App\Services\AppFeatureService;

use Illuminate\Support\Facades\Auth;
use App\Admin\Actions\DenyDeleteAction;

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
        return $content
            ->title(trans('charge level'))
            ->body($this->grid());
    }
    public function senderIndex(Content $content){
        return $content
        ->title(trans('charge level'))
        ->body($this->senderGrid());
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
                5=>__ ('charge'),
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

    public function receiverIndex(Content $content){
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
                5=>__ ('charge'),
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
    public function cpIndex(Content $content){
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
                5=>__ ('charge'),
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


    public function roomIndex(Content $content){
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
                5=>__ ('charge'),
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

    public function chargeIndex(Content $content){
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
                5=>__ ('charge'),
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
        return $content
            ->title(trans('charge level'))
            ->body($this->detail($id));
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
        return $content
            ->title(trans('charge level'))
            ->body($this->form()->edit($id));
    }

    public function create(Content $content)
    {
        return $content
            ->title(trans('charge level'))
            ->body($this->form());
    }

    public function update($id)
    {
        // $id = request()->route('id');
        $banner = vip::find($id);
        $admin = Auth::user();
        $created = AdminModel::find($banner->created_by);
        if ((!$admin->isRole('developer')) && $created && ($created->isRole('developer'))) {
            admin_info(trans('messages.denyDelete'));
            return redirect()->route('admin.vips.index');
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
        $grid = new Grid(new Vip());
        $grid->model()->orderByDesc('type')->orderBy('exp');
        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->where(function ($query) {
                switch ($this->input) {
                    case 'sender':
                        // custom complex query if the 'yes' option is selected
                        $query->where('type', 2);
                        break;
                    case 'received':
                        $query->where('type', 1);
                        break;
                    case 'cp':
                        $query->where('type', 3);
                        break;
                    case 'room':
                        $query->where('type', 4);
                        break;
                    case 'charge':
                        $query->where('type', 5);
                        break;
                }
            }, __('Select type'), 'name_for_url_shortcut')->radio([
                '' => __('All'),
                'sender' => __('Sender'),
                'received' => __('Received'),
                'cp' => __('cp'),
                'room' => __('room'),
            ]);
        });

        $grid->quickSearch();
        $grid->column('id', __('Id'));
        $grid->column('type', __('Type'))->select(
            [
                1 => __('broadcaster'),
                2 => __('honor'),
                3 => __('cp'),
                4 => __('room'),
                5=>__ ('charge'),
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
                5=>__ ('charge'),
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

        $form->saving(function (Form $form) {
            if ($form->isCreating()) {
                $form->model()->created_by = auth()->id();
            }
            $form->model()->updated_by = auth()->id();
        });
        return $form;
    }
}
