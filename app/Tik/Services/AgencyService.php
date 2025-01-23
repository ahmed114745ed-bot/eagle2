<?php

namespace App\Tik\Services;

use Exception;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Admin;
use App\Helpers\Common;
use App\Helpers\UserCommon;
use Illuminate\Support\Str;
use App\Facades\UserHandling;
use Illuminate\Support\Facades\DB;
use App\Facades\CustomNotification;
use App\Notifications\AcceptAgency;
use App\Notifications\RefuseAgency;
use http\Exception\RuntimeException;
use Illuminate\Support\Facades\Hash;
use App\Notifications\AgencyOwnerRole;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\CValidationException;
use App\Tik\Repositories\UserRepository;
use App\Tik\Repositories\AdminRepository;
use App\Tik\Repositories\AgencyRepository;
use App\Tik\Repositories\FollowRepository;
use App\Tik\Repositories\TargetRepository;
use App\Tik\Repositories\GiftLogRepository;
use App\Tik\Repositories\HistoryRepository;
use App\Tik\Repositories\LiveTimeRepository;
use Illuminate\Support\Facades\Notification;
use Modules\Reals\Http\Services\RealsService;
use App\Tik\Repositories\UserSalaryRepository;
use Illuminate\Validation\ValidationException;
use App\Tik\Repositories\AgencySalaryRepository;
use App\Tik\Repositories\ChargeAgencyRepository;
use App\Tik\Repositories\AgencyUserJobRepository;
use App\Tik\Repositories\AdditionalInfoRepository;
use App\Tik\Repositories\ProfileVisitorRepository;
use App\Tik\Repositories\AgencyJoinRequestRepository;
use App\Tik\Repositories\LeaveAgencyRequestRepository;
use Modules\AgencyApp\Transformers\AgencyHostResource;
use App\Http\Resources\Api\V1\AgancyCurantMonthResource;
use App\Http\Resources\Api\V1\MyDataForAgencyNewResource;
use Modules\AgencyApp\Transformers\AgencyMonthlyHostResource;



class AgencyService
{
    public function __construct(
        private readonly AgencyRepository $agencyRepository,
        private readonly AgencyJoinRequestRepository $agencyJoinRequestRepository,
        private readonly AgencyUserJobRepository $agencyUserJobRepository,
        private readonly UserRepository $userRepository,
        private readonly AgencySalaryRepository $agencySalaryRepository,
        private readonly UserSalaryRepository $userSalaryRepository,
        private readonly TargetRepository $targetRepository,
        private readonly HistoryRepository $historyRepository,
        private readonly AdditionalInfoRepository $additionalInfoRepository,
        private readonly LiveTimeRepository $liveTimeRepository,
        private readonly GiftLogRepository $giftLogRepository,
        private readonly ProfileVisitorRepository $profileVisitorRepository,
        private readonly FollowRepository $followRepository,
        private readonly LeaveAgencyRequestRepository $leaveAgencyRequestRepository,
        private readonly AdminRepository $adminRepository,
        private readonly ChargeAgencyRepository $chargeAgencyRepository,

    ) {}


    public function joinAgency($user, $request)
    {
        $agencyId = $request->agency_id;
        $agency = $this->agencyRepository->findById($agencyId);
        if (!$agency) throw new Exception(__('api_responses.agency'));
        if ($agency->status == 0) throw new \Exception(__('api_responses.agencyDown'));

        $joined = $user->agency_id;
        if ($joined) throw new \Exception(__('api_responses.you_are_already_under_agency'));
        $countRequest = $this->agencyJoinRequestRepository->countByMonth($user->id);
        if ($countRequest > 5)   throw new \Exception(__('api_responses.you_have_+5_requests_not_allowed_to_request_other_more'));
        $agency_request = $this->agencyJoinRequestRepository->countByAgency($user->id, $agencyId);
        if ($agency_request > 0)  throw new \Exception(__('api_responses.you_already_send_request_to_this_agency'));

        $data = [
            'user_id' => $user->id,
            'agency_id' => $agencyId,
            'whatsapp' => $request->whatsapp,

        ];
        $this->agencyJoinRequestRepository->create($data);

        $requests = $this->agencyJoinRequestRepository->getByUser($user->id);
        return $requests;
    }

