<?php

namespace App\Admin\Actions\Grid;

use Illuminate\Http\Request;
use App\Models\EmojiCategory;
use App\Models\CountryCategory;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class MoveGroupCountry extends BatchAction
{
    public $name;

    public function __construct()
    {
        parent::__construct();
        $this->name = __('move');
    }

    public function handle(Collection $collection, Request $request)
    {
        $newCategory = $request->get('category_id');
        foreach ($collection as $model) {
            $model->update([
                'country_category_id' => $newCategory,
            ]);
        }

        return $this->response()->success(__('moved successfully'))->refresh();
    }



    public function form()
    {
        $locale = app()->getLocale();

        // Category select — cache to avoid duplicate queries
        $this->select('category_id', __('Select Category'))
            ->options(function () use ($locale) {
                static $cachedCategories = null;

                if ($cachedCategories === null) {
                    $cachedCategories = [];
                    foreach (CountryCategory::get() as $category) {
                        $title = $category->title[$locale]
                            ?? $category->title['en']
                            ?? reset($category->title);

                        $cachedCategories[$category->id] = $title;
                    }
                }

                return $cachedCategories;
            })
            ->required();
    }
}
