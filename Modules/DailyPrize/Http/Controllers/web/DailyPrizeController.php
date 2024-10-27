<?php

namespace Modules\DailyPrize\Http\Controllers\web;

use App\Models\OVip;
use App\Models\Ware;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Modules\DailyPrize\Entities\DailyGift;
use Encore\Admin\Controllers\AdminController;

class DailyPrizeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'DailyGift';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $type = request('type');
        $grid = new Grid(new DailyGift());
        $grid->model()->where('type', $type);
        $grid->column('order', __('Order'))->editable();
        $grid->column('gift_type', __('gifts'));
        $grid->column('image', __('image'))->display(function ($path) {
            if ($this->gift_type == 'ware') {
                $ware = Ware::find($this->target);
                $path = $ware->img2 ?? $ware->show_img ;
            } elseif ($this->gift_type == 'vip') {
                $vips = OVip::find($this->target);
                $path = $vips->img;
            } elseif ($this->gift_type == 'achievement') {
                $path = $this->target;
            } else {
                $path = '';
            }

            /** @var Gift $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });

        $grid->column('expir', __('expire'));

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
        $show = new Show(DailyGift::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('type', __('Type'));
        $show->field('order', __('Order'));
        $show->field('gift_type', __('Gift type'));
        $show->field('target', __('Target'));
        $show->field('expir', __('Expir'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new DailyGift());

        $form->hidden('type')->value(request('type'));
        $form->select('order', __('order'))->options([1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7])->required();
        $form->select('gift_type', __('Gift type'))->options(["ware" => __('ware'), "vip" => __('vip'), "coins" => __('coins'), "achievement" => __('achievement')])
            ->when("ware", function () use ($form) {
                $form->select('target1', trans('wares'))->options(function () {
                    $ops = [0 => ''];
                    $wares = Ware::query()->select(['id', 'name', 'type'])->whereIn('type', [4, 5, 6])->get();
                    foreach ($wares as  $ware) {
                        $ops[$ware->id] = $ware->name . '_' . $ware->id;

                        if ($ware->type == 4) {
                            $ops[$ware->id] .= '_' . 'bubble';
                        } elseif ($ware->type == 5) {
                            $ops[$ware->id] .= '_' . 'intro';
                        } elseif ($ware->type == 6) {
                            $ops[$ware->id] .= '_' . 'frame';
                        }
                    }
                    return $ops;
                });
            })
            ->when("vip", function () use ($form) {
                $form->select('target2', trans('vips'))->options(function () {
                    $vips = OVip::query()->select('id', 'name')->get();
                    foreach ($vips as  $vip) {
                        $ops[$vip->id] = $vip->name;
                    }
                    return $ops;
                });
            })
            ->when("coins", function () use ($form) {
                $form->number("target3", __("coins"));
            })->when("achievement", function () use ($form) {
                $form->image("target4", __('image'))->name(function ($file) {
                    return now()->timestamp . '.' . $file->guessExtension();
                });
            })->required();
        $form->number('expir', __('expire'));

        return $form;
    }
}
