<?php

namespace Utd\Family\Admin\Actions;

use Utd\Family\Entities\FamilyUser;
use App\Facades\UserHandling;
use App\Helpers\Common;
use Utd\Vip\Entities\OVip;
use App\Models\Pack;
use App\Models\User;
use Utd\Vip\Entities\UserVip;
use App\Models\Ware;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
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
        $userHandling = family_facade('user_handling');
        if ($userHandling && $userHandling::checkIfUserOwnerOfFamily($model->id)) {
            throw ValidationException::withMessages(['error' => __('This User is the host Of family can\'t delete it go to remove family first')]);
        }
        $model->family_id = null;
        FamilyUser::query ()->where ('user_id',$model->id)->delete ();
        $model->save ();
        return $this->response()->success (__('dashboard.successful'));
    }

    public function dialog()
    {
        $this->confirm(__('dashboard.chickKick'),'',[]);
    }
}
