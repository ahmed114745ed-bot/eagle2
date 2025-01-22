<?php

namespace App\Http\Controllers\Api\V1;

use DB;
use Auth;
use Exception;
use App\Models\User;
use App\Models\Agency;
use App\Models\Config;
use App\Models\Target;
use App\Enums\UserType;
use App\Helpers\Common;
use App\Helpers\UserCommon;
use App\Models\UserSallary;
use Illuminate\Http\Request;
use App\Facades\UserHandling;
use App\Services\UserService;
use Illuminate\Validation\Rule;
use App\Http\Services\WhatsappOtp;
use App\Models\UserCodeInvitation;
use App\Models\UserEarnInvitation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Resources\Api\V1\MyDataResource;
use App\Http\Resources\Api\V1\MyStoreResource;
use App\Http\Services\ProfileRelationsService;
use App\Http\Resources\Api\V1\AllUsersResource;
use App\Http\Resources\Api\V1\ShowUserResource;
use App\Http\Resources\Api\V1\UserTypeResource;
use App\Http\Resources\Api\V1\LevelUserResource;
use App\Http\Resources\Api\V1\UserTargetResource;
use App\Http\Resources\Api\V1\DeviceTokenResource;
use Modules\WhatsappAuth\Services\WhatsappWebhook;
use Modules\FixedTarget\Services\FixedTargetService;
use Modules\SalaryTransaction\Entities\SalaryRequest;
use App\Http\Resources\Api\V1\ShowUserSettingResource;
use App\Http\Resources\Api\V1\ZegoCreditionalResource;
use Modules\Achievement\Http\Services\UserAchievementService;
use Modules\Achievement\Transformers\UserAchievementLevelsResource;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function chargerAgency(Request $request, ProfileRelationsService $profileRelationsService)
    {

        $users = $this->userService->userCharge();
        $usersType = UserTypeResource::collection($users);
        [$senderLevels, $receivedImage] = $profileRelationsService->getLevelsSenderAndReceiver($usersType);
        UserTypeResource::initializeData($senderLevels, $receivedImage, null);
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
        $data = $this->userService->userStatic($user,  $month, $year);
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


        $users = $users->through(function ($user) {
            $user->level = Common::level_centerSerch($user->id);

            return $user;
        });
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

    public function userFamily(Request $request)
    {
        $key = $request->q;
        $page = $request->get('page', 1);
        $users = $this->userService->searchUsersInFamily($key, $page);

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
        try {


            // \Log::info('This is the device token '. json_encode(getallheaders()));

            $userWithMedals = $this->userService->processUserData($user, $request->header('X-Device-Token'), $request->header('lat'), $request->header('long'));
        } catch (\Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
        $this->userService->unlockDressHand($user->id);
        request()->default_background = \DB::table('backgrounds')->where('enable', 1)->orderBy('id', 'asc')->first()?->img;

        $data = new MyDataResource($userWithMedals);

        return Common::apiResponse(true, '', $data, 200);
    }

    public function userFriend(Request $request)
    {
        $user = $request->user();
        $keyword = $request->keywords ?? '';
        return $this->userService->handleUserRelations($user, $request->type, $keyword);
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

        $agency = Agency::query()->with('owner')->where('app_owner_id', $user->id)->withCount('joinRequests')->first();
        if (!$agency) return Common::apiResponse(0, __("api_responses.u_not_owner_agncy"), []);
        $total_host_target = UserSallary::where('user_agency_id', $agency->id);

        if ($month != null && $year != null) {
            $total_host_target = $total_host_target->where('month', $month)
                ->where('year', $year);
        }
        $total_host_target = $total_host_target->sum('sallary');
        $owner =       $agency->owner;
        $owner->avatar = $agency->owner->avatar;
        $data = [
            'id'                => $agency->id,
            'name'              => $agency->name,
            'image'             => $agency->img,
            'pio'               => $agency->contents,
            'num_of_hosts'      => $agency->mempers->count(),
            'total_salary'      => $total_host_target,
            'agency_target'     => $agency->getSalary($month, $year),
            'number_request'              => $agency->joinRequests->count(),
            'owner' => $owner
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
                Rule::unique('users', 'phone')->ignore($user->id),
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
                'country'      => [
                    'id' => @$result->sender?->country?->id ?? 0,
                    'name' => @$result->sender?->country?->name ?? '',
                    'flag' => @$result->sender?->country?->flag ?? '',
                ],
                'achievements'  => UserAchievementLevelsResource::collection($achievement->getUserAchievement($result->sender)),
                // 'sender_level' => $result->sender->total_sender_level ?? 0,
                // 'receiver_level' => $result->receiver->total_received_level ?? 0,
                'total'        => numToString($currentTotal),
                'total_diff'   => $totalDiff,
                'frame'        => $frame,
                'frame_id'     => $frame != '' ? @$result->sender->dress_1 : 0,
                //'vip'     => $result->sender?->userVip?->level,
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
        $keys = Common::getConfFromKey(['app_sign', 'zego_app_id', 'youtube_key']);
        $data = $keys->mapWithKeys(function ($item) {
            return [$item['name'] => $item['name'] == 'zego_app_id' ? (int)$item['value'] : $item['value']];
        });

        $encryptedData = openssl_encrypt($data, 'AES-256-CBC', $ZegoEncreyptkey, 0, substr($ZegoEncreyptkey, 0, 16));

        return Common::apiResponse(1, '', $encryptedData);
    }

    public function switchAccountAnonymous(Request $request)
    {
        $user = $request->user();
        try {
            [$user, $token] = $this->userService->anonymous($user, $request);
        } catch (\Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
        if (!$this->canLogin($user)) {
            return Common::apiResponse(false, 'you are blocked', [], 408);
        }
        $user->auth_token = $token;
        return Common::apiResponse(
            true,
            __('api_responses.logged'),
            [
                'id'            => $user->id,
                'is_first'      => @(bool)$user->is_points_first,
                'auth_token'    => $user->auth_token
            ]
        );
        return Common::apiResponse(true, 'logged in successfully', new MyDataResource($user), 200);
    }

    public function canLogin($user)
    {
        $status = $user instanceof User ? $user->status : ($user['status'] ?? null);

        return $status == 1;
    }


    public function explain_invitation()
    {
        $lang = app()->getLocale();
        if ($lang == "en") {
            $data = Config::where("name", "explain_invitation_english")->first();
        } else {
            $data = Config::where("name", "explain_invitation_arabic")->first();
        }
        return Common::apiResponse(true, '', $data?->desc, 200);
    }

    public function UserEarnFromInvitationStatistics()
    {
        $userId             = Auth::id();
        $parentInvitations  = UserEarnInvitation::where("parent_id", $userId);
        $UserCodeInvitation = UserCodeInvitation::where("user_id", $userId);
        if ($parentInvitations != null) {
            $data = [
                "totalEarned"  => $parentInvitations->sum("parent_percentage"),
                "earnedDay"    => $parentInvitations->whereDate("created_at", date("Y-m-d"))->sum("parent_percentage"),
                "TotalInvited" => $UserCodeInvitation->count(),
                "invitedDay"   => $UserCodeInvitation->whereDate("created_at", date("Y-m-d"))->count(),
            ];
            return Common::apiResponse(true, '', $data, 200);
        }
        return Common::apiResponse(true, '', $data = [], 200);
    }

    public function parentUser()
    {
        $userId = Auth::id();
        $data   = UserCodeInvitation::with("user")->where("invited_id", $userId)->first();
        $lang   = app()->getLocale();
        if ($lang == 'ar') {
            $mes_user_not_found = 'لم يتم العثور علي المستخدم';
            $success_mes        = 'لا يوجد بيانات';
        } else {
            $mes_user_not_found = 'It was not found on the user';
            $success_mes        = 'not found data';
        }

        if ($data) {
            if ($data->user) {
                return Common::apiResponse(true, '', new MyDataResource($data->user), 200);
            }
            return Common::apiResponse(false, $mes_user_not_found, $data = [], 200);
        }
        return Common::apiResponse(false, $success_mes, $data = [], 200);
    }

    public function UserEarnFromInvitation()
    {
        $userId = Auth::id();
        $data   = UserEarnInvitation::with("user:id,name,uuid")->select("id", "user_id", "parent_id", "updated_at", "user_charge", "parent_percentage")->where("parent_id", $userId)->orderByDesc('created_at')->get();
        return Common::apiResponse(true, '', $data, 200);
    }

    public function AddCodeInvitation(Request $request)
    {
        $user_id      = Auth::id();
        $user_parent  = User::where("uuid", $request->code)->first();
        $existingUser = UserCommon::CheckUserParent($user_id);
        $CheckUserNew = UserCommon::CheckUserNew($user_id);
        $lang         = app()->getLocale();
        if ($lang == 'ar') {
            $mes_user_not_found = 'لم يتم العثور علي المستخدم';
            $mes_validation     = "لقد مر علي المستخدم 48 ساعه من تاريخ انشائه او المستخدم مسجل من قبل لدي شخص اخر";
            $success_mes        = 'تم الاضافه بنجاح';
        } else {
            $mes_user_not_found = 'It was not found on the user';
            $mes_validation     = "48 hours have passed since the user was created, or the user has already been registered with someone else";
            $success_mes        = 'Added successfully';
        }
        if (!$user_parent) {
            return Common::apiResponse(false, $mes_user_not_found, $existingUser, 404);
        }
        if ($CheckUserNew == false) {
            return Common::apiResponse(false, $mes_validation, $existingUser, 404);
        }

        if ($existingUser != null) {
            return Common::apiResponse(false, 'المستخدم مسجل من قبل', $existingUser, 404);
        }
        $data = UserCodeInvitation::create([
            "user_id"    => $user_parent->id,
            "invited_id" => $user_id,
        ]);

        return Common::apiResponse(true, $success_mes, $request->code, 200);
    }

    public function CreateCodeInvitation()
    {
        $user_id       = Auth::id();
        $generatedCode = random_int(1, 100000);
        $existingCode  = UserCodeInvitation::where('code', $user_id . $generatedCode)->exists();
        if ($existingCode) {
            $generatedCode = random_int(1, 100000);
        }
        $data = UserCodeInvitation::create([
            "user_id" => $user_id,
            "code"    => $user_id . $generatedCode,
        ]);
        return Common::apiResponse(true, 'تم انشاء الكود', $data->code, 200);
    }

    public function userLevel(Request $request)
    {
        $trashed = $this->userService->userLevel($request->per_page, $request->Page, $request->uuid);
        return Common::apiResponse(true, 'success', LevelUserResource::collection($trashed));
    }

    public function updateUserLevel($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'total_sender_level'         => 'required|numeric',
            'total_received_level'         => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }
        $this->userService->updateUserLevel($id, $request);
        return Common::apiResponse(true, ' updated successfully');
    }

    public function usersDeviceToken(Request $request)
    {
        $data = $this->userService->userDeviceToken($request->per_page, $request->Page, $request->device_token);
        return Common::apiResponse(true, 'success', DeviceTokenResource::collection($data));
    }


    public function deleteDeviceToken($id)
    {
        try {
            $this->userService->deleteDeviceToken($id);
            return Common::apiResponse(true, 'delete successfully');
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function usersTarget(Request $request)
    {
        $data = $this->userService->usersTargets($request->per_page, $request->Page);
        return Common::apiResponse(true, 'success', UserTargetResource::collection($data));
    }

    public function allUsers(Request $request)
    {
        $users = $this->userService->allUser($request->per_page, $request->Page, $request->family_id, $request->agency_id, $request->search, $request->host);
        return Common::apiResponse(true, 'done', AllUsersResource::collection($users));
    }

    public function kickAgency($id)
    {
        try {
            $this->userService->kickAgency($id);
            return Common::apiResponse(true, 'removed');
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function kickFamily($id)
    {
        try {
            $this->userService->kickFamily($id);
            return Common::apiResponse(true, 'removed');
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function changeAgency(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'         => 'required|integer|exists:users,id',
            'agency_id'         => 'required|integer|exists:agencies,id',
        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }
        try {
            $this->userService->changeAgency($request);
            return Common::apiResponse(true, 'changed');
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function updateSwitch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'key' => 'required|string|in:charge_status,transfer_salary,can_play',
            'value'   => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    if (in_array($request->key, ['charge_status', 'transfer_salary']) && !in_array($value, [0, 1])) {
                        $fail(__('The :attribute must be a boolean value for charge_status or transfer_salary.'));
                    }

                    if ($request->key === 'can_play' && !in_array($value, [2, 3])) {
                        $fail(__('The :attribute must be either 2 or 3 when the setting is can_play.'));
                    }
                },
            ],
        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }
        try {
            $this->userService->updateSwitch($request);
            return Common::apiResponse(true, 'changed');
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }



    public function updateUserSetting(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'         => 'required|integer|exists:users,id',
            'key' => 'required|string|in:hide_chat,show_invite_code',
            'value' => 'required|boolean',
        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }
        try {
            $this->userService->updateUserSetting($request);
            return Common::apiResponse(true, 'changed');
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }


    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uuid'         => 'required',
            'name'         => 'required|string',
            'charge_status'         => 'required|boolean',
            'transfer_salary'         => 'required|boolean',
            'can_play'         => 'required|integer|in:0,1',
            'country_id'         => 'nullable|integer|exists:countries,id',
            'di'    => 'nullable|integer',
            'user_diamond' => 'nullable|integer',
            'total_sender_level' => 'nullable|integer',
            'total_received_level' => 'nullable|integer',
            'salary' => 'nullable|integer',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'facebook_id' => 'nullable',
            'google_id' => 'nullable',
            'huawei_id' => 'nullable',
            'status' => 'required|boolean',
            'type_user' => 'required|integer',
            'manger_type_id' => 'nullable',
            'avatar' => 'nullable',
            'image_id' => 'nullable',
            'gender' => 'nullable',
            'show_invite_code'         => 'required|boolean',
            'hide_chat'         => 'required|boolean',

        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }

        try {
            $this->userService->create($request);
            return Common::apiResponse(true, 'created successfully');
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function showDataUser($id)
    {
        try {
            $user  = $this->userService->showDataUser($id);

            return Common::apiResponse(true, 'done', new ShowUserResource($user));
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function updateDataUser($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uuid'         => [
                'required',
                'exists:users,id',
                Rule::unique('users', 'uuid')->ignore($id),
            ],
            'name'         => 'required|string',
            'charge_status'         => 'required|boolean',
            'transfer_salary'         => 'required|boolean',
            'can_play'         => 'required|integer|in:2,3',
            'country_id'         => 'nullable|integer|exists:countries,id',
            'user_diamond' => 'nullable|integer',
            'total_sender_level' => 'nullable|integer',
            'total_received_level' => 'nullable|integer',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'status' => 'required|boolean',
            'type_user' => 'required|integer',
            'manger_type_id' => 'nullable',
            'avatar' => 'nullable',
            'image_id' => 'nullable',
            'gender' => 'nullable',
            'show_invite_code'         => 'required|boolean',
            'hide_chat'         => 'required|boolean',

        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }

        try {
            $this->userService->update($id, $request);
            return Common::apiResponse(true, 'updated successfully');
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function allCodes(Request $request)
    {
        $data = $this->userService->allCods($request->id, $request->per_page, $request->page);
        return Common::apiResponse(true, 'done', $data);
    }

    public function userType()
    {
        return response()->json([
            'success' => true,
            'message' => 'Success',
            'data' => (object) UserType::list(),
        ]);
    }
}
