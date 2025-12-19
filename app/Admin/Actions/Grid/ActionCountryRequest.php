<?php

namespace App\Admin\Actions\Grid;;

use Illuminate\Http\Request;
use App\Models\EmojiCategory;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class ActionCountryRequest extends BatchAction
{
    public $name;

    public function __construct()
    {
        parent::__construct();
        $this->name = __('status');
    }

    public function handle(Collection $collection, Request $request)
    {
        $status = $request->get('status');
        foreach ($collection as $model) {
            $model->update([
                'status' => $status,
            ]);
        }

        return $this->response()->success(__('emoji moved successfully'))->refresh();
    }



    public function form()
    {

        $this->select('status', __('status'))
            ->options(['accepted' => __('accept'), 'rejected' => __('reject')])->required();
    }
}
