<?php

namespace Modules\DailyPrize\Http\Controllers\web;

use App\Admin\Controllers\MainController;
use Encore\Admin\Facades\Admin;
use App\Models\OVip;
use App\Models\Ware;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Selectables\OVips;
use App\Selectables\Wares;
use Modules\DailyPrize\Entities\DailyGift;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;

class DailyPrizeController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'daily login gift';
    public $permission_name = 'daily-gift';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('daily login gift'))
            ->body($this->grid()));
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
        return parent::show($id,$content
            ->title(trans('daily login gift'))
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
        $id = request()->route('id');
        $model = DailyGift::findOrFail($id);

        $form = $this->form()->edit($id);

        return parent::edit($id,$content
            ->title(trans('daily login gift'))
            ->body($form));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('daily login gift'))
            ->body($this->form()));
    }

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
        // $grid->column('order', __('Order'))->editable();
        $grid->column('order', __('Order'))
            ->display(function ($order) {
                $days = [
                    1 => __('first_day'),
                    2 => __('second_day'),
                    3 => __('third_day'),
                    4 => __('fourth_day'),
                    5 => __('fifth_day'),
                    6 => __('sixth_day'),
                    7 => __('seventh_day'),
                ];
                return $days[$order] ?? $order;
            });

        $grid->column('gift_type', __('gifts'));
        $grid->column('image', __('image'))->display(function ($path) {
            if ($this->gift_type == 'ware') {
                $ware = Ware::find($this->target);
                $path = $ware->img2 ?? $ware?->show_img;
            } elseif ($this->gift_type == 'vip') {
                $vips = OVip::find($this->target);
                $path = $vips?->img;
            } elseif ($this->gift_type == 'achievement') {
                $path = $this->target;
            } else {
                $path = 'coin.png';
            }

            /** @var Gift $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });

        $grid->column('expir', __('expire'));
        Admin::script("
        if (window.innerWidth >= 1024) { // Example threshold for desktop screens
            $('.table-responsive').removeClass('table-responsive');
            }
        ");

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

    public function update($id)
    {
        $id = request()->route('id');
        return $this->form()->update($id);
    }


    protected function form()
    {
        $form = new Form(new DailyGift());
        $typeId = request()->route('type');
        $orderId = request()->route('id');
        $form->hidden('type')->value(request('type'));
        // $form->select('order', __('order'))->options([1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7])->required();
        $form->select('order', __('order'))->options([
            1 => __('first_day'),
            2 => __('second_day'),
            3 => __('third_day'),
            4 => __('fourth_day'),
            5 => __('fifth_day'),
            6 => __('sixth_day'),
            7 => __('seventh_day'),
        ])
            ->rules('required|unique:daily_gifts,order,' . $orderId . ',id,type,' . $typeId);

        $form->select('gift_type', __('Gift type'))
            ->options(["ware" => __('ware'), "vip" => __('vip'), "coins" => __('coins'), "achievement" => __('achievement')])
            ->when("ware", function () use ($form) {
                $form->belongsTo('target1', Wares::class, trans('wares'))->rules('required');
                $form->number('expir', __('expire'));
            })
            ->when("vip", function () use ($form) {
                $form->belongsTo('target2', OVips::class, trans('vips'))->rules('required');
                $form->number('expir', __('expire'));
            })
            ->when("coins", function () use ($form) {
                $form->number("target3", __("coins"))
                    ->rules('required');
            })
            ->when("achievement", function () use ($form) {
                $form->image("target4", __('image'))->name(function ($file) {
                    return now()->timestamp . '.' . $file->guessExtension();
                })->rules('required');
                $form->number('expir', __('expire'));
            })->required();

        $form->saving(function (Form $form) {
            if ($form->gift_type === 'coins') {
                $form->expir = null;
            }
        });
        return $form;
    }

    public function store()
    {
        $data = request()->all();

        $type = request()->route('type');
        $data['type'] = $type;

        // Determine target based on gift_type
        switch ($data['gift_type']) {
            case 'ware':
                $data['target'] = $data['target1'] ?? null;
                break;
            case 'vip':
                $data['target'] = $data['target2'] ?? null;
                break;
            case 'coins':
                $data['target'] = $data['target3'] ?? null;
                $data['expir'] = null; // No expiry for coins
                break;
            case 'achievement':
                if (request()->hasFile('target4')) {
                    $image = request()->file('target4');
                    $filename = now()->timestamp . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('uploads/achievements', $filename, 'public');
                    $data['target'] = $path;
                }
                break;
        }

        // Validate inputs
        $validated = validator($data, [
            'type' => 'required|string',
            'order' => 'required|unique:daily_gifts,order,NULL,id,type,' . $type,
            'gift_type' => 'required|in:ware,vip,coins,achievement',
            'target' => 'required',
        ])->validate();

        // Create the model
        DailyGift::create([
            'type' => $data['type'],
            'order' => $data['order'],
            'gift_type' => $data['gift_type'],
            'target' => $data['target'],
            'expir' => $data['expir'] ?? null,
        ]);

        admin_toastr(__('admin.save_succeeded'));

        return redirect()->route('admin.daily-gifts.index', ['type' => $type]);
    }
}
