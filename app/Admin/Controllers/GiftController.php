<?php

namespace App\Admin\Controllers;

use App\Models\Gift;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\LuckyGift;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use App\Models\GiftCategory;
use Illuminate\Support\MessageBag;
use App\Admin\Forms\TabsFrom;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\App;
use App\Admin\Actions\MoveGiftCategory;
use Illuminate\Validation\ValidationException;
use App\Admin\Actions\Grid\MoveGroupsGifts;
use App\Models\Setting;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Auth\Permission;
use App\Admin\Services\FileService;

class GiftController extends MainController
{
    use HasResourceActions;

    public $permission_name = 'gift';
    public function index(Content $content)
    {
        return parent::index($content
            ->title(__($this->title))
            // ->row(function (Row $row) {
            //     $row->column(12, $this->grid2());
            // })
            ->row(function ($row) {
                $row->column(12, $this->grid());
            }));
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
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(trans('Gifts'))
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

        if (url()->previous() != url()) {
            session(['return_url' => url()->previous()]);
        }

        return parent::edit($id, $content
            ->title(trans('Gifts'))
            ->body($this->form($id)->edit($id)));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('Gifts'))
            ->body($this->form()));
    }



    /**
     * Make a grid builder.
     *
     * @return Grid
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
            // ->orderByDesc('enable')   // 1️⃣ enabled first
            // ->orderBy('sort', 'asc');
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
            $categories = GiftCategory::orderBy('sort', 'asc')->get();
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
            $grid->column('enable', trans('enable'))->switch(Common::getSwitchStates());
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


        $this->extendGrid($grid);

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
                $actions->add(new MoveGiftCategory());
            }
        });
        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
            $batch->add(new MoveGroupsGifts());
        });
        if (request('filter')) {
            $grid->tools(function ($tools) {
                $tools->append('<a href="' . admin_url('gifts/' . request('filter') . '/create') . '" class="btn btn-sm btn-default">' . __('create') . '</a>');
            });
        }

        $grid->disableCreateButton();

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
        $show = new Show(Gift::findOrFail($id));

        $this->extendShow($show);
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id = null)
    {

        $form = new TabsFrom(new Gift);
        $this->disableFormTools($form);
        $type = old('type', $form->model()->type ?? null);

        $form->display(__('ID'));
        $form->text('name', __('name'));


        $selectedCategoryId = request('type') ?? $form->model()->gift_category_id;
        $categories = GiftCategory::where('id', $selectedCategoryId)->get();
          dd($categories
          
          ,request('type') , $form->model()->gift_category_id);
        $locale = App::getLocale();

        $form->html(view('admin.gift_type', [
            'categories' => $categories,
            'locale' => $locale,
            'model' => $id ? Gift::find($id) : [],
        ]));



        $form->currency('price', __('price'))->symbol('💎');
        $form->switch('enable', __('enable'))->states(Common::getSwitchStates());


        $form->file('img', __('img'))->name(function ($file) {
            $extension = $file->getClientOriginalExtension();
            if (empty($extension)) {
                $extension = $file->guessExtension();
            }
            return 'img_' . now()->timestamp . '_' . rand(100, 999) . '.' . $extension;
        }) ->default('1.png');
       

        $form->file('show_img', __('show_img'))->name(function ($file) {
            $extension = $file->getClientOriginalExtension();
            if (empty($extension)) {
                $extension = $file->guessExtension();
            }

            $extension = strtolower($extension);
            if ($extension === 'svg') {
                return 'svga_' . Str::random(8) . '.svg';
            }

            return 'animation_' . Str::random(8) . '.' . $extension;
        })->required();
        $form->select('image_type', __('image_type'))->options(
            [
                'svga' => __('svga'),
                'alpha' => __('alpha'),
                'mp4' => __('mp4'),
                'vap' => __('vap'),
                'png' => __('image:(jpg, jpeg, png,gif, bmp, tiff, svg, webp, mov, avi, wmv, flv, mkv, webm)'),

            ]
        )->required();

        $form->switch('music_gift', trans('music_gift'))->states(Common::getSwitchStatesGiftMucic());


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

        $form->saving(function (Form $form) {
            $hasShowImg = $form->show_img || $form->model()->show_img;
            $img2 = $form->img;
            $wareId = $form->model()->id;



            $hasImg2 = $img2 || $form->model()->img;

            if (!$hasShowImg && !$hasImg2) {
                $error = new MessageBag([
                    'title'   => 'Error',
                    'message' => 'Please upload at least one image',
                ]);
                return back()->with(compact('error'));
            }

            if ($form->img instanceof UploadedFile) {
                $allowedExtensions = ['svga', 'mp4', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'svg', 'webp', 'mov', 'avi', 'wmv', 'flv', 'mkv', 'webm'];

                // الحصول على الامتداد الحقيقي
                $originalExt = strtolower($form->img->getClientOriginalExtension());
                $guessedExt = strtolower($form->img->guessExtension());

                // إعطاء الأولوية للامتداد الأصلي
                $ext = !empty($originalExt) ? $originalExt : $guessedExt;



                if (!in_array($ext, $allowedExtensions)) {
                    throw ValidationException::withMessages([
                        'img' => ['Invalid file type. Allowed extensions are: ' . implode(', ', $allowedExtensions)],
                    ]);
                }

                $form->image_type = $ext;
            }

            // معالجة img2 - الحل الرئيسي للمشكلة
            if ($hasShowImg instanceof UploadedFile) {
                /** @var FileService $fileService*/
                $fileService = app(FileService::class);
                $ext = $fileService->getExtension($hasShowImg, $wareId, getFromService: true);

                // $form->input('detected_profile_frame_type', $ext);
                $form->image_type = $ext;
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

        $form->saved(function (Form $form) {
            $url = url('admin/gifts?filter=' . $form->model()->gift_category_id);
            return redirect()->to($url);
        });
        return $form;
    }

    public function luckyGiftSettings(Content $content)
    {
        if (!Admin::user()->can('*')) {
            Permission::check('browse-' . 'lucky-gift-setting');
        }
        $config = Setting::whereIn('key', ['app_wallet_lucky_gift', 'owner_lucky_gift', 'lucky_gift_coins', 'host_lucky_gift'])->pluck('value', 'key')->toArray();
        return $content->view('lucky_gift', compact('config'));
    }
}
