<?php

namespace Utd\Gifts\Http\Controllers\Admin;

use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Widgets\Box;
use Utd\Gifts\Entities\Gift;
use Utd\Gifts\Entities\GiftCategory;
use Utd\Gifts\Entities\LuckyGift;
use Utd\Gifts\Support\ClassResolver;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;

/**
 * GiftController
 */
class GiftController
{
    use HasResourceActions;

    public $permission_name = 'gift';
    protected $title = 'Gifts';

    public function index(Content $content)
    {
        return $content
            ->title(__($this->title))
            ->row(function ($row) {
                $row->column(12, $this->grid());
            });
    }

    protected function grid2()
    {
        $make_rooms_top = settings()->get('close_open_gifts');
        return (new Box(
            title: __('admin.Actions'),
            content: view('admin.grid.users.closeOpenGifts', compact(['make_rooms_top'])),
        ));
    }

    /**
     * Show interface
     */
    public function show($id, Content $content)
    {
        return $content
            ->title(trans('Gifts'))
            ->body($this->detail($id));
    }

    /**
     * Edit interface
     */
    public function edit($id, Content $content)
    {
        if (url()->previous() != url()) {
            session(['return_url' => url()->previous()]);
        }

        return $content
            ->title(trans('Gifts'))
            ->body($this->form($id)->edit($id));
    }

    /**
     * Create interface
     */
    public function create(Content $content)
    {
        return $content
            ->title(trans('Gifts'))
            ->body($this->form());
    }

