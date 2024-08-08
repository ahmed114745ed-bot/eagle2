<?php

namespace App\Admin\Actions;

use App\Facades\UserHandling;
use App\Helpers\Common;
use App\Models\AgencyJoinRequest;
use App\Models\FamilyUser;
use App\Models\OVip;
use App\Models\Pack;
use App\Models\User;
use App\Models\UserVip;
use App\Models\Ware;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AgencyButton extends RowAction
{
    public $name = 'achivement';

    public function handle(Model $model, Request $request)
    {
        return '<a href=""><i class="fa fa-eye"></i></a>';
    }

}
