<?php

namespace App\Admin\Actions;

use App\Models\EmojiCategory;
use Illuminate\Http\Request;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class MoveEmojiCategoryAction extends RowAction
{
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
            return $this->response()->error('Please select category')->refresh();
        }

        $gift->emoji_category_id = $newCategory;
        $gift->save();

        return $this->response()->success('emoji moved successfully')->refresh();
    }

    // Popup form
    public function form()
    {
        $locale = app()->getLocale();

        // Category select
        $this->select('category_id', __('Select Category'))
            ->options(function () use ($locale) {

                $categories = [];
                foreach (EmojiCategory::get() as $category) {
                    $title = $category->title[$locale]
                        ?? $category->title['en']
                        ?? reset($category->title);

                    $categories[$category->id] = $title;
                }

                return $categories;
            })
            ->required();
    }
}
