<?php

namespace Utd\Gifts\Actions;

use Encore\Admin\Actions\RowAction;
use Encore\Admin\Form;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Utd\Gifts\Entities\GiftCategory;

class MoveGiftCategory extends RowAction
{
    public function name()
    {
        // الاسم ديناميكي بحسب الحالة الحالية
        return __('move');
    }

    public function handle(Model $model, Request $request)
    {
        $gift = $this->row;
        $newCategory = $request->get('category_id');

        if (! $newCategory) {
            return $this->response()->error('Please select category')->refresh();
        }

        $gift->gift_category_id = $newCategory;
        $gift->save();

        return $this->response()->success('Gift moved successfully')->refresh();
    }

    // Popup form
    public function form()
    {
        $locale = app()->getLocale();

        // Category select
        $this->select('category_id', __('Select Category'))
            ->options(function () use ($locale) {

                $categories = [];
                foreach (GiftCategory::whereNotIn('type', ['lucky_gift', 'vip'])->get() as $category) {
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
