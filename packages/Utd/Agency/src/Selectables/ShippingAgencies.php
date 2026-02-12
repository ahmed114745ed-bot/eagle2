<?php

namespace Utd\Agency\Selectables;

use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Selectable;
use Illuminate\Support\Facades\Auth;
use Utd\Agency\Entities\ShippingAgency;

class ShippingAgencies extends Selectable
{
    public $model = ShippingAgency::class;

    public function make()
    {
        if (in_array(Auth::user()->type, ['superadmin', 'sub_super_admin'])) {
            $this->model()->where('type', 2)->where('country_id', Auth::user()->country_id);
        }
        $this->column('id');
        $this->column('name', __('name'));
        $this->column('img', __('img'))->display(function ($path) {
            $url = getImagePath($path);

            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });

        $this->filter(function (Filter $filter) {
            $filter->like('name');
        });
    }
}
