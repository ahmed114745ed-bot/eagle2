<?php

namespace Modules\RoomBoom\Http\Controllers\web;

use App\Admin\Controllers\MainController;
use App\Helpers\Common;
use App\Models\Gift;
use App\Models\Ware;
use App\Selectables\Gifts;
use App\Selectables\Wares;
use App\Selectables\WaresByType;
use Carbon\Carbon;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Modules\RoomBoom\Entities\RoomBoomLevel;
use Modules\RoomBoom\Entities\RoomBoomReward;
use Encore\Admin\Admin;
use Illuminate\Http\Request;
class RoomBoomRewardController extends MainController
{
    public $permission_name = 'room-boom-rewards';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(__('Room Boom Rewards'))
            ->body($this->grid()));
    }

    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(__('Room Boom Reward'))
            ->body($this->detail($id)));
    }

    public function edit($id, Content $content)
    {
        $id = request()->route('id');
        return parent::edit($id, $content
            ->title(__('Edit Room Boom Reward'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(__('Create Room Boom Reward'))
            ->body($this->form()));
    }

    protected function grid()
    {
        $grid = new Grid(new RoomBoomReward());

        $roomBoomLevelId = request('room_boom_level_id');
        $grid->model()->where('room_boom_level_id', $roomBoomLevelId);

        $grid->column('id', __('ID'))->sortable();
        $grid->column('target_type', __('target type'));
        $grid->column('target', __('target'))->display(function () {
            if ($this->target_type == "ware") {
                return @$this->ware->name;
            } elseif ($this->target_type == "gift") {
                return @$this->gift->name;
            } elseif ($this->target_type == "achievement") {
                $value = getDriverUrl() . '/' . @$this->target;
                return "<img src='$value' width='80' height='80'>";
            }
        });
        if (!request()->filled('_export_')) {
            $grid->column('image', __('image'))->display(function ($path) {
                if ($this->target_type == 'ware') {
                    $ware = Ware::find($this->target);
                    $path = $ware->img2 ?? $ware?->show_img;
                } elseif ($this->target_type == 'gift') {
                    $gift = Gift::find($this->target);
                    $path = $gift->show_img ?? $gift?->img;
                } elseif ($this->target_type == 'achievement') {
                    $value = getDriverUrl() . '/' . @$this?->target;
                    return "<img src='$value' width='80' height='80'>";
                } else {
                    $path = 'coin.png';
                }
                /** @var Gift $this */
                $url = getImagePath($path);
                return handleShowImageWithTypes($this->id, $url, 50, 50);
            });
        }
        $grid->column('priority', __('priority'));
        $grid->column('quantity', __('Quantity'));
        $grid->column('expire_days', __('expire'));
        $grid->column('created_at', __('Created At'))->display(function ($value) {
            return Carbon::parse($value)->format('Y-m-d');
        });
        if (method_exists($this, 'extendGrid')) {
            $this->extendGrid($grid);
        }

        $grid->tools(function (Grid\Tools $tools) {
            $label = __('Back');
            $url   = admin_url('room_boom_levels');

            $tools->append(
                <<<HTML
                    <a href="{$url}" class="btn btn-sm btn-info" style="margin-right: 10px;">
                        <i class="fa fa-arrow-left"></i> {$label}
                    </a>
                HTML
            );
        });

        return $grid;
    }

    protected function detail($id)
    {
        $id = request()->route('id');
        $show = new Show(RoomBoomReward::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('target_type', __('Target Type'));
        $show->field('target', __('target'));
        $show->field('priority', __('priority'));
        $show->field('quantity', __('Quantity'));
        $show->field('expire_days', __('expire'));
        $show->column('created_at', __('Created At'))->display(function ($value) {
            return Carbon::parse($value)->format('Y-m-d');
        });
        $show->column('updated_at', __('Updated At'))->display(function ($value) {
            return Carbon::parse($value)->format('Y-m-d');
        });

        return $show;
    }

    protected function form()
    {
        $form = new Form(new RoomBoomReward());

        $roomBoomLevelId = request('room_boom_level_id');
        $form->hidden('room_boom_level_id')->default($roomBoomLevelId);

        $form->saving(function (Form $form) use ($roomBoomLevelId) {
            if (!$form->model()->exists) {
                $count = RoomBoomReward::where('room_boom_level_id', $roomBoomLevelId)->count();
                if ($count >= 7) {
                    admin_error(__('You can only have a maximum of 7 gifts for this Room Boom Level.'));
                    return back();
                }
            }
        });

        $form->select('target_type', trans('Target Type'))->options([
            "ware" => __('ware'),
            "gift" => __('gift'),
            "achievement" => __('achievement')
        ])
        ->when("ware", fn() => $this->addWareFields($form))
        ->when("gift", fn() => $this->addGiftFields($form))
        ->when("achievement", fn() => $this->addAchievementFields($form));

        $form->number('priority', __('priority'))
            ->rules(function () use ($roomBoomLevelId, $form) {
                return [
                    'required',
                    'integer',
                    'min:1',
                    Rule::unique('room_boom_rewards', 'priority')
                        ->where('room_boom_level_id', $roomBoomLevelId)
                        ->ignore($form->model()->id)
                ];
            });
        $form->number('quantity', __('Quantity'))->rules('required|integer|min:1');
        $form->number('expire_days', __('expire'));

        $form->saving(function (Form $form) {
            switch ($form->target_type) {
                case 'ware':
                    $form->model()->target = $form->ware_target_id;
                    break;
                case 'gift':
                    $form->model()->target = $form->gift_target_id;
                    break;
                case 'achievement':
                    if ($form->achievement_target instanceof UploadedFile) {
                        $url = Common::upload('roomBoom', $form->achievement_target);
                    }
                    $form->model()->target = $url ?? '';
                    $form->target = $url ?? '';
                    break;
            }

            unset($form->ware_target_id);
            unset($form->gift_target_id);
        });




        \Log::info('Form object:', [$form]);

        return $form;
    }


    protected function addWareFields($form ,$prefix = 'ware_')
    {
        $form->belongsTo('target', WaresByType::class, __('Ware'), function ($form) use ($prefix) {
            $form->setElementName($prefix . 'target')
                ->select('id', __('wares'))
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

    }
    protected function addGiftFields($form, $prefix = 'gift_'): void
    {
        $fieldName = $prefix . 'id';

        $form->select('target', __('Gift'))
            ->options(function ($id) {
                $query = Gift::query()->pluck('name', 'id');

                if ($id) {
                    $gift = Gift::find($id);
                    if ($gift && !$query->has($gift->id)) {
                        $query[$gift->id] = "{$gift->name}_{$gift->id}";
                    }
                }
                return Gift::pluck('name', 'id');
            })
            ->attribute([
                'data-image-select' => 1,
                'data-load-url'     => admin_url('gifts-by-id'),
            ]);
        $form->html('<div id="gift-image-preview" style="margin-top:10px;"></div>');

        $this->addGiftJs($fieldName, 'gift-image-preview');
    }


    protected function addGiftJs(string $fieldName = 'gift_id', string $previewId = 'gift-image-preview'): void
    {
        $script = <<<'JS'
    (function () {
        var select = $('select[name="{{fieldName}}"]');
        var preview = $('#{{previewId}}');

        function updatePreview(id) {
            var url = select.data('load-url');
            if (!url || !id) { preview.empty(); return; }

            $.get(url, { id: id }, function (res) {
                var img  = (res && (res.image || (res.data && res.data.image))) ? (res.image || res.data.image) : null;
                var name = (res && (res.name  || (res.data && res.data.name ))) ? (res.name  || res.data.name)  : '';
                if (img) {
                    preview.html(
                      '<div style="margin-top:8px">' +
                      '<img src="'+ img +'" style="max-width:160px;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.1)"/>' +
                      '<div style="margin-top:6px;font-size:12px">'+ name +'</div>' +
                      '</div>'
                    );
                } else {
                    preview.html('<small class="text-muted">لا توجد صورة</small>');
                }
            });
        }
        select.on('change', function () { updatePreview($(this).val()); });

        if (select.val()) updatePreview(select.val());
    })();
    JS;

        $script = str_replace(['{{fieldName}}', '{{previewId}}'], [$fieldName, $previewId], $script);

        Admin::script($script);
    }



    protected function addAchievementFields($form): void
    {
        $form->image("achievement_target", __('image'))
            ->name(function ($file) {
                if ($file instanceof UploadedFile) {
                    return now()->timestamp . '.' . $file->guessExtension();
                }

                return $file;
            })
            ->disk('gcs');
    }

    public function store()
    {
        $form = $this->form();

        $form->saved(function (Form $form) {
            $roomBoomLevelId = $form->model()->room_boom_level_id;
            admin_toastr(__('Created successfully'));
            return redirect()->to('admin/room_boom_rewards/' . $roomBoomLevelId);
        });

        return $form->store();
    }

    public function update($id)
    {
        $id = request()->route('id');
        $form = $this->form()->edit($id);

        $form->saved(function (Form $form) {
            $roomBoomLevelId = $form->model()->room_boom_level_id;
            admin_toastr(__('Updated successfully'));
            return redirect()->to('admin/room_boom_rewards/' . $roomBoomLevelId);
        });

        return $form->update($id);
    }

    public function destroy($id)
    {
        $reward = RoomBoomReward::findOrFail($id);
        $roomBoomLevelId = $reward->room_boom_level_id;
        $reward->delete();

        admin_toastr(__('Deleted successfully'));

        return [
            'status' => true,
            'message' => __('Deleted successfully'),
            'redirect' => admin_url('room_boom_rewards?room_boom_level_id=' . $roomBoomLevelId),
        ];
    }


}
