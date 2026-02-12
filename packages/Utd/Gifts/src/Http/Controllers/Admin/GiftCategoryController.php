<?php

namespace Utd\Gifts\Http\Controllers\Admin;

use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Utd\Gifts\Entities\GiftCategory;

class GiftCategoryController
{
    use HasResourceActions;

    public $permission_name = 'gift-categories';

    protected $title = 'Gift Categories';

    public function index(Content $content)
    {
        return $content
            ->title(trans('Gift Categories'))
            ->body($this->grid());
    }

    /**
     * Show interface
     */
    public function show($id, Content $content)
    {
        return $content
            ->title(trans('Gift Categories'))
            ->body($this->detail($id));
    }

    /**
     * Edit interface
     */
    public function edit($id, Content $content)
    {
        return $content
            ->title(trans('Gift Categories'))
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface
     */
    public function create(Content $content)
    {
        return $content
            ->title(trans('Gift Categories'))
            ->body($this->form());
    }

    /**
     * Clear cache
     */
    public function clearCache(Request $request)
    {
        try {
            Cache::tags(['gift_categories', 'admin_data'])->flush();

            if (function_exists('opcache_reset')) {
                opcache_reset();
            }

            return response()->json([
                'status' => true,
                'message' => __('Cache cleared successfully'),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to clear cache: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => __('Failed to clear cache'),
            ], 500);
        }
    }

    /**
     * Sort update
     */
    public function sortUpdate(Request $request)
    {
        try {
            $orders = $request->input('orders', []);

            foreach ($orders as $order) {
                GiftCategory::where('id', $order['id'])
                    ->update(['sort' => $order['sort']]);
            }

            $this->clearCache($request);

            return response()->json([
                'status' => true,
                'message' => __('Sort updated successfully'),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to update sort: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => __('Failed to update sort'),
            ], 500);
        }
    }

    /**
     * Make a grid builder
     */
    protected function grid()
    {
        $grid = new Grid(new GiftCategory());

        $grid->id('ID')->sortable();
        $grid->column('title', __('Title'));
        $grid->column('type', __('Type'));
        $grid->column('sort', __('Sort'))->sortable()->editable();
        $grid->column('gifts_count', __('Gifts Count'))
            ->display(function () {
                return $this->gifts()->count();
            });
        $grid->column('created_at', __('Created at'));

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('title', __('Title'));
            $filter->equal('type', __('Type'));
        });

        return $grid;
    }

    /**
     * Make a show builder
     */
    protected function detail($id)
    {
        $show = new Show(GiftCategory::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('title', __('Title'))->as(function ($title) {
            return json_encode($title, JSON_UNESCAPED_UNICODE);
        });
        $show->field('type', __('Type'));
        $show->field('sort', __('Sort'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder
     */
    protected function form()
    {
        $form = new Form(new GiftCategory);

        // Disable default form tools
        $form->tools(function ($tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        $form->display('id', trans('ID'));
        $form->text('title.ar', __('Title (Arabic)'))->required();
        $form->text('title.en', __('Title (English)'))->required();

        $form->select('type', __('Type'))->options([
            'normal' => __('Normal'),
            'hot' => __('Hot'),
            'country' => __('Country'),
            'moment' => __('Moment'),
            'famous' => __('Famous'),
            'lucky_gift' => __('Lucky Gift'),
            'cp' => __('CP'),
            'vip' => __('VIP'),
        ])->required();

        $form->number('sort', __('Sort'))->default(1);

        return $form;
    }
}
