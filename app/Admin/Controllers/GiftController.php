<?php

namespace App\Admin\Controllers;

use App\Models\Gift;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\LuckyGift;
use Illuminate\Http\Request;
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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        $gift = Gift::with(['category', 'luckyGift', 'vip'])->findOrFail($id);

        return parent::show($id, $content
            ->title(trans('Gifts'))
            ->body(view('admin.gift_detail', compact('gift'))));
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
        $category = [];
        if (request('filter') != 'all') {
            $category = GiftCategory::find(request('filter'));
        }

        $grid->model()
            ->with(['vip', 'category'])
            ->where('type', '!=', 8)
            ->when($filterType !== 'all', fn($q) => $q->where('gift_category_id', $filterType))
            ->orderByRaw('ISNULL(`sort`), `sort` ASC')
            ->orderBy('use_count', 'desc')
            ->orderBy('price');

        // Enable drag-drop sorting only when filtering by category
        if ($filterType !== 'all') {
            $grid->sortable();
        }

        $grid->paginate(20);
        $grid->header(function () use ($filterType) {
            $locale = App::getLocale();

            // الأساس
            $tabs = ['all' => __('All')];

            // هات كل الكاتيجوري وطلع الترجمة حسب اللغة الحالية
            $categories = GiftCategory::select('id', 'title', 'sort')->orderBy('sort', 'asc')->get();
            foreach ($categories as $category) {
                $title = $category->title[$locale] ?? $category->title['en'] ?? '';
                $tabs[$category->id] = $title;
            }

            // بناء HTML
            $html = '<div class="nav-tabs-custom"><ul class="nav nav-tabs">';
            foreach ($tabs as $key => $label) {
                $active = $filterType === (string) $key ? 'active' : '';
                $url = request()->fullUrlWithQuery(['filter' => $key]);
                $html .= "<li class='{$active}'><a href='{$url}'>{$label}</a></li>";
            }
            $html .= '</ul></div>';

            return $html;
        });


        $grid->id(__('ID'));
        $grid->column('name', __('Name'))->display(function ($name) {

            $uid = @$this->id;
            $showUrl =  admin_url("gifts/{$this->id}") ?? 0;
            return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    <div>
                       <a href='{$showUrl}'
                        style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 5px;'>
                            <span style='text-decoration: underline; cursor: pointer;'>{$name}</span>
                        </a>
                        <span style='font-size: smaller;'>ID: $uid</span>
                    </div>
                </div>
            ";
        });

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
        $permission = $this->permission_name;
        $grid->actions(function ($actions) use ($permission) {
            $model = $actions->row;

            if (
                (Admin::user()->can('move-switch-' . $permission) || Admin::user()->can('*'))
                && $model->category?->type != null
            ) {
                $actions->add(new MoveGiftCategory());
            }
        });
        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
            $batch->add(new MoveGroupsGifts());
        });
        if (request('filter') && request('filter') !== 'all') {
            $grid->tools(function ($tools) use ($filterType) {
                $tools->append('<a href="' . admin_url('gifts/' . request('filter') . '/create') . '" class="btn btn-sm btn-default">' . __('create') . '</a>');

                // Add JavaScript for custom drag-drop handling per category
                $tools->append('
                <script>
                $(document).ready(function() {
                    console.log("🎁 Gift sortable initialized for category: ' . $filterType . '");
                    
                    // Clear cache before sorting starts
                    $(".grid-sortable tbody").on("sortstart", function(event, ui) {
                        console.log("⚡ Sort started - clearing cache...");
                        $.ajax({
                            url: "/admin/gifts/clear-cache",
                            method: "POST",
                            data: { _token: LA.token },
                            async: false
                        });
                    });
                    
                    // Intercept save order button
                    $(document).on("click", ".grid-save-order", function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        console.log("💾 Saving gift order...");
                        
                        var $btn = $(this);
                        var sorts = [];
                        
                        // Collect sort data
                        $(".grid-sortable tbody tr").each(function(index) {
                            sorts.push({
                                id: $(this).data("id"),
                                sort: index + 1
                            });
                        });
                        
                        console.log("📊 Sort data:", sorts);
                        
                        // Send to custom endpoint
                        $.ajax({
                            url: "/admin/gifts/sort-update",
                            method: "POST",
                            data: {
                                _token: LA.token,
                                _sort: sorts,
                                category_id: "' . $filterType . '"
                            },
                            cache: false,
                            headers: {
                                "Cache-Control": "no-cache, no-store, must-revalidate",
                                "Pragma": "no-cache",
                                "Expires": "0"
                            },
                            success: function(response) {
                                console.log("✅ Sort saved:", response);
                                toastr.success(response.message || "تم حفظ الترتيب بنجاح");
                                
                                // Force reload after 1 second to ensure fresh data
                                setTimeout(function() {
                                    console.log("🔄 Force reloading...");
                                    location.reload(true);
                                }, 1000);
                            },
                            error: function(xhr) {
                                console.error("❌ Sort failed:", xhr);
                                toastr.error("فشل حفظ الترتيب");
                            }
                        });
                        
                        return false;
                    });
                    
                    console.log("✅ Gift sortable handlers ready");
                });
                </script>
                ');
            });
        } elseif (request('filter')) {
            $grid->tools(function ($tools) {
                $tools->append('<a href="' . admin_url('gifts/' . request('filter') . '/create') . '" class="btn btn-sm btn-default">' . __('create') . '</a>');
            });
        }

        $grid->disableCreateButton();

        return $grid;
    }

    /**
     * Clear cache for Octane (called from JavaScript before sorting)
     */
    public function clearCache()
    {
        try {
            Cache::flush();
            \Artisan::call('cache:clear');

            if (function_exists('opcache_reset')) {
                @opcache_reset();
            }

            if (function_exists('clearstatcache')) {
                clearstatcache(true);
            }

            return response()->json([
                'status' => true,
                'message' => 'Cache cleared'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle sort update from grid-sortable extension
     */
    public function sortUpdate()
    {
        $sorts = request()->input('_sort');
        $categoryId = request()->input('category_id');

        if (empty($sorts)) {
            return response()->json([
                'status' => false,
                'message' => 'No sort data provided'
            ]);
        }

        try {
            // Clear ALL caches before updating
            Cache::flush();
            \Artisan::call('cache:clear');

            if (function_exists('opcache_reset')) {
                @opcache_reset();
            }

            // Use DB transaction for atomicity
            DB::beginTransaction();

            $updated = 0;
            foreach ($sorts as $sort) {
                // Use raw DB query to bypass Eloquent caching
                $result = DB::table('gifts')
                    ->where('id', $sort['id'])
                    ->when($categoryId, fn($q) => $q->where('gift_category_id', $categoryId))
                    ->update([
                        'sort' => $sort['sort'],
                        'updated_at' => now()
                    ]);
                $updated += $result;
            }

            DB::commit();

            // Clear all caches after updating
            Cache::flush();
            \Artisan::call('cache:clear');

            if (function_exists('opcache_reset')) {
                @opcache_reset();
            }

            // Force PHP to clear stat cache
            if (function_exists('clearstatcache')) {
                clearstatcache(true);
            }

            return response()->json([
                'status' => true,
                'message' => 'تم تحديث ترتيب الهدايا بنجاح'
            ])->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'فشل تحديث الترتيب: ' . $e->getMessage()
            ]);
        }
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
        $model = $id
            ? Gift::with('luckyGift')->findOrFail($id)
            : $form->model();
        $type = old('type', $model?->type ?? null);

        $form->display(__('ID'));
        $form->text('name', __('name'));


        $selectedCategoryId = request()->route('type') ?? $model?->gift_category_id;
        $categories = $selectedCategoryId
            ? GiftCategory::query()->whereKey($selectedCategoryId)->get()
            : collect();
        $locale = App::getLocale();

        $form->html(view('admin.gift_type', [
            'categories' => $categories,
            'locale' => $locale,
            'model' => $model,
        ]));



        $form->currency('price', __('price'))->symbol('💎');
        $form->switch('enable', __('enable'))->states(Common::getSwitchStates());

        // Calculate next sort value for new gifts
        $nextSort = 0;
        if (!$id && $selectedCategoryId) {
            $maxSort = Gift::where('gift_category_id', $selectedCategoryId)->max('sort');
            $nextSort = ($maxSort ?? 0) + 1;
        }
        $form->number('sort', __('Sort'))->default($nextSort)->help(__('Lower numbers appear first'));


        $form->file('img', __('img'))->name(function ($file) {
            $extension = $file->getClientOriginalExtension();
            if (empty($extension)) {
                $extension = $file->guessExtension();
            }
            return 'img_' . now()->timestamp . '_' . rand(100, 999) . '.' . $extension;
        })->default('1.png');


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

            if (request()->has('_edit_inline'))
                return;

            // Handle sort shifting to avoid duplicates
            $newSort = (int) $form->input('sort');
            $categoryId = $form->input('gift_category_id');
            $currentId = $form->model()->id;

            if ($categoryId && $newSort > 0) {
                // Check if sort value exists in the same category
                $query = Gift::where('gift_category_id', $categoryId)
                    ->where('sort', '>=', $newSort);

                // Exclude current gift if editing
                if ($currentId) {
                    $query->where('id', '!=', $currentId);
                }

                // Shift all gifts with sort >= newSort
                $query->increment('sort');
            }

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
                    'title' => 'Error',
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

        $config = Setting::whereIn('key', [
            'app_wallet_lucky_gift',
            'owner_lucky_gift',
            'lucky_gift_coins',
            'host_lucky_gift',
            'lucky_gift_version',
            'lucky_gifts_action'
        ])->pluck('value', 'key')->toArray();

        // FairLuck Data
        $fairLuckSettings = \App\Models\FairLuckSetting::pluck('value', 'key')->toArray();
        $fairLuckHistory = \App\Models\FairLuckWalletHistory::where('wallet_type', 'global_vault')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()->reverse()->values();

        //dd($fairLuckHistory, $fairLuckSettings);
        return $content->view('lucky_gift', [
            'config' => $config,
            'fairLuckSettings' => $fairLuckSettings,
            'history' => $fairLuckHistory,
        ]);
    }

    public function saveLuckyGiftVersion(Request $request)
    {
        $version = $request->input('lucky_gift_version');
        if (in_array($version, [1, 2, 3, 4])) {
            Setting::updateOrCreate(['key' => 'lucky_gift_version'], ['value' => $version]);
            Cache::forget('lucky_gift_version');
            Cache::put('lucky_gift_version', $version);
        }

        // Handle lucky gift coins
        if ($request->has('lucky_gift_coins')) {
            Setting::updateOrCreate(['key' => 'lucky_gift_coins'], ['value' => $request->lucky_gift_coins]);
            Cache::forget('lucky_gift_coins');
            Cache::put('lucky_gift_coins', $request->lucky_gift_coins);
        }

        // Handle V1 percentages and FairLuck settings
        $v1_keys = ['app_wallet_lucky_gift', 'owner_lucky_gift', 'host_lucky_gift', 'fair_luck_app_fee_rate', 'fair_luck_owner_fee_rate', 'fair_luck_receiver_fee_rate'];
        foreach ($v1_keys as $key) {
            if ($request->has($key)) {
                Setting::updateOrCreate(['key' => $key], ['value' => $request->input($key)]);
                Cache::forget($key);
                Cache::put($key, $request->input($key));
            }
        }

        // Handle V6 FairLuck settings
        $v6_keys = [
            'global_vault_negative_limit',
            'bankruptcy_min_safe_balance',
            'bankruptcy_critical_threshold',
            'bankruptcy_max_payout_percentage',
            'v6_max_probability_cap',
            'fairluck_jackpot_cooldown_bets',
            'fairluck_user_data_ttl_days',
            'v6_target_rtp'
        ];
        foreach ($v6_keys as $key) {
            if ($request->has($key)) {
                \App\Models\FairLuckSetting::updateOrCreate(['key' => $key], ['value' => $request->input($key)]);
                Cache::forget($key);
                Cache::put($key, $request->input($key));
            }
        }

        // Handle V7 FairLuck settings
        // Percentage fields submitted as 0-100, stored as 0-1
        $v7_pct_keys = ['V7_target_rtp', 'fair_luck_app_fee_rate', 'fair_luck_receiver_fee_rate', 'fair_luck_owner_fee_rate'];
        foreach ($v7_pct_keys as $key) {
            if ($request->has($key)) {
                $value = (float) $request->input($key) / 100;
                \App\Models\FairLuckSetting::updateOrCreate(['key' => $key], ['value' => $value]);
                Cache::forget($key);
                // Sync V7_app_fee_rate with fair_luck_app_fee_rate
                if ($key === 'fair_luck_app_fee_rate') {
                    \App\Models\FairLuckSetting::updateOrCreate(['key' => 'V7_app_fee_rate'], ['value' => $value]);
                }
            }
        }

        // Direct numeric V7 keys (stored as-is)
        $v7_direct_keys = [
            'V7_wallet_min', 'V7_wallet_tight', 'V7_wallet_target', 'V7_wallet_high', 'V7_wallet_drain',
            'V7_negative_limit', 'V7_nowin_sensitivity', 'V7_win_base_sensitivity', 'V7_win_position_sensitivity',
            'V7_boost_base_sensitivity', 'V7_boost_position_sensitivity', 'V7_wallet_weight', 'V7_rtp_weight',
            'V7_nowin_floor', 'V7_rtp_activation', 'V7_max_loss_streak', 'V7_forced_win_mult',
            'global_vault_negative_limit', 'coin_to_usd_rate',
        ];
        foreach ($v7_direct_keys as $key) {
            if ($request->has($key)) {
                \App\Models\FairLuckSetting::updateOrCreate(['key' => $key], ['value' => $request->input($key)]);
                Cache::forget($key);
            }
        }

        // Clear FairLuck settings cache so changes take effect immediately
        \Illuminate\Support\Facades\Cache::forget('fair_luck:settings');

        admin_toastr(__('Settings updated successfully.'), 'success');
        return back();
    }
}
