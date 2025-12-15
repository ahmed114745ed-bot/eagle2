<?php

namespace App\Admin\Actions\Grid;;

use Illuminate\Http\Request;
use App\Models\EmojiCategory;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class MoveGroupEmoji extends BatchAction
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
                'emoji_category_id' => $newCategory,
            ]);
        }

        return $this->response()->success(__('emoji moved successfully'))->refresh();
    }



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
