<?php

namespace App\Admin\Actions;

use App\Facades\UserHandling;
use App\Helpers\Common;
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

class KickOfFamilyAction extends RowAction
{
    public $name;
    public function __construct($id = 0)
    {
        $this->name = __("dashboard.kickFamily");
        parent::__construct();
    }
    /**
     * @throws ValidationException
     */
    public function handle(Model $model, Request $request)
    {
        if (UserHandling::checkIfUserOwnerOfFamily($model->id)){
            throw ValidationException::withMessages(['error' => __('This User is the host Of family can\'t delete it go to remove family first')]);
        }
        $model->family_id = 0;
        FamilyUser::query ()->where ('user_id',$model->id)->delete ();
        $model->save ();
        return $this->response()->success (__('dashboard.successful'));
    }

    public function dialog()
    {
        $this->confirm(__('dashboard.chickKick'),'',[]);
    }
}
