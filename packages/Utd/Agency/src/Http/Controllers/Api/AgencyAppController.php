<?php

namespace Utd\Agency\Http\Controllers\Api;

use Admin;
use App\Models\User;
use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Facades\CustomNotification;
use Utd\Agency\Facades\AgencyHelper;
use App\Models\Admin as ModelsAdmin;
use App\Models\AgencyJoinRequest;
use App\Models\Agent;
use App\Models\Follow;
use App\Models\GiftLog;
use App\Models\LiveTime;
use App\Models\ProfileVisitor;
use App\Models\UserSallary;
use App\Notifications\AcceptAgency;
use App\Notifications\RefuseAgency;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Notification;
use Utd\Agency\Emails\SendAgencyEmail;
use Modules\Reals\Http\Services\RealsService;
use Utd\Agency\Entities\AdditionalInfo;
use Utd\Agency\Notifications\AgencyMail;
use Utd\Agency\Http\Requests\CreateAgencyRequest;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Utd\Agency\Entities\AgencyHostInvite;
use Utd\Agency\Exports\HostDailyDataExport;
use Utd\Agency\Transformers\AgencyHostResource;
use Utd\Agency\Transformers\AgencyInvitationResource;
use Utd\Agency\Transformers\AgencyMonthlyHostResource;
use Utd\Agency\Transformers\HostDailyReportResource;

class AgencyAppController extends Controller
{

    public function get_user(Request $request) {
        if($request->user_id){
            $admin = ModelsAdmin::find($request->user()->id);
            $user = User::find($request->user_id);
            if(isset($admin)&& $admin->isRole("admin")){
                $user = User::find($request->user_id);
            }else{
                $user = null ;
            }
        }
        else{
            $user = User::find($request->user()->id);
        }
        return $user;
    }
    public function actionInvitation(Request $request)
    {
        if (!$request->invite_id || !$request->status) {
            return AgencyHelper::apiResponse(0, 'البيانات غير مكتمله',  423);
        }
        $user = $this->get_user(request());
        if (!$user) {
            return AgencyHelper::apiResponse(0, 'لا يوجد مستخدم!',  200);
        }
        $invitation = AgencyHostInvite::findOrFail($request->invite_id);
        if ($invitation->created_at->addDays(7) < now()) {
            $invitation->update(['status'=>3]);
            return AgencyHelper::apiResponse(0, 'لقد مر اكثر من 7 ايام علي الدعوه',  423);
        }
        $invitation->update(['status'=>$request->status]);
        if ($request->status == 1) {
            $user->update(['agency_id' => $invitation->agency_id]);
        }
        return AgencyHelper::apiResponse(1, 'تم التعديل بنجاح',[],  200);
    }

    public function agencyHostInvitation(Request $request)
    {
        $user = $this->get_user(request());
        if (!$user) {
            return AgencyHelper::apiResponse(0, 'لا يوجد مستخدم!',  200);
        }
        $invitations = AgencyHostInvite::where("agency_id",$user->agency_id)->get();
        return AgencyHelper::apiResponse(1, '', AgencyInvitationResource::collection($invitations),  200);
    }

    public function invite_user_to_hostAgency(Request $request)
    {
        if (!$request->user_id2) {
            return AgencyHelper::apiResponse(0, 'المستخدم مطلوب!',  423);
        }
        $user = $this->get_user($request);
        if (!$user) {
            return AgencyHelper::apiResponse(0, 'لا يوجد مستخدم!',  200);
        }
        $newhost = User::find($request->user_id2);

        if ($newhost->agency_id != 0) {
            return AgencyHelper::apiResponse(0, 'المستخدم موجود في وكاله!',  423);
        }
        $check = AgencyHostInvite::where([ 'agency_id' => $user->agency_id,'user_id'  => $newhost->id])->latest('id')->first();
        if ($check!=null && $check->created_at->addDays(7) > now() && $check->status == 0) {
            return AgencyHelper::apiResponse(0, 'لم يمر علي اخر دعوه 7 ايام!',  423);
        }
        AgencyHostInvite::create([
            'user_invite_id' => $user->id,
            'agency_id' => $user->agency_id,
            'user_id'  => $newhost->id,
            'status' => 0,
        ]);

        return AgencyHelper::apiResponse(1, 'تم ارسال الدعوه بنجاح', [],  200);
    }

