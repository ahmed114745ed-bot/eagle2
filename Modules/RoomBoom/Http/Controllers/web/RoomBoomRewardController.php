<?php

namespace Modules\RoomBoom\Http\Controllers\web;

use App\Admin\Controllers\MainController;
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
use Illuminate\Validation\Rule;
use Modules\RoomBoom\Entities\RoomBoomReward;

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
        $grid->column('image', __('image'))->display(function ($path) {
            if ($this->target_type == 'ware') {
                $ware = Ware::find($this->target);
                $path = $ware->img2 ?? $ware?->show_img;
            } elseif ($this->target_type == 'gift') {
                $gift = Gift::find($this->target);
                $path = $gift->show_img ?? $gift?->img;
            } elseif ($this->target_type == 'achievement') {
                $path = $this?->target;
            } else {
                $path = 'coin.png';
            }
            /** @var Gift $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column('priority', __('priority'));
        $grid->column('quantity', __('Quantity'));
        $grid->column('expire_days', __('expire'));
        $grid->column('created_at', __('Created At'))->display(function ($value) {
            return Carbon::parse($value)->format('Y-m-d');
        });
        if (method_exists($this, 'extendGrid')) {
            $this->extendGrid($grid);
        }

        return $grid;
    }

    protected function detail($id)
    {
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
            ->when("ware", fn() => $this->addWareFields($form, 'ware_'))
            ->when("gift", fn() => $this->addGiftFields($form, 'gift_'))
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

        return $form;
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

    protected function addWareFields($form ,$prefix = '')
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

//            $this->addWareJs();
        });
    }

    protected function addGiftFields($form ,$prefix = '')
    {
        $form->belongsTo('target', Gifts::class, __('Gift'), function ($form) use ($prefix) {
            $form->setElementName($prefix . 'target')
                ->select('id', __('gifts'))
                ->options(function ($id) {
                    if (!$id) return [];
                    $gift = Gift::find($id);
                    return $gift ? [$gift->id => "{$gift->name}_{$gift->id}"] : [];
                })
                ->attribute([
                    'data-image-select' => 1,
                    'data-load-url' => ''
                ]);

            $form->html('<div id="ware-image-preview" style="margin-top:10px;"></div>');
        });
    }

    protected function addAchievementFields($form)
    {
        $form->image("achievement", __('image'))
            ->name(fn($file) => now()->timestamp . '.' . $file->guessExtension())
            ->disk('gcs');
    }
}
