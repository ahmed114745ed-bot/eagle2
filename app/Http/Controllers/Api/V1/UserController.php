<?php

namespace App\Http\Controllers\Api\V1;

use Auth;
use Exception;
use App\Models\User;
use App\Models\Agency;
use App\Helpers\Common;
use App\Models\UserSallary;
use Illuminate\Http\Request;
use App\Facades\UserHandling;
use App\Services\UserService;
use Illuminate\Validation\Rule;
use App\Http\Services\WhatsappOtp;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Resources\Api\V1\MyDataResource;
use App\Http\Resources\Api\V1\MyStoreResource;
use App\Http\Services\ProfileRelationsService;
use App\Http\Resources\Api\V1\UserTypeResource;
use Modules\WhatsappAuth\Services\WhatsappWebhook;
use Modules\SalaryTransaction\Entities\SalaryRequest;
use App\Http\Resources\Api\V1\ShowUserSettingResource;
use App\Http\Resources\Api\V1\ZegoCreditionalResource;
use App\Models\Target;
use DB;
use Modules\Achievement\Http\Services\UserAchievementService;
use Modules\Achievement\Transformers\UserAchievementLevelsResource;
use Modules\FixedTarget\Services\FixedTargetService;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function chargerAgincy(Request $request, ProfileRelationsService $profileRelationsService)
    {
        $users = User::where('type_user', 3)->orWhere('type_user', 4)->orderByDesc('id')->paginate(10);
        $usersType = UserTypeResource::collection($users);
        [$senderLevels, $receivedImage] = $profileRelationsService->getLevelsSenderAndReceiver($usersType);
        UserTypeResource::initializeData($senderLevels, $receivedImage, null);
        $data = UserTypeResource::collection($usersType);
        return Common::apiResponse(1, '', $usersType);
    }

    public function showSetting(Request $request)
    {
        $user = $request->user();
        $sitting = $this->userService->setting($user->id, $request);

        return Common::apiResponse(1, 'تم التعديل بنجاح', new ShowUserSettingResource($sitting));
    }

    public function user_statistic(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'date' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            // Other validation rules...
        ]);

        if ($validator->fails()) {
            return Common::apiResponse(false, 'date format error');
        }
        $user = $request->user();

        try {
            $date = \Carbon\Carbon::parse($request->date);
        } catch (Exception $e) {
        }

        $month = $date?->month ?? now()->month;
        $year = $date?->year ?? now()->year;

        if (now()->month == $month && now()->year == $year) {
            (new FixedTargetService($user))->calculateTarget();
        }
        $totalSalary = UserSallary::query()->where('user_id', $user->id)
            ->where(function ($query) use ($year, $month) {
                $query->where(DB::raw('concat(year,"-", month)'), '<=', $year . '-' . $month);
            })
            ->sum(DB::raw('sallary - cut_amount'));

        $user_sallary = UserSallary::query()->where('user_id', $user->id)
            ->where('month', $month)
            ->where('year', $year)
            ->orderByDesc('id')
            ->first();


        $total_usd = 0;
        $current_total_hour = "0 / 0";
        $current_total_day = "0 / 0";
        $current_diamond = "0 / 0";
        if ($user_sallary != null) {
            $total_usd          = $totalSalary;
            $current_total_hour = $user_sallary->hours;
            $current_total_day  = $user_sallary->days;
            $diamonds            = $user_sallary->diamond;
            $current_diamond    = $diamonds ?? $current_diamond;
            if ($diamonds) {
                $stringWithoutSpaces = str_replace(' ', '', $diamonds);
                $parts = explode('/', $stringWithoutSpaces);

                // Convert the parts to integers
                $firstNumber = intval($parts[0]);
                //                $secondNumber = intval($parts[1]);
                $nextTarget = Target::query()->where('diamonds', '>', $firstNumber)->orderBy('diamonds')->first();
                if ($nextTarget) {
                    $current_diamond = $firstNumber . ' / ' . $nextTarget->diamonds;
                }
            }
        }
        $data = [
            "total_diamond" => $user->total_diamond_received,
            "total_usd" => floor($total_usd),
            "current_total_hour" => $current_total_hour,
            "current_total_day" => $current_total_day,
            "diamond" => $current_diamond,
        ];
        return Common::apiResponse(true, '', $data, 200);
    }

    public function app_setting()
    {
        $user = auth()->user();
        $chat_status = settings()->get('chat_status');
        $showChat = $user->userSetting?->hide_chat ?? $chat_status;
        $stop_invite_code = settings()->get('stop_invite_code');
        if ($stop_invite_code == 1) {
            $invite_code = true;
        } else {
            $invite_code = false;
            if ($user->userSetting->show_invite_code == 1) {
                $invite_code = true;
            }
        }
        //        $shared = Common::getConfig('shared') ?? '1234';
        $data = [
            'version' => [
                'android_version'   => settings()->get('android_current_version'),
                'ios_version'       => settings()->get('ios_current_version'),
                'huawei_version'    => settings()->get('huawei_current_version'),
            ],
            'hide_invite'       => $invite_code,
            'show_chat'         => ($chat_status == null ? false : ($showChat == 0 ? false : true)),
            'shared_key' => Common::getConfig('shared') ?? '1234',
            'stop_transfer_salary' => settings()->get('transfer_salary') == 0 ? $user->transfer_salary : (settings()->get('transfer_salary') == 1 ? true : false),
            'have_pending_request' => SalaryRequest::where("status", 2)->where("host_id", $user->id)->first() != null ? true : false,
        ];
        return Common::apiResponse(true, '', $data, 200);
    }
    public function search(Request $request)
    {
        $key = $request->search;
        $users = $this->userService->searchUsers($key);

        return response()->json($users);
    }

    public function search2(Request $request)
    {
        $key = $request->q;
        $page = $request->get('page', 1);
        $users = $this->userService->searchUsersWithPage($key, $page);

        return response()->json($users);
    }

    public function userAgency(Request $request)
    {
        $key = $request->q;
        $page = $request->get('page', 1);
        $users = $this->userService->searchUsersInAgency($key, $page);

        return response()->json($users);
    }

    public function joinAccount(Request $request)
    {
        $user = $request->user();
        try {
            $this->userService->bind($user, $request);
        } catch (Exception $e) {
            return Common::apiResponse(false, $e->getMessage(), null, 407);
        }
        return Common::apiResponse(1, 'bind successful', new UserResource($user));
    }

    public function my_data(Request $request)
    {
        $user = $request->user();

        $userWithMedals = $this->userService->processUserData($user, $request->header('device_token'), $request->header('lat'), $request->header('long'));

        $this->userService->unlockDressHand($user->id);
        request()->default_background = \DB::table('backgrounds')->where('enable', 1)->orderBy('id', 'asc')->limit(1)->first()->img;

        $data = new MyDataResource($userWithMedals);
        return Common::apiResponse(true, '', $data, 200);
    }

    public function userFriend(Request $request)
    {
        $user = $request->user();
        return $this->userService->handleUserRelations($user, $request->type);
    }

    public function follow(Request $request)
    {
        return $this->userService->followUser($request);
    }

    public function unFollow(Request $request)
    {
        return $this->userService->unFollowUser($request);
    }

    public function my_store_all(Request $request)
    {
        $user = $request->user();
        $user = $this->userService->myStore($user, $request);
        $data = new MyStoreResource($user);
        return Common::apiResponse(true, '', $data, 200);
    }

    public function ranking_room(Request $request)
    {
        $toArray =  $this->userService->roomRanking($request);
        return Common::apiResponse(1, '', $toArray);
    }

    public function show(Request $request, $id)
    {
        $isVisit = @$request->is_visit == 'true' ? true : false;
        $auth   = $request->user();
        try {

            $user = $this->userService->showUser($id, $auth, $request, $isVisit);
        } catch (Exception $e) {
            return Common::apiResponse(false, $e->getMessage(), null, 407);
        }
        return Common::apiResponse(true, '', new UserResource($user), 200);
    }


    public function changePhoneWhatsapp(Request $request, WhatsappWebhook $whatsappWebhook)
    {
        $phone = $request->phone;
        if (!$phone) return Common::apiResponse(0, 'missing params', null, 422);
        $user = $request->user();
        $rules = [
            'phone' => [
                'required',
                Rule::unique('users', 'phone')->withoutTrashed()->ignore($user->id),
            ],
        ];
        if ($user->phone == $phone) return Common::apiResponse(0, 'Old phone is wrong', null, 404);
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Common::apiResponse(0, 'Validation failed', $validator->errors(), 422);
        }

        $whatsappWebhookValidate = $whatsappWebhook->getLastValidatedPhone($phone);
        if (!$whatsappWebhookValidate) {
            return Common::apiResponse(false, __('current phone not verified'));
        }

        $user->phone = $phone;

        $user->save();
        return Common::apiResponse(1, 'reset successful', new UserResource($user));
    }

    public function resetWhatsapp(Request $request, WhatsappWebhook $whatsappWebhook)
    {
        $phone = $request->phone;
        if (!$phone || !$request->password) return Common::apiResponse(0, 'missing params', null, 422);
        $user = $request->user();


        if ($user->phone != $phone) return Common::apiResponse(0, 'phone number not register with your account', null, 404);

        $rules = [
            'phone' => [
                'required',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Common::apiResponse(0, 'Validation failed', $validator->errors(), 422);
        }
        try {
            $this->userService->resetWhatsapp($request, $whatsappWebhook);
        } catch (Exception $e) {
            return Common::apiResponse(false, $e->getMessage(), null, 407);
        }
        return Common::apiResponse(1, 'reset successful', new UserResource($user));
    }

    public function userWithSearch(Request $request)
    {
        $data = $this->userService->allUsers($request->search);
        return Common::apiResponse(1, '', $data);
    }

    public function userInfoWithRole(Request $request) {}


    public function logout(Request $request)
    {
        $user = $request->user();
        $user->is_logout = 1;
        $user->save();
        $user->currentAccessToken()->delete();
        return Common::apiResponse(1, 'logged out');
    }

    public function user_agency_information()
    {
        $user   =   Auth::user();
        $month  =   \request('month');
        $year  =   \request('year');

        $agency = Agency::query()->where('app_owner_id', $user->id)->first();
        if (!$agency) return Common::apiResponse(0, __("api_responses.u_not_owner_agncy"), []);
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
        return Common::apiResponse(1, '', $data);
    }

    public function changePhone(Request $request)
    {
        if (!$request->phone  || !$request->current_phone || !$request->old_code || !$request->new_code) return Common::apiResponse(0, 'missing params', null, 422);
        $user = $request->user();
        $rules = [
            'phone' => [
                'required',
                Rule::unique('users', 'phone')->withoutTrashed()->ignore($user->id),
            ],
        ];
        if ($user->phone != $request->current_phone) return Common::apiResponse(0, 'Old phone is wronge', null, 404);
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Common::apiResponse(0, 'Validation failed', $validator->errors(), 422);
        }
        $whatsappOtpService = new WhatsappOtp();
        $phone              = $request->phone;
        $isValidOld            = $whatsappOtpService->isValidate($request->current_phone, $request->old_code);
        $isValidNew            = $whatsappOtpService->isValidate($phone, $request->new_code);
        if (!$isValidOld) {
            return Common::apiResponse(false, __('api_responses.invalid_old_code'));
        }
        if (!$isValidNew) {
            return Common::apiResponse(false, __('api_responses.invalid_new_code'));
        }
        $whatsappOtpService->resetCodes($phone);

        $user->phone = $request->phone;


        $user->save();
        return Common::apiResponse(1, 'reset successful', new UserResource($user));
    }

    public function get_users_support()
    {
        $userId = \request('user_id');

        $results = $this->userService->supporter($userId);


        $achievement = new UserAchievementService();

        $previousTotal = null;
        $data          = $results->map(function ($result) use ($achievement, &$previousTotal) {

            $image         = optional(optional($result->sender)->profile)->avatar ?? '';
            $currentTotal  = $result->total;
            $totalDiff     = isset($previousTotal) ? $previousTotal - $currentTotal : 0;
            $previousTotal = $currentTotal;
            $frame         =
                Common::getUserDress($result->sender?->id, $result->sender?->dress_1, 4, 'img2', true) ?: Common::getUserDress($result->sender?->id, $result->sender?->dress_1, 4, 'img1', true);
            return [
                'id'           => $result->sender_id,
                'uuid'         => $result->sender?->uuid,
                'name'         => $result->sender?->name,
                'image'        => $image,
                'gender'       => $result->sender?->gender,
                'achievements'  => UserAchievementLevelsResource::collection($achievement->getUserAchievement($result->sender)),
                'sender_level' => $result->sender->total_sender_level ?? 0,
                'receiver_level' => $result->receiver->total_received_level ?? 0,
                'total'        => numToString($currentTotal),
                'total_diff'   => $totalDiff,
                'frame'        => $frame,
                'frame_id'     => $frame != '' ? @$result->sender->dress_1 : 0,
                'vip'     => $result->sender?->userVip?->level,
            ];
        })->all();

        $toArray      = $data;
        $countData    = count($data);
        $arr['top']   = $countData < 4 ? $data : array_slice($toArray, 0, 3);
        $arr['other'] = $countData < 4 ? [] : array_slice($toArray, 3);
        $arr['count'] = $countData;

        return Common::apiResponse(1, '', $arr);
    }

    public function delete(Request $request)
    {
        $user = $request->user();

        if (UserHandling::checkIfUserOwnerOfAgency($user)) {
            return Common::apiResponse(0, 'This User is the host Of agency can\'t delete it');
        }
        $user->tokens()->delete();
        $user->delete();
        return Common::apiResponse(1, 'account deleted successfully');
    }

    public function zegoCredential()
    {
        $ZegoEncreyptkey = config('app.zego_credential');
        $keys = Common::getConfFromKey(['app_sign', 'zego_app_id']);
        $data = $keys->mapWithKeys(function ($item){
            return [$item['name'] => $item['name'] == 'zego_app_id' ? (integer)$item['value'] :$item['value']];
        });

        $encryptedData = openssl_encrypt($data, 'AES-256-CBC', $ZegoEncreyptkey, 0, substr($ZegoEncreyptkey, 0, 16));

        return Common::apiResponse(1, '',$encryptedData );
    }
}
