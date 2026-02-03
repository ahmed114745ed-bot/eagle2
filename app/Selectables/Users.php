<?php

namespace App\Selectables;

use App\Models\Gift;
use App\Models\User;
use Modules\Vip\Entities\VipPrivilege;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Selectable;

class Users extends Selectable
{

    public $model = User::class;

    public function make()
    {

        // When creating: show users that don't have any percentage assignment.
        // When editing (request has id): show users that either don't have a percentage
        // or already belong to the PercentageGame being edited so they remain selectable.
        if (request('id')) {
            $percentageId = request('id');
            $this->grid->model()->with('profile')->where(function ($q) use ($percentageId) {
                $q->doesntHave('gamePercentage')
                    ->orWhereHas('gamePercentage', function ($q2) use ($percentageId) {
                        $q2->where('percentage_game_id', $percentageId);
                    });
            });
        } else {
            $this->grid->model()->with('profile')->doesntHave('gamePercentage');
        }

        $this->column('id');
        $this->column('uuid');
        $this->column('name');
        $this->column('profile.avatar', __('Image'))->image();

        $this->filter(function (Filter $filter) {
            $filter->like('name');
        });
    }
}