    /**
     * Make a grid builder
     */
    protected function grid()
    {
        $grid = new Grid(new Gift);
       
        $filterType = request('filter', 'all');
        $category  = [];
        if (request('filter') != 'all') {
            $category = GiftCategory::find(request('filter'));
        }

        $grid->model()
            ->with('vip')
            ->where('type', '!=', 8)
            ->when($filterType !== 'all', fn($q) => $q->where('gift_category_id', $filterType))
            ->orderBy('use_count', 'desc')
            ->orderBy('type')
            ->orderByRaw('ISNULL(`sort`), `sort`')
            ->orderBy('price');

        $grid->paginate(20);

        $grid->header(function () use ($filterType) {
            $locale = App::getLocale();

            // الأساس
            $tabs = ['all' => __('All')];

            // هات كل الكاتيجوري وطلع الترجمة حسب اللغة الحالية
            $categories = GiftCategory::all();
            foreach ($categories as $category) {
                $title = $category->title[$locale] ?? $category->title['en'] ?? '';
                $tabs[$category->id] = $title;
            }

            // بناء HTML
            $html = '<div class="nav-tabs-custom"><ul class="nav nav-tabs">';
            foreach ($tabs as $key => $label) {
                $active = $filterType === (string)$key ? 'active' : '';
                $url = request()->fullUrlWithQuery(['filter' => $key]);
                $html .= "<li class='{$active}'><a href='{$url}'>{$label}</a></li>";
            }
            $html .= '</ul></div>';

            return $html;
        });

        $grid->id(__('ID'));
        $grid->name(__('Name'));

        if ($category && $category->type == 'vip') {
            $grid->column('level', trans('vip'))->display(function () {
                $defaultImage = asset("images/image.png");
                $path = getImagePath($this?->vip?->img);
                $url = $path ?: $defaultImage;
                return handleShowImageWithTypes($this->id, $url, 50, 50);
            });
            $grid->column('vip_level', __('level_num'));
        }

        if (Admin::user()->can('edit_gift_price') || Admin::user()->can('*')) {
            $CommonHelperClass = ClassResolver::helper('common');
            if ($CommonHelperClass) {
                $grid->column('enable', trans('enable'))->switch($CommonHelperClass::getSwitchStates());
            }
        }

        $grid->column('price', __('price'))->display(function ($coin) {
            $icon = asset('images/coin.jpg');
            return "
            <div style='display: flex; align-items: center; gap: 5px;'>
                <span>" . number_format($coin) . "</span>
                <img src='{$icon}' alt='Coin' width='20' height='20'>
            </div>
        ";
        });

        $grid->column('img', trans('image'))->display(function ($path) {
            $imgPath = getImagePath($path) ?: asset("images/image.png");
            $musicIcon = $this->music_gift == 1
                ? "<img src='" . asset('images/music.jpg') . "'
                style='position: absolute; top: 5px; right: 5px; width: 20px; height: 20px;
                background-color: rgba(0, 0, 0, 0.5); border-radius: 50%; padding: 2px;'>"
                : '';

            return "<div style='position: relative; display: inline-block;'>
                    <img src='{$imgPath}' style='width: 70px; height: 70px;' class='img img-thumbnail' />
                    {$musicIcon}
                </div>";
        });

        $grid->column('show_img', trans('show_img'))->display(function ($path) {
            $url = getImagePath($path) ?: asset('images/image.png');
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });

        $grid->column("use_count", __('use count'));

        $grid->disableExport();

        Admin::script("
        if (window.innerWidth >= 1024) {
            $('.table-responsive').removeClass('table-responsive');
        }
    ");
        $permission    = $this->permission_name;
        $grid->actions(function ($actions) use ($permission) {
            $model = $actions->row;

            if ((Admin::user()->can('move-switch-' . $permission) || Admin::user()->can('*'))
                && $model->category?->type != null
            ) {
                $MoveGiftCategoryAction = ClassResolver::action('move_gift_category');
                if ($MoveGiftCategoryAction && class_exists($MoveGiftCategoryAction)) {
                    $actions->add(new $MoveGiftCategoryAction());
                }
            }
        });
        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
            $MoveGroupsGiftsAction = ClassResolver::action('move_groups_gifts');
            if ($MoveGroupsGiftsAction && class_exists($MoveGroupsGiftsAction)) {
                $batch->add(new $MoveGroupsGiftsAction());
            }
        });
        return $grid;
    }

    /**
     * Make a show builder
     */
    protected function detail($id)
    {
        $show = new Show(Gift::findOrFail($id));
        // Extended show fields can be added here
        return $show;
    }

    /**
     * Make a form builder
     */
    protected function form($id = null)
    {
        $TabsFromClass = ClassResolver::form('tabs_from');
        $form = new $TabsFromClass(new Gift);
        
        // Disable default form tools
        $form->tools(function ($tools) {
            $tools->disableDelete();
            $tools->disableView();
        });
        
        $type = old('type', $form->model()->type ?? null);

        $form->display(__('ID'));
        $form->text('name', __('name'));

        // Build Gift Category options
        $categories = GiftCategory::all();
        $locale = App::getLocale();

        $form->html(view('admin.gift_type', [
            'categories' => $categories,
            'locale' => $locale,
            'model' => $id ? Gift::find($id) : [],
        ]));

        $form->currency('price', __('price'))->symbol('💎');
        $CommonHelperClass = ClassResolver::helper('common');
        if ($CommonHelperClass) {
            $form->switch('enable', __('enable'))->states($CommonHelperClass::getSwitchStates());
        }

        $form->file('img', __('img'));
        $form->file('show_img', __('show_img'))->name(function ($file) {
            return 'svga_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
        })->required();
        $form->select('image_type', __('image_type'))->options(
            [
                'svga' => __('svga'),
                'alpha' => __('alpha'),
                'mp4' => __('mp4'),
                'vap' => __('vap'),
            ]
        )->required();

        $CommonHelperClass = ClassResolver::helper('common');
        if ($CommonHelperClass) {
            $form->switch('music_gift', trans('music_gift'))->states($CommonHelperClass::getSwitchStatesGiftMucic());
        }
        
        // Before saving, handle validations and model fields
        $form->saving(function (Form $form) {
            if (request()->has('_edit_inline')) return;

            $categoryId = $form->input('gift_category_id');
            $category = GiftCategory::find($categoryId);
            $type = $category?->type;

            $form->model()->gift_category_id = $categoryId;

            if ($type === 'lucky_gift') {
                $minPercentag = (float) ($form->input('luckyGift.min_percentag') ?? 0);
                $midPercentag = (float) ($form->input('luckyGift.mid_percentag') ?? 0);
                $maxPercentag = (float) ($form->input('luckyGift.max_percentag') ?? 0);
                $total = $minPercentag + $midPercentag + $maxPercentag;

                if ($total != 100) {
                    $error = new \Illuminate\Support\MessageBag([
                        'title' => 'Error',
                        'message' => trans('admin.percent_total_error', ['total' => $total]),
                    ]);
                    return back()->with(compact('error'))->withInput();
                }

                // Save these to the form instance for use in saved()
                $form->winProbability = $form->input('luckyGift.win_probability') ?? 0;
                $form->percentagesString = implode(',', [$minPercentag, $midPercentag, $maxPercentag]);
            }

            if ($type === 'vip') {
                $form->model()->vip_level = $form->input('vip_level') ?? null;
            }
        });

        // After saving, create or update LuckyGift
        $form->saved(function (Form $form) {
            $categoryId = $form->model()->gift_category_id;
            $category = GiftCategory::find($categoryId);
            $type = $category?->type;

            if ($type === 'lucky_gift') {
                LuckyGift::updateOrCreate(
                    ['gift_id' => $form->model()->id],
                    [
                        'win_probability' => $form->winProbability,
                        'min_percentage' => $form->percentagesString,
                    ]
                );
            }
        });

        return $form;
    }

    public function luckyGiftSettings(Content $content)
    {
        if (!Admin::user()->can('*')) {
            Permission::check('browse-lucky-gift-setting');
        }
        $SettingModel = ClassResolver::model('setting');
        $config = $SettingModel::whereIn('key', ['app_wallet_lucky_gift', 'owner_lucky_gift', 'lucky_gift_coins','host_lucky_gift'])->pluck('value', 'key')->toArray();
        return $content->view('lucky_gift', compact('config'));
    }
}
