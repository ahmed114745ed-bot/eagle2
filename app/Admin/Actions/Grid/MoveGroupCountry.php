<?php

namespace App\Admin\Actions\Grid;

use Illuminate\Http\Request;
use App\Models\EmojiCategory;
use App\Models\CountryCategory;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class MoveGroupCountry extends BatchAction
{
    protected static ?array $cachedCategories = null;

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

        // Pre-load categories once
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
