<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;

use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\GiftCategory;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use App\Admin\Controllers\MainController;

class GiftCategoryController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'GiftCategory';
    public $permission_name = 'gift-categories';




    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('Gift Categories'))
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
        return parent::show($id, $content
            ->title(trans('Gift Categories'))
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
        return parent::edit($id, $content
            ->title(trans('Gift Categories'))
            ->body($this->form($id)->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('Gift Categories'))
            ->body($this->form()));
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        // Clear cache before update
        Cache::tags(['gift_categories'])->flush();
        
        $response = parent::update($id);
        
        // Ensure fresh data from database for Octane
        GiftCategory::query()->whereKey($id)->first()?->refresh();
        Cache::tags(['gift_categories'])->flush();
        
        return $response;
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
            
            \Log::info('GiftCategory cache cleared for sorting');
            
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
     * Handle sort update from grid-sortable extension (dedicated route)
     */
    public function sortUpdate()
    {
        $sorts = request()->input('_sort');
        
        \Log::info('GiftCategory Sort Update Request', [
            'data' => $sorts,
            'request_all' => request()->all()
        ]);
        
        if (empty($sorts)) {
            return response()->json([
                'status' => false,
                'message' => 'No sort data provided'
            ]);
        }
        
        try {
            // Clear cache before updating
            Cache::tags(['gift_categories'])->flush();
            
            // Disable events temporarily for bulk update
            \DB::beginTransaction();
            
            $updated = 0;
            foreach ($sorts as $sort) {
                $result = \DB::table('gift_categories')
                    ->where('id', $sort['id'])
                    ->update([
                        'sort' => $sort['sort'],
                        'updated_at' => now()
                    ]);
                $updated += $result;
            }
            
            \DB::commit();
            
            \Log::info('GiftCategory Sort Update Success', [
                'updated_count' => $updated,
                'total_items' => count($sorts)
            ]);
            
            // Clear cache after updating
            Cache::tags(['gift_categories'])->flush();
            
            // Force Octane to clear its state
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            
            return response()->json([
                'status' => true,
                'message' => 'تم تحديث الترتيب بنجاح'
            ]);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            
            \Log::error('GiftCategory Sort Update Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => false,
                'message' => 'فشل تحديث الترتيب: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new GiftCategory());
        
        // Enable sortable - Laravel Admin Extension will handle everything
        $grid->sortable();
        
        $grid->column('id', __('Id'))->width(50);
        $grid->column('sort', __('Order'))->width(80)->editable();
        $grid->column('title', __('title'))->display(function ($value) {
            $locale = App::getLocale();
            return $value[$locale] ?? ($value['en'] ?? '');
        });
        $grid->column('type', __('type'));

        $this->extendGrid($grid);
        $grid->disableExport();
        $grid->disableRowSelector();
        
        // Add JavaScript to force reload after sort for Octane compatibility
        $grid->tools(function ($tools) {
            $tools->append('
            <script>
            $(document).ready(function() {
                console.log("Octane sortable fix initialized");
                
                // Clear cache when user starts dragging (before save)
                $(".grid-sortable tbody").on("sortstart", function(event, ui) {
                    console.log("Sort started - clearing cache...");
                    
                    // Send request to clear cache
                    $.ajax({
                        url: "/admin/gift-categories/clear-cache",
                        method: "POST",
                        data: { _token: LA.token },
                        async: false // Synchronous to ensure cache is cleared before sort
                    });
                });
                
                // Intercept the save order button click
                $(document).on("click", ".grid-save-order", function(e) {
                    console.log("Save order button clicked");
                    
                    var $btn = $(this);
                    
                    // Clear cache before saving
                    $.ajax({
                        url: "/admin/gift-categories/clear-cache",
                        method: "POST",
                        data: { _token: LA.token },
                        async: false,
                        success: function() {
                            console.log("Cache cleared before save");
                        }
                    });
                    
                    // Let the default handler run, then reload
                    setTimeout(function() {
                        console.log("Waiting for save to complete...");
                        
                        // Force reload after successful save (Octane fix)
                        setTimeout(function() {
                            console.log("Reloading page for fresh data...");
                            location.reload(true); // Force reload from server
                        }, 2000);
                    }, 500);
                });
                
                console.log("Octane sortable handlers attached");
            });
            </script>
            ');
        });
        
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
        $show = new Show(GiftCategory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('sort', __('Sort'));
        $show->field('title', __('Title'));
        $show->field('type', __('Type'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id = null)
    {
        $form = new Form(new GiftCategory());

        // Pass model to view (this makes edit mode show old values)
        $form->html(view('admin.multi_lang_tabs', [

            'model' => $id != null ? GiftCategory::find($id) : [],
        ]));

        // Type field
        $form->select('type', __('Type'))->options([
            'normal'     => __('Normal'),
            'lucky_gift' => __('Lucky gifts'),
            'cp'         => __('CP'),
            'vip'        => __('VIP'),
        ])->required();
        
        $form->number('sort', __('sort'))
            ->rules('required|integer|min:1')      // minimum value 1
            ->required();

        // Save titles back as array
        $form->saving(function (Form $form) {
            $titles = request()->input('title', []);
            $form->model()->title = $titles;
            
            // Clear cache when saving
            Cache::tags(['gift_categories'])->flush();
        });

        return $form;
    }
}