    public function host_daily_export_data(Request $request)
    {
        if (!$request->date) {
            return AgencyHelper::apiResponse(0, 'التاريخ مطلوب!',  200);
        }
        $date = $request->date;
        $user = $this->get_user($request);
        if (!$user) {
            return AgencyHelper::apiResponse(0, 'لا يوجد مستخدم!',  200);
        }

        if (!$user->agency) {
            return AgencyHelper::apiResponse(0, 'لا تملك وكاله!',  200);
        }
        $hosts = User::where("agency_id",$user->agency_id)->where("type_user",'!=',0);
        if ($request->host_id != null) {
            $hosts = $hosts ->where("id",$request->host_id);
        }
        $hosts = $hosts->get();
        $data = HostDailyReportResource::collection($hosts);
        return Excel::download(new HostDailyDataExport($data), 'data.xlsx');
    }

    public function host_daily_report(Request $request)
    {
        if (!$request->date) {
            return AgencyHelper::apiResponse(0, 'التاريخ مطلوب!',  200);
        }
        $date = $request->date;
        $user = $this->get_user($request);
        if (!$user) {
            return AgencyHelper::apiResponse(0, 'لا يوجد مستخدم!',  200);
        }

        if (!$user->agency) {
            return AgencyHelper::apiResponse(0, 'لا تملك وكاله!',  200);
        }
        $hosts = User::where("agency_id",$user->agency_id)->where("type_user",'!=',0);
        if ($request->host_id != null) {
            $hosts = $hosts ->where("id",$request->host_id);
        }
        $hosts = $hosts->get();
        return AgencyHelper::apiResponse(1, '', HostDailyReportResource::collection($hosts),  200);
    }


    public function host_report($id)
    {
        $user = $this->get_user(request());
        if (!$user) {
            return AgencyHelper::apiResponse(0, 'لا يوجد مستخدم!',  200);
        }
        $host = User::find($id);
        if (!$host) {
            return AgencyHelper::apiResponse(0, 'لا يوجد هذا المضيف!',  200);
        }

        if ($host->agency_id != $user->agency_id) {
             return AgencyHelper::apiResponse(0, 'هذا المستخدم ليس في وكالتك!',  200);
        }

        $today = Carbon::today();
        $previousMonth = $today->subMonth();
        $user_sallary = UserSallary::where(['user_id' => $host->id, 'month'=> $previousMonth->format('m'), 'year'=> $previousMonth->format('Y')])->first();
        $join_date = AgencyJoinRequest::where(['user_id' => $host->id, 'agency_id'=>$user->agency_id])->first()?->updated_at;

        $last_month_di = 0;
        if ($user_sallary) {
            $stringWithoutSpaces = str_replace(' ', '', $user_sallary->diamond);
            $parts = explode('/', $stringWithoutSpaces);
            $last_month_di = intval($parts[0]);
        }

        $start_date = now()->startOfMonth();
        $end_date = now();
        $dAilyReport = [];
        $total_hours = 0;
        $total_total_hours = 0;
        $total_diamonds = 0;
        $days = 0;
        for ($date = $start_date; $date <= $end_date; $date->addDay(1)) {
            $hours = LiveTime::query()
                ->where('uid', $host->id)
                ->whereDate('created_at', $date->toDateString())
                ->sum('hours');

            $total_hours += $hours;

            if ($total_hours >= 2) {
                $days++;
                $total_hours = 0;
                $total_total_hours +=$hours;
            }
           $diamonds = GiftLog::query()->selectRaw('receiver_id, SUM(giftNum * giftPrice) AS total')->groupBy("receiver_id")->where('receiver_id', $host->id)->whereDate("created_at",$date)->first();
            $total_diamonds +=  $diamonds?->total ?? 0;
           $dAilyReport[] = [
                'date'          => $date->toDateString(),
                'total_hours'   => $hours,
                'total_days'    => $days,
                'diamond'    => $diamonds?->total ?? 0,
            ];
        }


        $data=[
            'monthly_diamond' => $host->monthly_diamond_received,
            'last_month_diamond' => $last_month_di,
            'date_of_join' => $join_date,
            'last_active' =>  Carbon::parse($host->online_time)->toDateTimeString(),
            'days' =>  $days,
            'total_hours' =>  $total_total_hours,
            'total_diamonds' =>  $total_diamonds,
            'dailyReport' =>  $dAilyReport,
            'host' => [
                'id' => $host->id,
                'uuid' => $host->uuid,
                'name' => $host->name,
                'img' => $host->profile->avatar,
            ]
        ];

        return AgencyHelper::apiResponse(1, '', $data,  200);
    }

