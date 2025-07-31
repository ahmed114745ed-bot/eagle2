<?php

namespace Modules\CP\Http\Controllers\web;

use Modules\Vip\Entities\Vip;
use Modules\Vip\Entities\OVip;
use App\Models\Ware;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Illuminate\Http\Request;
use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;
use Encore\Admin\Controllers\HasResourceActions;
use Modules\CP\Entities\CpLevel;
use Modules\CP\Entities\CpLevelGift;
use Encore\Admin\Admin;


class LevelGiftController extends MainController
{
    use HasResourceActions;
    // public function __construct()
    // {
    //     (new AppFeatureService)->validateStatusEnable("target_events");
    // }
    public function index(Content $content)
    {
        return $content
            ->header(trans('admin.index'))
            ->description(trans('admin.description'))
            ->body($this->grid());
    }
    public function create(Content $content)
    {
        return $content
            ->header(trans('admin.create'))
            ->description(trans('admin.description'))
            ->body($this->form());
    }

    public function update($id)
    {
        $id = request()->route('id');
        return $this->form()->update($id);
    }

    // public function edit($id, Content $content)
    // {
    //     $id = request()->route('id');
    //     return $content
    //         ->header(trans('admin.edit'))
    //         ->description(trans('admin.description'))
    //         ->body($this->form()->edit($id));
    // }

    public function edit($id, Content $content)
    {
        $id = request()->route('id');
        // العثور على النموذج بناءً على المعرف
        $model = CpLevelGift::findOrFail($id);

        // تحميل النموذج
        $form = $this->form()->edit($id);

        // تعبئة حقل coins بالقيمة الموجودة في item_id إذا كان النوع "coins"
        if ($model->type == 'coins') {
            $form->coins = (int) $model->item_id; // تعيين قيمة coins
        }

        return $content
            ->header(trans('admin.edit'))
            ->description(trans('admin.description'))
            ->body($form);
    }

    public function show($id, Content $content)
    {
        return $content
            ->header(trans('admin.detail'))
            ->description(trans('admin.description'))
            ->body($this->detail($id));
    }
    protected function grid()
    {

        $charge_event_id = request('cp_level_id');
        $vip = CpLevel::query()->find($charge_event_id);
        $grid = new Grid(new CpLevelGift());
        $grid->disableRowSelector();
        $grid->column('created_at')->hide();
        $grid->model()->where("vip_id", $charge_event_id);

        $grid->column('id', __('Id'));
        $grid->column('type', __('Type'));
        $grid->column('gift_id', __('gifts'))->display(function () {
            if ($this?->type == "ware") {
                return @$this?->ware?->name;
            } elseif ($this?->type == "vip") {
                return @$this?->vip?->name;
            } elseif ($this?->type == "coins") {
                return @$this?->item_id;
            } elseif ($this?->type == "achievement") {
                $value = getDriverUrl() . '/' . @$this?->item_id;
                return "<img src='$value' width='80' height='80'>";
            }
        });
        $grid->column('created_at', __('Created at'));

        $grid->tools(function (Grid\Tools $tools) use ($vip, $charge_event_id) {
            $url = url('admin/cp-levels/' . $charge_event_id);
            $customButtonHTML = <<<HTML
                     <div style="display: contents; align-items: center;">
                        <a href="{$url}" class="btn btn-sm btn-info" style="margin-right: 10px;">
                            <i class="fa fa-arrow-left"></i> الرجوع إلى levels
                        </a>
                        <label style="margin: 0;">هدايه الخاصه ب : {$vip->level} </label>
                    </div>
                HTML;
            $tools->append($customButtonHTML);
        });

        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        return $grid;
    }

    protected function form()
    {
        $form = new Form(new CpLevelGift());

        $form->hidden('vip_id')->value(request('cp_level_id'));

        $form->select('type', trans('type'))->options([
            "ware" => __('ware'),
            "vip" => __('vip'),
            "coins" => __('coins'),
            "achievement" => __('achievement')
        ])->when("ware", function () use ($form) {
            $form->select('type_ware', trans('type wares'))
                ->options(getTranslatedUsedWare())
                ->load('item_id', admin_url('wares-by-type')); // AJAX load

            $form->select('item_id', __('wares'))
                ->options([]) // loaded via ->load()
                ->attribute(['data-image-select' => 1]);


            Admin::script(<<<'JS'
                $(function () {
                    function formatWithImage(option) {
                        if (!option.id) return option.text;

                        let img = option.image
                            ? `<img src="${option.image}" style="width:30px;height:30px;border-radius:4px;margin-right:6px;">`
                            : '';
                        return $(`<span>${img}${option.text}</span>`);
                    }

                    $('select[data-image-select]').each(function () {
                        $(this).select2({
                            templateResult: formatWithImage,
                            templateSelection: formatWithImage,
                            escapeMarkup: function (m) { return m; }
                        });
                    });
                });
                JS);


            $form->hidden('sub_type');
        })
            ->when("vip", function () use ($form) {
                $form->select('item_id', trans('vips'))->options(function () {
                    $vips = OVip::query()->select('id', 'name')->get();
                    $ops = [];
                    foreach ($vips as $vip) {
                        $ops[$vip->id] = $vip->name;
                    }
                    return $ops;
                });
            })
            ->when("coins", function () use ($form) {
                $form->number("coins", __("coins"));
            })
            ->when("achievement", function () use ($form) {
                $form->image("achievement", __('image'))->name(function ($file) {
                    return now()->timestamp . '.' . $file->guessExtension();
                })->disk('gcs');
            });

        $form->number('expire', __('expire'));
        $form->select('gender', __('gender'))->options([
            'all' => __('all'),
            'male' => __('Male'),
            'female' => __('Female')
        ])->required();

        $form->saving(function (Form $form) {
            unset($form->type_ware);
            if ($form->type == 'ware') {
                $ware = Ware::find($form->item_id);
                if ($ware) {
                    if ($ware?->type == 4) {
                        $form->sub_type = 'bubble';
                    } elseif ($ware?->type == 5) {
                        $form->sub_type = 'intro';
                    } elseif ($ware?->type == 6) {
                        $form->sub_type = 'frame';
                    }
                }
            } elseif ($form->type == 'vip') {
            } elseif ($form->type == 'coins') {
                $form->item_id = $form->coins;
            } elseif ($form->type == 'achievement') {
                $form->item_id = $form->achievement;
            }
        });


        return $form;
    }

    public function getWaresByType(Request $request)
    {
        $type = $request->get('q');
        $wares = Ware::where('type', $type)->get();

        $data = [];
        foreach ($wares as $ware) {
            $text = "{$ware->name}_{$ware->id}";

            $data[] = [
                'id'   => $ware->id,
                'text' => $text,
                'image' =>  getImagePath($ware->show_img),
            ];
        }

        return response()->json($data);
    }
}
