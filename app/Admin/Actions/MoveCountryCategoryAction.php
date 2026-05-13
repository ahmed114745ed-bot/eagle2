<?php

namespace App\Admin\Actions;

use App\Models\CountryCategory;
use App\Models\EmojiCategory;
use Illuminate\Http\Request;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class MoveCountryCategoryAction extends RowAction
{
    protected static ?array $cachedCategories = null;

    public function name()
    {
        // الاسم ديناميكي بحسب الحالة الحالية
        return  __('move');
    }

    public function handle(Model $model, Request $request)
    {
        $gift = $this->row;
        $newCategory = $request->get('category_id');

        if (!$newCategory) {
            return $this->response()->error(__('Please select category'))->refresh();
        }

        $gift->country_category_id = $newCategory;
        $gift->save();

        return $this->response()->success(__('moved successfully'))->refresh();
    }

    /**
     * Pre-warm the category cache from an already-loaded collection.
     */
    public static function warmCategoryCache($categories, string $locale): void
    {
        if (self::$cachedCategories === null) {
            self::$cachedCategories = [];
            foreach ($categories as $category) {
                $title = $category->title[$locale]
                    ?? $category->title['en']
                    ?? reset($category->title);

                self::$cachedCategories[$category->id] = $title;
            }
        }
    }

    // Popup form
    public function form()
    {
        $locale = app()->getLocale();

        // Pre-load categories once for all row action instances
        if (self::$cachedCategories === null) {
            self::$cachedCategories = [];
            foreach (CountryCategory::get() as $category) {
                $title = $category->title[$locale]
                    ?? $category->title['en']
                    ?? reset($category->title);

                self::$cachedCategories[$category->id] = $title;
            }
        }

        $this->select('category_id', __('Select Category'))
            ->options(self::$cachedCategories)
            ->required();
    }
}
