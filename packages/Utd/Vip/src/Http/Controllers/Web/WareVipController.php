<?php

namespace Utd\Vip\Http\Controllers\Web;

use App\Admin\Controllers\MainController;
use App\Helpers\Common;
use App\Models\Ware;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Utd\Vip\Entities\VipPrivilege;

class WareVipController extends MainController
{
    use HasResourceActions;

    public $permission_name = 'wares-vips';

    protected $title = 'wares-vips';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('wares-vips'))
            ->body($this->grid()));
    }

    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(trans('wares-vips'))
            ->body($this->detail($id)));
    }

    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(trans('wares-vips'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('wares-vips'))
            ->body($this->form()));
    }

    protected function grid()
    {
        $grid = new Grid(new Ware());

        $grid->model()->where('get_type', 1)->orderByDesc('is_active_for_vip');
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('level', __('level'));
            });

            $filter->column(1 / 2, function ($filter) {
                $filter->equal('type', __('type'))->select(
                    VipPrivilege::pluck('name', 'type')->toArray()
                );
            });
        });

        $grid->id(__('ID'));
        $grid->column('get_type', __('get_type'))->select(
            [
                1 => trans('vip level automatic acquisition'),
                4 => trans('purchase'),
                6 => trans('limited time purchase'),
            ]
        );
        $grid->column('type', __('type'))->select(
            [
                1 => trans('Gemstone'),
                3 => trans('Card Scroll'),
                4 => trans('Avatar Frame'),
                5 => trans('Bubble Frame'),
                6 => trans('Entering Special Effects'),
                7 => trans('Microphone Aperture'),
                8 => trans('Badge'),
                9 => trans('NoKick'),
                10 => trans('Icon'),
                11 => trans('intro animation'),
                12 => trans('wapel'),
                13 => trans('hide country and last login'),
                14 => trans('vip gifts'),
                15 => trans('no pan'),
                16 => trans('hidden room'),
                17 => trans('anonymous man'),
                18 => trans('colored name'),
                19 => trans('profile visitors hide in'),
                20 => trans('hide last active'),
                21 => trans('sound effect'),
                22 => trans('upload GIF image'),
                28 => trans('profile frame'),
            ]
        );
        $grid->column('name', __('name'))->editable();
        $grid->title(__('title'));
        $grid->column('price', __('price'))->currency();
        $grid->level(__('level'));
        $grid->column('show_img', __('show_img'))->image('', 30);
        $grid->column('color', __('color'));
        $grid->expire(__('expire'));
        if (Admin::user()->can('edit_ware_price') || Admin::user()->can('*')) {
            $grid->column('enable', __('enable'))->switch(Common::getSwitchStates());
        }
        $grid->column('is_active_for_vip', __('active_for_vip'))->switch(Common::getSwitchStates());
        $grid->sort(__('sort'), __('sort'));
        $this->extendGrid($grid);
        $grid->disableExport();

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(Ware::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('get_type', __('Get type'));
        $show->field('type', __('Type'));
        $show->field('name', __('Name'));
        $show->field('title', __('Title'));
        $show->field('price', __('Price'));
        $show->field('score', __('Score'));
        $show->field('level', __('Level'));
        $show->field('show_img', __('Show img'));
        $show->field('img1', __('Img1'));
        $show->field('img2', __('Img2'));
        $show->field('img3', __('Img3'));
        $show->field('color', __('Color'));
        $show->field('expire', __('Expire'));
        $show->field('enable', __('Enable'));
        $show->field('sort', __('Sort'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('num', __('Num'));
        $show->field('is_active_for_vip', __('Is active for vip'));
        $show->field('name_en', __('Name en'));
        $show->field('title_en', __('Title en'));

        return $show;
    }

    protected function form()
    {
        $form = new Form(new Ware());

        $form->display('ID');
        $form->select('get_type', trans('get_type'))->options(
            [
                1 => trans('vip level automatic acquisition'),
            ]
        )->default(1);
        $form->select('type', trans('type'))->options(function ($value) {
            $privileges = [];
            foreach (VipPrivilege::get() as $pri) {
                $privileges[$pri->type] = $pri->name;
            }

            return $privileges;
        })->rules('required');
        $form->text('name', trans('name'));
        $form->text('name_en', trans('Name en'));
        $form->text('title', trans('title'));
        $form->text('title_en', trans('Title en'));
        if (! $form->isEditing()) {
            if (Admin::user()->can('add_ware_price') || Admin::user()->can('*')) {
                $form->currency('price', __('price'))->symbol('💰');
                $form->switch('enable', trans('enable'))->states(Common::getSwitchStates());
            }
        }
        if ($form->isEditing()) {
            if (Admin::user()->can('edit_ware_price') || Admin::user()->can('*')) {
                $form->currency('price', __('price'))->symbol('💰');
                $form->switch('enable', trans('enable'))->states(Common::getSwitchStates());
            }
        }
        $form->number('level', trans('level'))->rules(
            'required|numeric|min:1',
            [
                'min' => 'levels can not be 0',
            ]
        );
        $form->text('key', trans('key'));
        $form->image('show_img', trans('img'))->name(function ($file) {
            return now()->timestamp.rand(0, 999).'.'.$file->guessExtension();
        })->default('1.png');
        $form->switch('half_image_profile', trans('half image'))->states(Common::getSwitchStates());
        $form->file('img2', trans('svg'))->name(function ($file) {
            $wareId = request()->route('wares-vips');
            $wareId = $wareId ?? Ware::max('id') + 1;

            $type = request()->input('type');
            $prefix = '';

            if (app()->environment('local')) {
                $prefix = 't-';
            }

            if ($type === 4) {
                return $prefix.'w-f'.$wareId.'.'.$file->guessExtension();
            }
            if ($type === 5) {
                return $prefix.'w-b'.$wareId.'.'.$file->guessExtension();
            }
            if ($type === 10) {
                return $prefix.'w-vb'.$wareId.'.'.$file->guessExtension();
            }

            return $prefix.'w-default'.$wareId.'.'.$file->guessExtension();

        });
        $form->select('image_type', __('image_type'))->options(
            [
                'svga' => __('svga'),
                'vap' => __('vap'),
                'alpha' => __('alpha'),
                'mp4' => __('mp4'),
                'image' => __('image'),
            ]
        )->rules(function ($form) {
            if ($form->model()->img2) {
                return 'required';
            }

            return 'nullable';
        });
        $form->color('color', trans('color'));
        $form->number('expire', trans('expire(in days)'))->placeholder(trans('0 if permanent'));

        $form->switch('is_active_for_vip', __('active_for_vip'))->states(Common::getSwitchStates());
        $form->number('num', __('num'));

        return $form;
    }
}
