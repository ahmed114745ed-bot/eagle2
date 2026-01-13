<?php

namespace App\Admin\Actions;

use App\Models\CountryCategory;
use App\Models\EmojiCategory;
use Illuminate\Http\Request;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class MoveCountryCategoryAction extends RowAction
{
    protected static $categories;
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

    // Popup form
    public function form()
    {
        $locale = app()->getLocale();

        // Category select
        if (!isset(self::$categories)) {
            self::$categories = CountryCategory::orderBy('id')->get()
                ->mapWithKeys(function ($category) use ($locale) {
                    $title = $category->title[$locale]
                        ?? $category->title['en']
                        ?? reset($category->title);

                    return [$category->id => $title];
                })
                ->toArray();
        }

        $this->select('category_id', __('Select Category'))
            ->options(self::$categories)
            ->required();
    }
}
