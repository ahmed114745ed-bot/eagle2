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
        
        // Handle sortable requests (from grid-sortable extension)
        if (request()->has('_sort')) {
            return $this->handleSortUpdate();
        }
        
        $response = parent::update($id);
        
        // Ensure fresh data from database for Octane
        GiftCategory::query()->whereKey($id)->first()?->refresh();
        Cache::tags(['gift_categories'])->flush();
        
        return $response;
    }
    
    /**
     * Handle sort update from grid-sortable extension
     */
    protected function handleSortUpdate()
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
                'message' => 'Failed to update sort order: ' . $e->getMessage()
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
        
        // Enable sortable with proper Octane handling
        $grid->sortable('sort');
        
        $grid->column('id', __('Id'))->width(50);
        $grid->column('sort', __('Order'))->width(80)->editable();
        $grid->column('title', __('title'))->display(function ($value) {
            $locale = App::getLocale();

            // $value is already an array because of casts
            return $value[$locale] ?? ($value['en'] ?? '');
        });
        $grid->column('type', __('type'));

        $this->extendGrid($grid);

        $grid->disableExport();
        
        // Disable row selector to prevent issues with sortable
        $grid->disableRowSelector();
        
        // Add custom JS to handle Octane cache clearing and force refresh
        $grid->tools(function ($tools) {
            $tools->append('
            <style>
                .grid-sortable-handle {
                    cursor: grab !important;
                }
                .grid-sortable-handle:active {
                    cursor: grabbing !important;
                }
            </style>
            <script>
            $(function() {
                // Override the sortable update callback
                var originalSortableOptions = {};
                
                // Wait for grid to be ready
                setTimeout(function() {
                    var sortableTable = $(".grid-sortable tbody");
                    
                    if (sortableTable.length && sortableTable.sortable("instance")) {
                        // Get current options
                        originalSortableOptions = sortableTable.sortable("option");
                        
                        // Override update callback
                        sortableTable.sortable("option", "update", function(event, ui) {
                            var data = [];
                            sortableTable.find("tr").each(function(index) {
                                var id = $(this).data("id") || $(this).find("td:first").text();
                                data.push({
                                    id: id,
                                    sort: index + 1
                                });
                            });
                            
                            // Send AJAX request with cache busting
                            $.ajax({
                                url: window.location.pathname,
                                method: "POST",
                                data: {
                                    _token: LA.token,
                                    _sort: data,
                                    _method: "PUT",
                                    _octane_cache_bust: Date.now()
                                },
                                success: function(response) {
                                    if (response.status) {
                                        toastr.success(response.message || "تم تحديث الترتيب بنجاح");
                                        
                                        // Force reload to get fresh data from database
                                        setTimeout(function() {
                                            $.pjax.reload({container:"#pjax-container", timeout: 2000});
                                        }, 500);
                                    } else {
                                        toastr.error(response.message || "فشل تحديث الترتيب");
                                        // Revert the UI
                                        $.pjax.reload({container:"#pjax-container"});
                                    }
                                },
                                error: function(xhr) {
                                    toastr.error("حدث خطأ أثناء التحديث");
                                    console.error(xhr);
                                    // Revert the UI
                                    $.pjax.reload({container:"#pjax-container"});
                                }
                            });
                        });
                        
                        console.log("Grid sortable override applied for Octane compatibility");
                    }
                }, 1000);
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