    public function agency_data(Request $request)
    {
        $user = $this->get_user(request());
        if (!$user) {
            return AgencyHelper::apiResponse(0, 'لا يوجد مستخدم!',  200);
        }
        if (!$user->ownAgency) {
            return AgencyHelper::apiResponse(0, 'هذا المستخدم لا يمتلك وكاله!',  200);
        }
        $agency = Agency::where("id",$user->agency_id)->first();
        $users      = $agency?->mempers?->pluck("id")->toArray();
        $diamonds   = GiftLog::query()->whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)->whereIn("receiver_id",$users)->sum('giftPrice');
        $days       =  LiveTime::query()
                                ->whereIn('uid', $users)
                                ->whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->groupBy('uid')
                                ->selectRaw('uid, SUM(hours) AS hnum, COUNT(DISTINCT DATE(created_at)) as days')
                                ->havingRaw('SUM(hours) >= 1')
                                ->get()
                                ->sum('days') ?? 0;
        $hours       = LiveTime::query()->where('uid', $users)
                                ->whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->sum('hours');
        $visitors   = ProfileVisitor::query()->whereIn('user_id',$users)
                                ->whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)->count();
        $follows    = Follow::query()->where(fn($q)=>$q->whereIn("followed_user_id",$users)
                                ->orWhere(fn($q2)=>$q2->whereIn("user_id",$users)->where("status",1)))
                                ->whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)->count();
        $friends    =   Follow::query()->where(fn($q)=>$q->whereIn("followed_user_id",$users)->orWhereIn("user_id",$users))
                                ->where("status",1)
                                ->whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)->count();

        $data=[

        ];

        $hosts = $agency->mempers->where("type_user",'!=',0);
        if (request('host_id')) {
            $hosts = $hosts ->where('id',request('host_id'));
        }
        $AllHosts = AgencyHostResource::collection($hosts);
        $monthlyHost = User::where("agency_id",$agency->id)->where("type_user",'!=',0)->whereMonth("join_agency_date",date("m"))->get();
        $month_hosts = AgencyMonthlyHostResource::collection($monthlyHost);
        $totalSalary = $agency->salary;
        $last_salary = $agency->last_month_salary;
        $current_salary = $agency->agencySalary ? $agency->agencySalary->sum(\DB::raw('sallary - cut_amount')) : 0;
        $userSallaries= UserSallary::whereIn("user_id",$hosts->pluck("id")->toArray())->where("month",date("m"))->where("year",date("Y"))->get();
        $total_hosts_achieve= $userSallaries->sum("sallary");
        $total_hosts_percentages= $userSallaries->sum("agency_sallary");
        $data= [
            'id'                =>  $agency->id,
            'name'              =>  $agency->name,
            'notice'            =>  $agency->notice,
            'status'            =>  $agency->status,
            'phone'             =>  $agency->phone,
            'url'               =>  $agency->url,
            'img'               =>  $agency->img,
            'contents'          =>  $agency->contents,
            'diamonds'          =>  $diamonds,
            'hours'             =>  $hours,
            'days'              =>  $days,
            'visitors'          =>  $visitors,
            'friends'           =>  $friends,
            'follows'           =>  $follows,
            'total_profit'      =>  $agency->getTotalSallaryAgency(),
            'total_salary'      =>  $totalSalary,
            'last_salary'       =>  $last_salary,
            'current_salary'    =>  $current_salary,
            'host_sallary'    =>  $total_hosts_achieve,
            'host_percentage'    =>  $total_hosts_percentages,
            'hosts'             =>  $AllHosts,
            'monthly_hosts'     =>  $month_hosts,
        ];
        return AgencyHelper::apiResponse(1, '', $data,  200);
    }



    public function host_agency_edit(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'notice' => 'nullable',
        ]);
        $user = $this->get_user(request());
        if (!$user) {
            return AgencyHelper::apiResponse(0, 'لا يوجد مستخدم!',  200);
        }
        if (!$user->ownAgency) {
            return AgencyHelper::apiResponse(0, 'هذا المستخدم لا يمتلك وكاله!',  200);
        }
        $agency = Agency::where("id",$user->agency_id)->first();
        $agency->name = $request->name;
        $agency->notice = $request->notice;
        $agency->phone = $request->phone;
        $agency->save();
        return AgencyHelper::apiResponse(1, '', $agency,  200);
    }
    public function createAgency(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
            'img' => 'nullable|mimes:jpg,jpeg,png',
            'email' => [
                'nullable',
                'email',
                function ($attribute, $value, $fail) {
                    if ($value && !str_contains($value, '@gmail.com')) {
                        $fail($attribute.__('api.gmail'));
                    }
                },
            ],
            'face_image' => 'required|mimes:jpg,jpeg,png',
            'back_image' => 'required|mimes:jpg,jpeg,png',
            'country' => 'nullable',
            'apps' => 'required',
            'salary' => 'required|integer',
            'host' => 'required|integer',
            'uuid'=> 'nullable|exists:users,uuid',
            'video'=>'nullable|file|mimes:mp4,ogx,oga,ogv,ogg,webm'
        ]);

        if ($validator->fails()) {
            $errors = implode(',',$validator->errors()->all());
            return AgencyHelper::apiResponse(0, $errors,  200);
        }
        $checkAgency = Agency::where(['app_owner_id'=>$request->user()->id,'status' => 0])->first();
        if ($checkAgency) {
            return AgencyHelper::apiResponse(0, 'لقد قمت بتقديم طلب من قبل ولم يتم اتخاذ اي اجراء فيه!',  200);
        }
        $checkUserAgency = Agency::where(['app_owner_id'=>$request->user()->id,'status' => 1])->first();
        if ($checkUserAgency) {
            return AgencyHelper::apiResponse(0, 'انت تملك وكاله بالفعل',  200);
        }
        if ($request->hasFile('img')) {
            $img = $request->file('img');
            $image = AgencyHelper::upload('agency', $img);
        }
        $agency = Agency::create(
            [
                'app_owner_id' => $request->user()->id,
                'name' => $request->input('name'),
                'notice' => $request->input('notice'),
                'status' => 0,
                'phone' => $request->input('phone'),
                'img' => $image ?? null,
                'type' => 1,
            
            ]
        );
        if ($request->hasFile('face_image')) {
            $img = $request->file('face_image');
            $face_image_nationalId = AgencyHelper::upload('nationalId', $img);
        }
        if ($request->hasFile('back_image')) {
            $img = $request->file('back_image');
            $back_image_nationalId = AgencyHelper::upload('nationalId', $img);
        }
        $user = User::query()->searchByUuid($request->user_id)->first();
        if ($request->hasFile('video')) {
            $data        = $request->file('video');
            $video = RealsService::upload($data);
        }
        $additionalInfo = AdditionalInfo::create([
            'agency_id' => $agency->id,
            'gmail' => $request->input('email'),
            'status' => 0,
            'face_image_nationalId' =>  $face_image_nationalId,
            'back_image_nationalId' => $back_image_nationalId,
            'country' => $request->input('country'),
            'history_app_info' => $request->input('apps'),
            'salary' => $request->input('salary'),
            'host' => $request->input('host'),
            'user_id' => $user->id ?? null,
            'video' => $video ?? null,
            'owner_id' =>  $request->user()->id,
        ]);


        $agencyWithAdditionalInfo = Agency::with('additionalInfo')->find($agency->id);
        $gmail = AgencyHelper::getConfig('gmail');
        try {
            Mail::to($gmail)->send(new SendAgencyEmail($agencyWithAdditionalInfo));
        } catch (\Illuminate\Database\QueryException $e) {
            return AgencyHelper::apiResponse(1, __("api_responses.created"),  200);
        }
        return AgencyHelper::apiResponse(1, __("api_responses.created"),  200);
    }


    public function allAgencyRequest()
    {
        $agency = Agency::where('status', 0)->whereHas('additionalInfo', function ($query) {
            $query->where('status', 0);
        })->with('additionalInfo')->get();
        return AgencyHelper::apiResponse(1, '', $agency,  200);
    }

    public function actionRequestAgency(Request $request)
    {
        $agency = Agency::with('additionalInfo')->find($request->agency_id);
        $user = User::find($agency->app_owner_id);
        if (!$agency) AgencyHelper::apiResponse(0, 'agency not found',  404);
        if ($request->status != 1) {

            if ($agency->additionalInfo->gmail) {
                Notification::route('mail',  $agency->additionalInfo->gmail)->notify(new RefuseAgency());
            }
            $agency->delete();

            CustomNotification::refuseRequestAgency($user);
            return AgencyHelper::apiResponse(1, 'deleted request',  200);
        }
        $agency->status = $request->status;

        $agency->save();
        $additionalInfo = AdditionalInfo::where('agency_id', $agency->id)->first();
        $additionalInfo->status = $request->status;
        $additionalInfo->save();
        $appOwnerId = $agency->app_owner_id;
        // type = 1 means host agency, type = 2 means shipping agency
        if ($agency->type == 1) {
            $user = User::find($appOwnerId);
            $user->type_user = 2;
            $user->agency_id = $agency->id;
            $user->save();
        }
        if ($agency->additionalInfo->gmail) {
            Notification::route('mail',  $agency->additionalInfo->gmail)->notify(new AcceptAgency());
        }
        CustomNotification::acceptRequestAgency($user);
        return AgencyHelper::apiResponse(1, 'accept request',  200);
    }

    public function cancel_request_createAgency(Request $request)
    {
        $user = $request->user();
        if ($request->agency_id) {
            $agency = Agency::find($request->agency_id);
        } else {
            $agency = Agency::where('app_owner_id', $user->id)->latest()->first();
        }
        if (!$agency) return AgencyHelper::apiResponse(0, __('not found request'), []);

        if ($agency->status != 0 && $agency->status != 3) return AgencyHelper::apiResponse(0, __("not cancel request"), []);
        if ($agency->status == 3) return AgencyHelper::apiResponse(0, __('you already canceled'), []);

        $time_after24 = Carbon::parse($agency->created_at)->addHours(24);
        if ($agency->created_at > $time_after24) return AgencyHelper::apiResponse(0, __("24 hours have passed since your request"), []);

        $agency->delete();
        AdditionalInfo::where('agency_id', $agency->id)->update(['status' => 3]);
        return AgencyHelper::apiResponse(1, __("The request has been successfully cancelled"), []);
    }

    public function agency_request_info(Request $request)
    {
        $user = Auth::user();
        $additional = AdditionalInfo::where('owner_id', $user->id)->latest()->first();
        $can_make_request = true;
        $can_cancel_request = false;

        if ($additional) {
            $time_after24 = Carbon::parse($additional->created_at)->addHours(24);
            if ($additional->status == 0 || $additional->status == 1) {
                $can_make_request = false;
            }

            if ($additional->status == 0 &&  $additional->created_at <= $time_after24) {
                $can_cancel_request = true;
            }
        }
        $chekAgency = Agency::where("app_owner_id", $user->id)->first();
        if ($chekAgency) {
            $can_make_request = false;
        }
        $data = [
            'can_make_request' => $can_make_request,
            'can_cancel_request' => $can_cancel_request,
        ];
        return AgencyHelper::apiResponse(1, '', $data);
    }

    public function user_agency_information()
    {
        $user   =   Auth::user();
        $month  =   \request('month');
        $year  =   \request('year');

        $agency = Agency::query()->where('app_owner_id', $user->id)->first();
        if (!$agency) return AgencyHelper::apiResponse(0, __("api_responses.u_not_owner_agncy"), []);
        $total_host_target = UserSallary::where('user_agency_id', $agency->id);

        if ($month != null && $year != null) {
            $total_host_target = $total_host_target->where('month', $month)
                ->where('year', $year);
        }
        $total_host_target = $total_host_target->sum('sallary');

        $data = [
            'id'                => $agency->id,
            'name'              => $agency->name,
            'image'             => $agency->img,
            'pio'               => $agency->contents,
            'num_of_hosts'      => $agency->mempers->count(),
            'total_salary'      => $total_host_target,
            'agency_target'     => $agency->getSalary($month, $year),
        ];
        return AgencyHelper::apiResponse(1, '', $data);
    }
}