    public function find($agencyId)
    {
        $agency = $this->agencyRepository->findById($agencyId);
        if (!$agency) throw new Exception(__('api_responses.agency'));
        return $agency;
    }

    public function agencyMembers($agencyId)
    {
        $agency = $this->agencyRepository->findById($agencyId);
        if (!$agency) throw new Exception(__('api_responses.agency'));
        return $this->agencyRepository->members($agency);
    }

    public function showRequests($userId)
    {
        $admin = $this->agencyUserJobRepository->findByUserId($userId);
        if ($admin) {
            $agency = $this->agencyRepository->findById($admin->agency_id);
        } else {
            $agency = $this->agencyRepository->findAgencyByOwnerId($userId);
        }
        if (!$agency) throw new Exception('u_not_have_agncy');
        $requestList = $this->agencyJoinRequestRepository->getByAgencyId($agency->id);

        return $requestList;
    }

    public function requestAction($owner, $request)
    {
        $accept    = $request->accept;
        $user = $this->userRepository->findById($request->user_id);

        $admin = $this->agencyUserJobRepository->findByUserId($owner->id);
        if ($admin) {
            $agency = $this->agencyRepository->findById($admin->agency_id);
        } else {
            $agency = $this->agencyRepository->findAgencyByOwnerId($owner->id);
        }
        if (!$agency) throw new Exception('u_not_owner_agncy');

        $action = $this->agencyJoinRequestRepository->findRequest($user->id, $agency->id);


        if (!$action) throw new Exception('Request not found');

        if ($accept == 0) {
            $action->status = 2;
            $action->save();
        } elseif ($accept == 1) {
            $action->status = 1;
            $action->save();
            $this->userRepository->updateTypeUser($user);
            // add vip to user
            UserCommon::userVip($user);
            CustomNotification::acceptAgencyApp($agency, $user);
        }
        return true;
    }

    public function listOption($agencyId)
    {
        $agency = $this->agencyRepository->getWithSelectMonthAndYear($agencyId);

        $createdAt   = $agency->created_at;
        $currentDate = now();

        $monthsToInclude = [];
        while ($createdAt <= $currentDate) {
            $month = $createdAt->format('m');
            $year  = $createdAt->format('Y');

            // Only add if the month and year are not the same as the current date
            if ($month !== $currentDate->format('m') || $year !== $currentDate->format('Y')) {
                $monthsToInclude[] = [
                    'month' => $month,
                    'year' => $year,
                ];
            }
            // Clone the date object before adding the month
            $createdAt = clone $createdAt;
            $createdAt->addMonth(); // Move to the next month
        }

        // Add the current month and year
        $monthsToInclude[] = [
            'month' => $currentDate->format('m'),
            'year' => $currentDate->format('Y'),
        ];

        return $monthsToInclude;
    }


    public function historySearch($agencyId, $request)
    {
        $month        = $request->month;
        $year         = $request->year;
        $CurrentMonth = date('m'); // Get the current month as a two-digit number (e.g., 08 for August)
        $CurrentYear  = date('Y');
        if ($CurrentYear == $year && $CurrentMonth == $month) {
            $perPage = 15;                 // Number of items per page
            $page    = request('page', 1); // Get the current page number from the request, default to 1
            $dataQuery = $this->userRepository->findUsersByAgencyIdI($agencyId);
            $paginatedData = $dataQuery->paginate($perPage, ['*'], 'page', $page);
            $data =  $dataQuery->with('userSallary')->get();
            $totalDiamond = $data->sum('monthly_diamond_received');
        } else {
            $data = $this->historyRepository->getByMonthAndYear($agencyId, $month, $year);
            $totalDiamond = $data->sum('diamond');
            $perPage = 15;                 // Number of items per page
            $page    = request('page', 1); // Get the current page number from the request, default to 1
            $paginatedData   = $data->paginate($perPage, ['*'], 'page', $page);
        }
        $transformedData = AgancyCurantMonthResource::collection($paginatedData);

        $target   = $this->userSalaryRepository->getSumByMonthAndYear($agencyId, $month, $year, 'agency_sallary');

        $minValue = $this->targetRepository->getByUsd($target);


        $totalusd = $this->userSalaryRepository->getSumByMonthAndYear($agencyId, $month, $year, 'sallary');
        $total    = (@$minValue->agency_share / 100) * $totalusd;

        $agencySalary = $this->agencySalaryRepository->findByMonthAndYear($agencyId, $month, $year);
        $total = $agencySalary?->sallary ?? 0;
        $responseData = [
            'sum'             => $totalDiamond ?: 0,
            'sum_usd' => $totalusd ?: 0, // 'owner_usd' => $curant?:0,
            'Total_owner_usd' => $total ?: 0,
            'users' => $transformedData ?: 0,

        ];
        return $responseData;
    }

