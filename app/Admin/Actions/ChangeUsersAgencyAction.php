<?php

namespace App\Admin\Actions;


use App\Models\User;
use App\Models\Agency;
use App\Models\UserSallary;
use Illuminate\Http\Request;
use App\Models\UsersJoinedAgency;
use Illuminate\Support\Facades\DB;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class ChangeUsersAgencyAction extends RowAction
{
    public $name;
    
    public $id;

    public function __construct($id = 0)
    {
        $this->id = $id;
        $this->name = __("dashboard.changeMemberAgency");
        parent::__construct();
    }
    /**
     * @throws ValidationException
     */
    public function handle(Model $model, Request $request)
    {
       
       $users = User::where('agency_id',$request->old_agency_id)->where('type_user',1)->get();
       $checkAgencyUser = UsersJoinedAgency::where([

        'agency_id' => $request->old_agency_id,
        'type' => 2,
    ])->where('leave_date', null)->update(['leave_date', now()]);
        foreach($users as $user)
        {
            $user->agency_id = $request->new_agency_id;
            $user->save();
            $checkAgencyUser = UsersJoinedAgency::where([
                'user_id' => $user->id,
                'agency_id' => $request->old_agency_id,
                'type' => 2,
            ])->where('leave_date', null)->exists();
            if (!$checkAgencyUser) {
                UsersJoinedAgency::create([
                    'user_id' => $user->id,
                    'agency_id' => $request->old_agency_id,
                    'type' => 2,
                    'join_date' => now(),
                ]);
            }
        }
        $usersSalary = UserSallary::where('user_agency_id',$request->old_agency_id)->where('month',now()->month)->where('year',now()->year)->get();
        foreach($usersSalary as $userSalary)
        {
            $userSalary->user_agency_id = $request->new_agency_id;
            $userSalary->save();
        }

        return $this->response()->success('success')->refresh();
    }

    public function form()
    {
        $this->hidden('old_agency_id', __('id'))->value($this->id);
        $this->select('new_agency_id', __('agency id'))->options(function ($value){
            $ops2 = [];
            foreach (Agency::where('id','!=',$this->id)->where(function ($query) {
            $query->WhereDoesntHave('additionalInfo')->orWhereHas(
                'additionalInfo',
                function ($query) {
                    $query->where('status', 1);
                }
            );
        })->get() as $agency){
                $ops2[$agency->id] = $agency->id. '_' .$agency->name;
            }
            return $ops2;
        });
    }

    public function html()
    {
        return '<a href="javascript:void(0);" onclick="pu('.$this->id.')" ></a>
<script>

function pu(val) {

  $("#vid").val(val)
}
</script>
';
    }
}
