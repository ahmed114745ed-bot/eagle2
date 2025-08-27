<?php

namespace App\Admin\Actions;

use Modules\Vip\Entities\OVip;
use App\Models\AgencyUserJob;
use App\Models\Pack;
use App\Models\User;
use App\Models\UsersJoinedAgency;
use App\Models\Ware;
use App\Models\Agency;
use App\Helpers\Common;
use Modules\Vip\Entities\UserVip;
use App\Models\FamilyUser;
use Encore\Admin\Admin;
use Illuminate\Http\Request;
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
        Admin::script('$.fn.modal.Constructor.prototype.enforceFocus = function () {};');

        $this->name = __("dashboard.changeAgency");
        $this->id = $id;
        parent::__construct();
    }
    /**
     * @throws ValidationException
     */
    public function handle(Model $model, Request $request)
    {
        $user = User::find($request->id);

        $agencyOwner = Agency::query()
            ->where('owner_id', $request->id)
            ->orWhere('app_owner_id', $request->id)
            ->exists();
        if ($agencyOwner) {
            throw ValidationException::withMessages([
                'error' => __('This user is the agency owner and cannot be deleted')
            ]);
        }

        $oldAgencyId = $user->agency_id;

        uploadMonthlyDiamondReceive($user->id, 0);

        $this->handleUserSalaries($user);

        $this->updatePreviousAgencyJoined($user, $oldAgencyId);

        UsersJoinedAgency::create([
            'user_id' => $user->id,
            'agency_id' => $request->agency_id,
            'type' => 2,
            'join_date' => now(),
            'status' => 'Joined'
        ]);

        $user->agency_id = $request->agency_id;
        $user->type_user = User::TYPE_HOST;
        $user->save();

        AgencyUserJob::where(['user_id' => $user->id, 'agency_id' => $oldAgencyId])->delete();

        return $this->response()->success('success')->refresh();
    }

    private function handleUserSalaries(User $user)
    {
        $agencyId = $user->agency_id;
        $userSalaries = UserSallary::query()
            ->where('user_id', $user->id)
            ->where('user_agency_id', $agencyId)
            ->latest()
            ->take(2)
            ->get();

        if ($userSalaries->isEmpty()) return;

        $currentMonth = now()->month;
        $currentYear = now()->year;

        $currentSalary = $userSalaries[0];
        if ($currentSalary->month == $currentMonth && $currentSalary->year == $currentYear) {
            if (isset($userSalaries[1]) && $currentSalary->cut_amount >= $currentSalary->sallary) {
                $prevSalary = $userSalaries[1];
                $prevSalary->cut_amount += ($currentSalary->cut_amount - $currentSalary->sallary);
                $prevSalary->save();
            }
            $currentSalary->update(['is_finished' => 1]);
        }
    }

    private function updatePreviousAgencyJoined(User $user, $agencyId)
    {
        $checkAgencyUser = UsersJoinedAgency::where([
            'user_id' => $user->id,
            'agency_id' => $agencyId,
        ])->whereNull('leave_date')->first();

        if (!$checkAgencyUser) {
            UsersJoinedAgency::create([
                'user_id' => $user->id,
                'agency_id' => $agencyId,
                'type' => 2,
                'join_date' => now(),
                'leave_date' => now(),
                'status' => 'change agency by admin',
                'kicked_by_admin' => Auth::id(),
            ]);
        } else {
            $checkAgencyUser->update([
                'leave_date' => now(),
                'status' => 'change agency by admin',
                'kicked_by_admin' => Auth::id()
            ]);
        }
    }
    public function form()
    {
        $this->hidden('id', __('id'))->value($this->id);
        $this->select('agency_id', __('agency id'))->ajax('/admin/search/host-agency', 'id', 'name');
    }

    public function html()
    {
        return '<a href="javascript:void(0);" onclick="pu(' . $this->id . ')"  ></a>
            <script>
            function pu(val) {

              $("#vid").val(val)
            }
            </script>
        ';
    }
}