    public function update($userId, $agencyId, $request)
    {

        $agency = $this->agencyRepository->findById($agencyId);
        if (!$agency) throw new Exception(__('api_responses.agency'));

        if ($agency->app_owner_id != $userId)  throw new Exception(__('api_responses.agency_app_owner'));


        if ($request->name != null) {
            $agency->name = $request->name;
        }

        if ($request->contents != null) {
            $agency->contents = $request->contents;
        }

        if ($request->get('content') != null) {
            $agency->notice = $request->get('content');
        }

        if ($request->hasFile('img')) {
            if ($agency->img && Storage::exists($agency->img)) {
                Storage::delete($agency->img);
            }

            $img = $request->file('img');
            $image = Common::upload('agency', $img);
            $agency->img = $image;
        }

        $agency->save();

        return $agency;
    }

    public function userHandlingRequest($userId, $agencyId)
    {
        $operator = $this->userRepository->findById($userId);

        if ($agencyId != $operator->agency_id) throw new CValidationException('يجب ان يكون المستخدم في الوكاله!');

        if ($this->agencyUserJobRepository->exists($userId, $agencyId)) {
            throw new CValidationException(__('This user already has an agency job requested!'));
        }

        $data = [
            'agency_id' => $agencyId,
            'user_id' => $operator->id,
            'type' => "requestManger",
        ];
        $this->agencyUserJobRepository->create($data);
        return true;
    }


    public function create($userId, $request)
    {
        $checkAgency = $this->agencyRepository->findAgencyByOwnerId($userId, 0);
        if ($checkAgency)   throw new Exception('لقد قمت بتقديم طلب من قبل ولم يتم اتخاذ اي اجراء فيه!');

        $checkUserAgency =  $this->agencyRepository->findAgencyByOwnerId($userId, 1);

        if ($checkUserAgency) throw new Exception('انت تملك وكاله بالفعل');

        if ($request->hasFile('img')) {
            $img = $request->file('img');
            $image = Common::upload('agency', $img);
        }

        $data = [
            'app_owner_id' => $request->user()->id,
            'name' => $request->input('name'),
            'notice' => $request->input('notice'),
            'status' => 0,
            'phone' => $request->input('phone'),
            'img' => $image ?? null,
            'Host_agency' => true,
            'Shipping_agency' => false,
        ];
        $agency = $this->agencyRepository->create($data);

        if ($request->hasFile('face_image')) {
            $img = $request->file('face_image');
            $face_image_nationalId = Common::upload('nationalId', $img);
        }
        if ($request->hasFile('back_image')) {
            $img = $request->file('back_image');
            $back_image_nationalId = Common::upload('nationalId', $img);
        }
        $user = $this->userRepository->searchUser($request->user_id);

        if ($request->hasFile('video')) {
            $data        = $request->file('video');
            $video = RealsService::upload($data);
        }
        $dataInfo = [
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
        ];
        $this->additionalInfoRepository->create($dataInfo);
        $agencyWithAdditionalInfo = $this->agencyRepository->findById($agency->id);
        return $agencyWithAdditionalInfo;
    }

