<?php

namespace App\Admin\Actions;

use App\Models\OVip;
use App\Models\Pack;
use App\Models\User;
use App\Models\Ware;
use App\Models\Agency;
use App\Helpers\Common;
use App\Models\UserVip;
use App\Models\FamilyUser;
use Illuminate\Http\Request;
use App\Facades\UserHandling;
use App\Models\UserSallary;
use Illuminate\Support\Facades\DB;
use Encore\Admin\Actions\RowAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class ChangeAgencyAction extends RowAction
{
    public $name;
    
    public $id;

    public function __construct($id = 0)
    {
        $this->name = __("dashboard.changeAgency");
        $this->id = $id;
        parent::__construct();
    }
    /**
     * @throws ValidationException
     */
    public function handle(Model $model, Request $request)
    {
       $agencyOwner= Agency::query()->where('owner_id', $request->id)->orWhere('app_owner_id', $request->id)->exists();
       if($agencyOwner)throw ValidationException::withMessages(['error' => __('This User is the host Of agency can\'t delete it')]);
        $user = User::find($request->id);
        $user->agency_id = $request->agency_id;
        $user->save();
        $userSalary = UserSallary::where('user_id',$user->id)->where('month',now()->month)->where('year',now()->year)->first();
        if($userSalary){
            $userSalary->user_agency_id = $request->agency_id;
            $userSalary->save();
        }
        return $this->response()->success('success')->refresh();
    }

    public function form()
    {
        $this->hidden('id', __('id'))->value($this->id);
        $this->select('agency_id', __('agency id'))->options(function ($value){
            $ops2 = [];
            foreach (Agency::get() as $agency){
                $ops2[$agency->id] =  $agency->id. '_' . $agency->name;
            }
            return $ops2;
        });
    }

    public function html()
    {
        return '<a href="javascript:void(0);" onclick="pu('.$this->id.')"  ></a>
<script>
function pu(val) {

  $("#vid").val(val)
}
</script>
';
    }
}
