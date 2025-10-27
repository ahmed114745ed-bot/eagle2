<?php

namespace App\Selectables;

use App\Models\Family;
use Encore\Admin\Grid\Filter;
use Modules\Vip\Entities\OVip;
use Encore\Admin\Grid\Selectable;
use Illuminate\Support\Facades\Auth;

class Families extends Selectable
{

    public $model = Family::class;

    public function make()
    {
        if (in_array(Auth::user()->type, ['superadmin', 'sub_super_admin'])) {
             $this->model()->where('country_id', Auth::user()->country_id);
        }
        $this->column('id');
        $this->column('name', __('name'));
        $this->column('image', __('img'))->display(function ($path) {
            /** @var OVip $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });

        $this->filter(function (Filter $filter) {
            $filter->like('name');
        });
    }
}