    public function actionRequestAgency($request)
    {
        $agency = $this->agencyRepository->findById($request->agency_id);
        if (!$agency)  throw new Exception('agency not found');
        $user = $this->userRepository->findById($agency->app_owner_id);

        if ($request->status != 1) {

            if ($agency->additionalInfo->gmail) {
                Notification::route('mail',  $agency->additionalInfo->gmail)->notify(new RefuseAgency());
            }
            $agency->delete();

            CustomNotification::refuseRequestAgency($user);
            return true;
        }
        $this->agencyRepository->updateStatus($agency, $request->status);

        $additionalInfo = $this->additionalInfoRepository->findByAgencyId($agency->id);
        $additionalInfo->status = $request->status;
        $additionalInfo->save();

        $Host_agency = $agency->Host_agency;
        if ($Host_agency == 1) {
            $user->type_user = 2;
            $user->agency_id = $agency->id;
            $user->save();
        }
        if ($agency->additionalInfo->gmail) {
            Notification::route('mail',  $agency->additionalInfo->gmail)->notify(new AcceptAgency());
        }
        Common::createUserAdmin($agency->app_owner_id);
        CustomNotification::acceptRequestAgency($user);
        return true;
    }

    public function allRequest()
    {
        return $this->agencyRepository->getByAdditionalInfo();
    }

    public function historyLastThirtyDays($userUUId)
    {
        $user = $this->userRepository->searchUser($userUUId);
        $endDate = now();
        $startDate = now()->subDays(30);
        $data = [];

        $total_days = 0;
        $total_hours = 0;
        $total_diamonds = 0;
        for ($date = $startDate; $date->lessThanOrEqualTo($endDate); $date->addDay()) {
            $day        = $this->liveTimeRepository->sumDays($user->id, $startDate, $endDate, $date->toDateString());
            $hour       = $this->liveTimeRepository->SumHours($user->id, $startDate, $endDate, $date->toDateString());

            $diamond    = $this->giftLogRepository->sumGiftPriceByReceiver($user->id, $startDate, $endDate, $date->toDateString());


            $total_days += $day;
            $total_hours += $hour;
            $total_diamonds += $diamond;
            $data[] = [
                'date'      =>  $day,
                'days'      =>  $day,
                'hours'     =>  $hour,
                'diamonds'  =>  $diamond,
            ];
        }
        $data[] = [
            'total_days'    => $total_days,
            'total_hours'   => $total_hours,
            'total_diamonds' => $total_diamonds,
        ];

        return $data;
    }

    public function agencyReport($agencyId)
    {
        $userIds      = $this->userRepository->getIdsByAgencyId($agencyId);

        [$diamonds, $days, $hours, $visitors, $follows, $friends] = $this->details($userIds);

        return  $data = [
            'diamonds'    =>  $diamonds,
            'hours'       =>  $hours,
            'days'        =>  $days,
            'visitors'    =>  $visitors,
            'friends'     =>  $friends,
            'follows'     =>  $follows,
        ];
    }

    public function details($userIds)
    {
        $diamonds   = $this->giftLogRepository->totalUsersGiftPrice($userIds);
        $days       = $this->liveTimeRepository->totalUsersHoursDays($userIds, 'days');

        $hours       = $this->liveTimeRepository->totalUsersHoursDays($userIds, 'hours');
        $visitors   = $this->profileVisitorRepository->countUsersByYearAbdMonth($userIds);
        $follows    = $this->followRepository->countFollows($userIds);
        $friends    =  $this->followRepository->countFriends($userIds);
        return [$diamonds, $days, $hours, $visitors, $follows, $friends];
    }

    public function leaveAgency($userId, $agency)
    {
        $check = $this->leaveAgencyRequestRepository->getOldRequest($agency->id, $userId);
        if ($check) throw new Exception("هناك طلب من قبل !");
        $data = [
            'agency_id' =>  $agency->id,
            'user_id'   =>  $userId,
            'admin_id'  =>  $agency->owner_id,
            'status'    =>  0,
        ];
        $this->leaveAgencyRequestRepository->create($data);
        return true;
    }

    public function handlingRequest($agencyId, $userId)
    {
        $operator = $this->userRepository->findById($userId);

        if ($agencyId != $operator->agency_id) throw new Exception('يجب ان يكون المستخدم في الوكاله!');
        $data = [
            'agency_id' => $agencyId,
            'user_id' => $operator->id,
            'type' => "requestManger",
        ];
        $this->agencyUserJobRepository->create($data);
        return true;
    }

