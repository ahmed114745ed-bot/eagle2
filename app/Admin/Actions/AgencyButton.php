<?php

namespace App\Admin\Actions;
use App\Models\AgencyJoinRequest;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AgencyButton extends RowAction
{
    public $name = 'achivement';

    public function handle(Model $model, Request $request)
    {
        return '<a href=""><i class="fa fa-eye"></i></a>';
    }

}
