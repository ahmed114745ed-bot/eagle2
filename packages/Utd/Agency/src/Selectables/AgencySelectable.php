<?php

namespace Utd\Agency\Selectables;

use Utd\Agency\Entities\Agency;
use Encore\Admin\Grid\Selectable;

class AgencySelectable extends Selectable
{
    public $model = Agency::class;

    public function make()
    {
        $this->column('id', __('ID'));
        $this->column('name', __('Name'));
        $this->column('owner.name', __('Owner'));
        $this->column('status', __('Status'))->display(function ($status) {
            return $status == 1 ? 'Active' : 'Inactive';
        });

        $this->filter(function ($filter) {
            $filter->like('name', __('Name'));
            $filter->equal('id', __('ID'));
        });
    }
}