    public function kickAgency($auth, $userId)
    {
        $user_kicked = $this->userRepository->findById($userId);

        if ($user_kicked->agency_id != $auth->ownAgency->id || $user_kicked->id == $auth->ownAgency->app_owner_id) throw new Exception('لا يمكنك ازاله هذا المستخدم!');
        UserHandling::kickUserFromAgency($user_kicked);
        return true;
    }

    public function filter($keyword)
    {
        $agencies = $this->agencyRepository->getAgencyByFilter($keyword);

        $agencyManger = $this->userRepository->getAgencyMangerByFilter($keyword);
        return [$agencies, $agencyManger];
    }

    public function dailyReport($user, $month, $year)
    {
        $dailyDiamonds = $this->giftLogRepository->getByDaily($user->id, $user->agency_id, $month, $year);


        $dailyTimes = $this->liveTimeRepository->getByDaily($user->id, $month, $year);

        $dailyDiamonds = $dailyDiamonds->map(function ($data) {
            $data->day = Carbon::parse($data->date)->day;
            return $data;
        });
        $dailyTimes = $dailyTimes->map(function ($data) {
            $data->day = Carbon::parse($data->date)->day;
            return $data;
        });
        /** @var User $user */
        $totalDays = $user->getTotalDays();

        $userInfoArray = $user->getSallaryInfo();

        $totalSalary = @$userInfoArray['total_salary'] ?? 0;
        $totalCutAmount = @$userInfoArray['total_cut_amount'] ?? 0;

        $isThisMonth = $month == now()->month && $year == now()->year;
        $startDay = 1;
        $endDay = Carbon::create($year, $month)->endOfMonth()->day;

        if ($isThisMonth) $endDay = today()->day;

        // \Log::info('this2 is error');
        $hours = $dailyTimes->sum('hours');
        $minutes = $hours * 60;
        $data = [
            'user_salary' => [
                'cut_amount' => (int)$totalCutAmount,
                'salary' =>  intval($totalSalary),
            ],
            'request_leave_agency' => $this->leaveAgencyRequestRepository->getRequest($user->id, $user->agency_id),
            'diamonds' => numToStringNew($dailyDiamonds->sum('diamonds')),
            'live_minutes' => (string)$minutes,
            'active_days' => (string)$totalDays,
            'daly_reports' => []
        ];
        for (; $startDay <= $endDay; $startDay++) {
            $hours = $dailyTimes->where('day', $startDay)->first()?->hours ?? 0;
            $minutes = $hours * 60;
            $diamonds = $dailyDiamonds->where('day', $startDay)->first()?->diamonds ?? 0;
            $data['daly_reports'][] = [
                'day' => (int)$startDay,
                'live_minutes' => (int)$minutes,
                'diamonds' => numToString((int)$diamonds),
                'is_active_day' => $hours >= 1,
            ];
        }

        return $data;
    }


