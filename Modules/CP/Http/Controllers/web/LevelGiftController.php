<?php

namespace Modules\CP\Http\Controllers\web;

use App\Helpers\Common;
use App\Models\OVip;
use App\Models\Ware;
use App\Selectables\OVips;
use App\Selectables\Wares;
use App\Selectables\WaresByType;
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

                return  self::renderWareWithImage($this?->ware);

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
    
        $form->select('type', trans('type'))
            ->options([
                "ware" => __('ware'),
                "vip" => __('vip'),
                "coins" => __('coins'),
                "achievement" => __('achievement')
            ])
            ->when("ware", fn() => $this->addWareFields($form, 'ware_'))
            ->when("vip", fn() => $this->addVipFields($form, 'vip_'))
            ->when("coins", fn() => $this->addCoinsFields($form))
            ->when("achievement", fn() => $this->addAchievementFields($form));
    
        $form->number('expire', __('expire'));
        $form->select('gender', __('gender'))->options([
            'all' => __('all'),
            'male' => __('Male'),
            'female' => __('Female')
        ])->required();
    
        $form->saving(fn($form) => $this->handleSaving($form));
    
        return $form;
    }
    
    protected function addVipFields($form ,$prefix = '')
    {
        $form->belongsTo($prefix.'item_id', OVips::class, __('vips'));
    }

    protected function addWareFields($form ,$prefix = '')
    {
        $form->select('type_ware', __('Type wares'))
            ->options(getTranslatedUsedWare())
            ->load('items.item_id', admin_url('wares-by-type'), ['type_ware']);

        $form->belongsTo($prefix.'item_id', WaresByType::class, __('Ware'), function ($form) {
            $form->select('id', __('wares'))
                ->options(function ($id) {
                    if (!$id) return [];
                    $ware = Ware::find($id);
                    return $ware ? [$ware->id => "{$ware->name}_{$ware->id}"] : [];
                })
                ->attribute([
                    'data-image-select' => 1,
                    'data-load-url' => admin_url('wares-by-id') 
                ]);

            $form->html('<div id="ware-image-preview" style="margin-top:10px;"></div>');

            $this->addWareJs();
        });

        $form->hidden('sub_type');
    }

 
    protected function addCoinsFields($form)
    {
        $form->number("coins", __("coins"))
            ->default(fn($form) => $form->model()->type === 'coins'
                ? (int) $form->model()->item_id
                : null
            );
    }

    protected function addAchievementFields($form)
    {
        $form->image("achievement", __('image'))
            ->name(fn($file) => now()->timestamp . '.' . $file->guessExtension())
            ->disk('gcs');
    }
    protected function addWareJs()
    {
        \Encore\Admin\Admin::script(<<<'JS'
            function formatWithImage(option) {
                if (!option.id) return option.text;
                let img = option.image 
                    ? `<img src="${option.image}" style="width:130px;height:100px;border-radius:4px;margin-right:6px;">` 
                    : '';
                return $(`<span>${img}${option.text}</span>`);
            }

            let $select = $('select[data-image-select]');

            $select.select2({
                ajax: {
                    delay: 250,
                    url: $select.data('load-url'),
                    data: function(params) {
                        return { q: params.term };
                    },
                    processResults: function (data) {
                        return { results: data };
                    }
                },
                templateResult: formatWithImage,
                templateSelection: formatWithImage,
                escapeMarkup: function (m) { return m; }
            });

            $select.on('select2:select', function (e) {
                let data = e.params.data;
                $('#ware-image-preview').html(
                    data.image
                        ? `<img src="${data.image}" style="max-width:150px;max-height:150px;border:1px solid #ccc;border-radius:4px;">`
                        : ''
                );
            });

            let initialId = $select.val();
            if (initialId) {
                $.getJSON($select.data('load-url'), { id: initialId }, function (data) {
                    if (data && data.length > 0) {
                        let item = data[0];
                        let option = new Option(item.text, item.id, true, true);
                        $select.append(option).trigger('change');
                        if (item.image) {
                            $('#ware-image-preview').html(
                                `<img src="${item.image}" style="max-width:150px;max-height:150px;border:1px solid #ccc;border-radius:4px;">`
                            );
                        }
                    }
                });
            }
        JS);
    }

    protected function handleSaving($form)
    {

        if ($form->ware_item_id) {
            $form->item_id = intval($form->ware_item_id);
            $form->model()->item_id =  intval($form->ware_item_id);

        }

        if ($form->vip_item_id) {
            $form->item_id = intval($form->vip_item_id);
            $form->model()->item_id =  intval($form->vip_item_id);

        }
        unset($form->type_ware);
        $form->ignore('type_ware');
        if ($form->type == 'ware') {
            $ware = Ware::find($form->item_id);
            if ($ware) {
                if ($ware->type == 4) $form->sub_type = 'bubble';
                elseif ($ware->type == 5) $form->sub_type = 'intro';
                elseif ($ware->type == 6) $form->sub_type = 'frame';
            }
        } elseif ($form->type == 'coins') {
            $form->item_id = $form->coins;
        } elseif ($form->type == 'achievement') {
            if ($form->achievement instanceof UploadedFile) {
                $url = Common::upload('cp', $form->achievement);
            }
            $form->item_id = $url ?? '';
        }

        

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

    public static function renderWareWithImage($ware)
    {
        $imageUrl = @$ware?->show_img;

        if ($imageUrl) {
            return sprintf(
                '<div style="display: flex; align-items: center; gap: 8px;">
                    <img src="%s" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd;">
                    <span style="font-size: 14px; color: ;">%s</span>
                </div>',
                getImagePath($imageUrl),
                e(@$ware?->name)
            );
        }

        return sprintf(
            '<span style="font-size: 14px; color:;">%s</span>',
            e(@$ware?->name)
        );
    }

}



