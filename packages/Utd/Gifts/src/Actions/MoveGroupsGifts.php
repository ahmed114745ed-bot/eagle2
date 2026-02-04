<?php

namespace Utd\Gifts\Actions;

use Utd\Gifts\Entities\GiftCategory;
use Illuminate\Http\Request;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class MoveGroupsGifts extends BatchAction
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
                'gift_category_id' => $newCategory,
            ]);
        }

        return $this->response()->success(__('Gift moved successfully'))->refresh();
    }



    public function form()
    {
        $locale = app()->getLocale();

        // Category select
        $this->select('category_id', __('Select Category'))
            ->options(function () use ($locale) {

                $categories = [];
                foreach (GiftCategory::whereNotIn('type', ['lucky_gift', 'vip'])->get() as $category) {
                    // Access title as object property or array
                    $titleValue = $category->title;
                    
                    if (is_array($titleValue)) {
                        $title = $titleValue[$locale] ?? $titleValue['en'] ?? reset($titleValue);
                    } else {
                        $title = $titleValue;
                    }

                    $categories[$category->id] = $title;
                }

                return $categories;
            })
            ->required();
    }
}
