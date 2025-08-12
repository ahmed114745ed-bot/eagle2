<?php

namespace Modules\CP\Http\Controllers\web;

use App\Helpers\Common;
use App\Models\OVip;
use App\Models\Ware;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Illuminate\Http\Request;
use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;
use Encore\Admin\Controllers\HasResourceActions;
use Illuminate\Http\UploadedFile;
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
        $model = CpLevelGift::findOrFail($id);
        $form = $this->form()->edit($id);

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
            $url = url('admin/cp-levels/' . $vip->cp_relation_id);
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
                ->options(function ($id) {
                    if (!$id) return [];

                    $ware = Ware::find($id);
                    if (!$ware) return [];

                    return [$ware->id => "{$ware->name}_{$ware->id}"];
                })
                ->attribute(['data-image-select' => 1]);
                $form->html('<div id="ware-image-preview" style="margin-top:10px;"></div>');

                Admin::script(<<<'JS'
                    $(function () {
                        function formatWithImage(option) {
                            if (!option.id) return option.text;
                            let img = option.image ? `<img src="${option.image}" style="width:30px;height:30px;border-radius:4px;margin-right:6px;">` : '';
                            return $(`<span>${img}${option.text}</span>`);
                        }
            
                        let $itemSelect = $('select[data-image-select]');
            
                        $itemSelect.select2({
                            ajax: {
                                delay: 250,
                                url: $('select[name="type_ware"]').data('load-url') || $('select[name="type_ware"]').attr('data-load-url'),
                                data: function(params) {
                                    return {
                                        type_ware: $('select[name="type_ware"]').val(),
                                        q: params.term
                                    };
                                },
                                processResults: function (data) {
                                    return { results: data };
                                }
                            },
                            templateResult: formatWithImage,
                            templateSelection: formatWithImage,
                            escapeMarkup: function (m) { return m; }
                        });
            
                        $itemSelect.on('select2:select', function (e) {
                            let data = e.params.data;
                            
                            $('#ware-image-preview').html(
                                data.image
                                    ? `<img src="${data.image}" style="max-width:150px;max-height:150px;border:1px solid #ccc;border-radius:4px;">`
                                    : ''
                            );
                        });
            
                        // إذا فيه قيمة محفوظة، نحمل بياناتها ونظهر المعاينة
                        let initialId = $itemSelect.val();
                        if (initialId) {
                            $.getJSON($itemSelect.data('load-url') || $itemSelect.attr('data-load-url'), { id: initialId }, function (data) {
                                if (data && data.length > 0) {
                                    let item = data[0];
                                    let option = new Option(item.text, item.id, true, true);
                                    $itemSelect.append(option).trigger('change');
                                    if (item.image) {
                                        $('#ware-image-preview').html(`<img src="${item.image}" style="max-width:150px;max-height:150px;border:1px solid #ccc;border-radius:4px;">`);
                                    }
                                }
                            });
                        }
                    });
                JS);
            
                $form->hidden('sub_type');
            })
            ->when("vip", function () use ($form) {
                $form->select('item_id', trans('vips'))
                    ->options(OVip::pluck('name', 'id'));
//                $form->select('item_id', trans('vips'))
//                    ->options(function ($id) {
//                        if (!$id) return [];
//                        $vip = OVip::find($id);
//                        if (!$vip) return [];
//                        return [$vip->id => $vip->name];
//                    })
//                    ->load('item_id', admin_url('vips-by-type'));
            })
            ->when("coins", function () use ($form) {
                $form->number("coins", __("coins"))
                    ->default(function ($form) {
                        return $form->model()->type === 'coins' ? (int) $form->model()->item_id : null;
                    });
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
            $form->ignore('type_ware');

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

                if ($form->achievement instanceof UploadedFile) {
                    $url = Common::upload('cp', $form->achievement);
                }
                $form->item_id = $url ?? '';
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

    public function getVipsByType(Request $request)
    {
        $q = $request->get('q');
        // If you want to filter by $q, add where clauses.
        $vips = OVip::select('id', 'name')->get();

        $data = [];
        foreach ($vips as $vip) {
            $data[] = [
                'id'   => $vip->id,
                'text' => $vip->name,
            ];
        }

        return response()->json($data);
    }
}