    public function dataAgency()
    {
        $user = $this->get_user(request());
        if (!$user)  throw new Exception('لا يوجد مستخدم!');

        if (!$user->ownAgency)  throw new Exception('هذا المستخدم لا يمتلك وكاله!');
        $agency = $this->agencyRepository->findById($user->agency_id);
        $userIds    = $this->agencyRepository->userMembers($agency);

        [$diamonds, $days, $hours, $visitors, $follows, $friends] = $this->details($userIds);

        $hosts = $agency->mempers->where("type_user", '!=', 0);
        if (request('host_id')) {
            $hosts = $hosts->where('id', request('host_id'));
        }
        $AllHosts = AgencyHostResource::collection($hosts);
        $monthlyHost = $this->userRepository->getUsersByJoinAgency($agency->id);
        $month_hosts = AgencyMonthlyHostResource::collection($monthlyHost);
        $totalSalary = $agency->salary;
        $last_salary = $agency->last_month_salary;
        $current_salary = $agency->agencySalary ? $agency->agencySalary->sum(\DB::raw('sallary - cut_amount')) : 0;

        $total_hosts_achieve = $this->userSalaryRepository->sum($hosts->pluck("id")->toArray(), 'sallary');

        $total_hosts_percentages = $this->userSalaryRepository->sum($hosts->pluck("id")->toArray(), 'agency_sallary');

        return [
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
    }

    public function hostReport($id)
    {
        $user = $this->get_user(request());
        if (!$user)  throw new Exception('لا يوجد مستخدم!');
        $host = $this->userRepository->findById($id);
        if (!$host)  throw new Exception('لا يوجد هذا المضيف!');
        if ($host->agency_id != $user->agency_id)  throw new Exception('هذا المستخدم ليس في وكالتك!');


        $today = Carbon::today();
        $previousMonth = $today->subMonth();
        $userSalary = $this->userSalaryRepository->getByUser($host->id, $previousMonth->format('m'), $previousMonth->format('Y'));
        $joinDate = $this->agencyJoinRequestRepository->findByUsersAndAgency($host->id, $user->agency_id)?->updated_at;

        $last_month_di = 0;
        if ($userSalary) {
            $stringWithoutSpaces = str_replace(' ', '', $userSalary->diamond);
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
            $hours = $this->liveTimeRepository->sumUserHoursByDate($host->id, $date->toDateString());
            $total_hours += $hours;

            if ($total_hours >= 2) {
                $days++;
                $total_hours = 0;
                $total_total_hours += $hours;
            }
            $diamonds = $this->giftLogRepository->getByDate($host->id, $date);
            $total_diamonds +=  $diamonds?->total ?? 0;
            $dAilyReport[] = [
                'date'          => $date->toDateString(),
                'total_hours'   => $hours,
                'total_days'    => $days,
                'diamond'    => $diamonds?->total ?? 0,
            ];
        }


        return [
            'monthly_diamond' => $host->monthly_diamond_received,
            'last_month_diamond' => $last_month_di,
            'date_of_join' => $joinDate,
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
    }

    public function hostDailyReport($request)
    {
        $user = $this->get_user($request);
        if (!$user)  throw new Exception('لا يوجد مستخدم!');

        if (!$user->agency)  throw new Exception('لا تملك وكاله!');

        $hosts = $this->userRepository->getByHost($user->agency_id, $request->host_id);

        return $hosts;
    }

    public function editAgency($request)
    {
        $user = $this->get_user(request());
        if (!$user)  throw new Exception('لا يوجد مستخدم!');
        if (!$user->ownAgency)  throw new Exception('هذا المستخدم لا يمتلك وكاله!');
        $agency = $this->agencyRepository->findById($user->agency_id);
        $data = [
            'name' => $request->name,
            'notice' => $request->notice,
            'phone' => $request->phone,
        ];
        $this->agencyRepository->update($data, $agency->id);
        return $agency;
    }



    public function get_user($request)
    {
        if ($request->user_id) {
            $admin = $this->adminRepository->findById($request->user()->id);
            if (isset($admin) && $admin->isRole("admin")) {
                $user = $this->userRepository->findById($request->user_id);
            } else {
                $user = null;
            }
        } else {
            $user = $this->userRepository->findById($request->user()->id);
        }
        return $user;
    }

    public function showAgencyRequest($user, $type)
    {
        // التحقق مما إذا كان المستخدم هو المدير
        $admin = $this->agencyRepository->getAdminByUserId($user->id);
        if ($admin) {
            $agency = $this->agencyRepository->getAgencyById($admin->agency_id);
        } else {
            $agency = $this->agencyRepository->getAgencyByOwnerId($user->id);
        }

        if (!$agency) {
            return Common::apiResponse(0, __('api_responses.notAdmin'));
        }

        $agency_id = $agency->id;
        $list_req = $this->agencyRepository->getJoinRequests($agency_id);

        if ($type == "application") {
            $list_req1 = $list_req->where('status', 0)->with('user')->get();
            $list_req = MyDataForAgencyNewResource::collection($list_req1, 'application');
        } elseif ($type == "record") {
            $list_req = $list_req->where('status', '!=', 0)->with('user', 'admin')->get();
            $list_req = MyDataForAgencyNewResource::collection($list_req, 'record');
        }

        if ($list_req) {
            return $list_req;
        }

        return [];
    }

    public function allRequests($id, $uuid, $perPage, $page, $status, $action)
    {
        return $this->agencyRepository->getByAdditionalInfoPaginate($id, $uuid, $perPage, $page, $status, $action);
    }

    public function allAgencyCharged()
    {
        $agencyIds = $this->chargeAgencyRepository->all();
        return $this->agencyRepository->getByIds($agencyIds);
    }
}
