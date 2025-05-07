<?php

namespace App\Admin\Actions;

use App\Models\User;
use App\Helpers\UserCommon;
use Encore\Admin\Actions\Action;
use App\Models\AgencyJoinRequest;
use App\Models\UsersJoinedAgency;
use App\Facades\CustomNotification;



class AcceptAgencyJoinRequestAction extends Action
{

    public $id;

    protected $selector = '.salary_action';
   

    public function __construct($id = 0)
    {
        $this->name = __('dedicate');
        $this->id = $id;
        parent::__construct();
    }

    public function handle(\Illuminate\Http\Request $request)
    {
        $agencyJoinRequest = AgencyJoinRequest::where('id', $request->id)->with('user', 'agency')->fist();
        if (!$agencyJoinRequest) return $this->response()->error(__('not found'))->refresh();
        $user = $agencyJoinRequest->user;
        if (!$user) return $this->response()->error(__('user not found'))->refresh();
        $agency = $agencyJoinRequest->agency;
        if (!$agency) return $this->response()->error(__('agency not found'))->refresh();
        if ($user->agency_id) return $this->response()->error(__('user joined in another agency'))->refresh();
        $agencyJoinRequest->status = 1;
        $agencyJoinRequest->save();

        $user->agency_id = $agencyJoinRequest->agency_id;
        $user->type_user = 1;
        $user->save();
        $checkAgencyUser = UsersJoinedAgency::where('user_id', $user->id)->where('agency_id', $agency->id)->where('leave_date', null)->exists();
        if (!$checkAgencyUser) {
            $joinAgencyData = [
                'user_id' =>  $user->id,
                'agency_id' => $agency->id,
                'type' => 2,
                'join_date' => now(),
            ];
            UsersJoinedAgency::create($joinAgencyData);
        }
        // add vip to user
        UserCommon::userVip($user);
        CustomNotification::acceptAgencyApp($agency, $user);
        return $this->response()->success('success')->refresh();
    }


    public function form()
    {
        $this->hidden('id', __('id'))->attribute('id', 'vid');

        $this->confirm(__('messages.confirm_delete'), __('messages.accept'), [
            'icon' => 'warning',
            'showCancelButton' => true,
            'confirmButtonText' => __('messages.yes'),
            'cancelButtonText' => __('messages.cancel'),
        ]);
    }


    public function html()
    {
        return '<a href="javascript:void(0);" onclick="pu(' . $this->id . ')" class="btn btn-sm btn-info salary_action ">' . __('accept') . '</a>
<script>
function pu(val) {

  $("#vid").val(val)
}
</script>
';
    }
}
