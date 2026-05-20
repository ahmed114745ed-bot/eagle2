<?php

namespace App\Http\Controllers\Api\V1;

use App\Classes\Gifts\UpdateUserWhenSendGift;
use App\Facades\CustomNotification;
use App\Facades\UserHandling;
use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\GiftLogResource;
use App\Http\Resources\GiftLogUtdResource;
use App\Jobs\AllOpeningRoomsZegoRequest;
use App\Jobs\CleanGiftLogsJob;
use App\Models\GiftLog;
use App\Models\MonthlyDiamondReceive;
use App\Models\RemainingDiamond;
use App\Models\User;
use App\Models\UserSallary;
use App\Repositories\Room\RoomTopUsersRepository;
use App\Tik\Services\GiftLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Public\Http\Services\UpgradeRoomLevelServices;
use Modules\RoomBoom\Entities\TotalRoomGift;
use App\Services\Gifts\LuckyGiftService;
use App\Services\FairLuck\V5\GlobalStabilityManager;
use App\Models\Gift;


class GiftLogController extends Controller
{

    private $roomTopUsersRepository;
    private $luckyGiftService;
    public function __construct(
        RoomTopUsersRepository $roomTopUsersRepository,
        private GiftLogService $giftLogService,
        LuckyGiftService $luckyGiftService,
  
    ) {

        $this->roomTopUsersRepository = $roomTopUsersRepository;
        $this->luckyGiftService = $luckyGiftService;
    }

    public function updateRoomPercentageAndHost($ownerId, array $receiverIds, $totalCoins, $coinsPerUser)
    {
        $this->addRoomCoins($ownerId, $totalCoins);
        $this->addHostCoins($receiverIds, $coinsPerUser);
    }
    public function addHostCoins(array $receiverIds, int $totalCoins)
    {
        $receiverIds = UserHandling::checkIfUserHostByIds($receiverIds);

        if (count($receiverIds) == 0)
            return;

        $coins = floor($totalCoins * 0.03);
        DB::table('users')->whereIn('id', $receiverIds)->update(values: ['di' => DB::raw(sprintf("di + %s", $coins))]);
        $data = [];
        foreach ($receiverIds as $receiverId) {
            $data[] = [
                'user_id' => $receiverId,
                'coins' => $coins,
                'from_coins' => $totalCoins,
                'type' => 'host_coins'
            ];
        }
        $this->insertGiftPercentage($data);
    }
    public function addRoomCoins(int $ownerId, int $totalCoins)
    {
        $coins = floor($totalCoins * 0.03);

        DB::table('users')->where('id', $ownerId)->update(values: ['di' => DB::raw(sprintf("di + %s", $coins))]);
        $data = [
            'user_id' => $ownerId,
            'coins' => $coins,
            'from_coins' => $totalCoins,
            'type' => 'room_coins'
        ];
        $this->insertGiftPercentage($data);
    }
    public function updateFamilyLevelForSender(\Illuminate\Database\Eloquent\Collection $users, $totalCoinsPerUser): bool
    {
        $families = $users->pluck('family')->where('id', '!=', null);
        $familiesIds = $families->pluck('id')->toArray();
        if (count($familiesIds) == 0)
            return false;
        $repeatedData = $this->getDuplication($familiesIds);

        foreach ($repeatedData as $data) {
            $family = $families->where('id', $data['id'])->first();
            $this->updateFamilyModel($totalCoinsPerUser * $data['count'], $family);
        }
        $this->addFamilyCoins($families->pluck('user_id')->toArray(), $totalCoinsPerUser);

        return true;
    }

    public function addFamilyCoins(array $ownerIda, int $totalCoins)
    {
        $coins = floor($totalCoins * 0.01);


        DB::table('users')->whereIn('id', $ownerIda)->update(values: ['di' => DB::raw(sprintf("di + %s", $coins))]);

        $data = [];
        foreach ($ownerIda as $ownerId) {
            $data[] = [
                'user_id' => $ownerId,
                'coins' => $coins,
                'from_coins' => $totalCoins,
                'type' => 'family_coins'
            ];
        }
        $this->insertGiftPercentage($data);
    }
    public function insertGiftPercentage(array $data): void
    {

        $isTwoDiminutionsArray = false;
        foreach ($data as &$value) {
            if (is_array($value)) {
                $isTwoDiminutionsArray = true;
                $value['created_at'] = now();
                $value['updated_at'] = now();
            } else
                break;
        }
        if (!$isTwoDiminutionsArray) {
            $data['created_at'] = now();
            $data['updated_at'] = now();
        }

        DB::table('gift_percentage_logs')->insert($data);
    }
    public function sendToZego($gift, $to_id, $totalPrice, $receiversIds, $room, ?string $toName, $ownerId, $number, $user, $firstReceiver, ?bool $isToZigo = false): array
    {


        $userCoins = User::where('id', $user->id)->value('di') ?? 0;

        $zigoData = collect(
            [
                'show_gift' => $gift->show_img ?: $gift->show_img2,
                'gift_img' => $gift->img,
                'gift_id' => $gift->id,
                'sender_id' => (int) $user->id,
                'receiver_id' => (int) $to_id,
                'num_gift' => $totalPrice,
                "plural" => is_array($receiversIds) && count($receiversIds) > 1,
                'room_session' => $room->session_string,
                'is_password' => (bool) (@$room->room_pass),
                'room_id' => $room->id,
                'from_name' => $user->name,
                'to_name' => $toName,
                'gift_price' => $gift->price,
                'owner_id' => $ownerId,
                'number' => $number,
                'coins' => numToString($userCoins)/*$user->coins_string*/ ,
                'is_lucky_gift' => ($gift->type == 6),
                'gift_image_type' => $gift->image_type,

            ]
        );

        if ($totalPrice >= 2000) {
            $levels = [
                $user->total_sender_level,
                $user->total_received_level,
                $firstReceiver->total_received_level,
                $firstReceiver->total_sender_level,
            ];
            $levels = Common::getLevels($levels);
            $senderLevels = $levels->where('type', '=', 2);
            $receiverLevels = $levels->where('type', '=', 1);
            $values = [
                's_vip_level' => @$user->userVip->level ?? 0,
                's_image' => @$user->profile->avatar ?? '',
                's_name' => @$user->name ?? '',
                's_sender_level' => @$senderLevels->where('level', '=', $user->total_sender_level)->first()->img ?? '',
                's_receiver_level' => @$receiverLevels->where('level', '=', $user->total_received_level)->first()->img ?? '',
                'r_vip_level' => @$firstReceiver->userVip->level ?? 0,
                'r_name' => @$firstReceiver->name ?? '',
                'r_image' => @$firstReceiver->profile->avatar ?? '',
                'r_sender_level' => @$senderLevels->where('level', '=', $firstReceiver->total_sender_level)->first()->img ?? '',
                'r_receiver_level' => @$receiverLevels->where('level', '=', $firstReceiver->total_received_level)->first()->img ?? '',
            ];
            $zigoData = $zigoData->merge($values);
        }

        //        dispatch(new SendGiftToZegoJob($zigoData, $totalPrice, ($request->to_zego == 1)))->onQueue('sendGiftToZigo');
        /* $startTime = microtime(true);*/
        return $this->sendZigoGifts($zigoData, $totalPrice, ($isToZigo));
    }

    public function sendZigoGifts($zigoData, $totalPrice, $isToZego): array
    {
        //        Common::sendToZego_2('SendBroadcastMessage', $zigoData['room_id'], $zigoData['sender_id'], $zigoData['from_name'], "  {$zigoData['number']} x ارسل هدية  " . " قيمتها {$zigoData['gift_price']} " . " الى {$zigoData['to_name']}");
        if ($isToZego) {
            $d = [
                "messageContent" => [
                    "message" => "showGifts",
                    "showGift" => $zigoData['show_gift'],
                    'giftImg' => $zigoData['gift_img'],
                    'gift_id' => $zigoData['gift_id'],
                    'send_id' => $zigoData['sender_id'],
                    'receiver_id' => $zigoData['receiver_id'],
                    'isExpensive' => $totalPrice >= 2000,
                    'num_gift' => $zigoData['number'],
                    "plural" => $zigoData['plural'],
                    'gift_price' => $totalPrice, // $zigoData['room_session'],
                    'coins' => @$zigoData['coins'] ?? '0',
                    'is_lucky_gift' => (bool) $zigoData['is_lucky_gift'],
                    'type' => @$zigoData['gift_image_type'] ?? 'mp4'

                ]
            ];
            $json = json_encode($d);
            $jsons[] = $json;
            //            Common::sendToZego('SendCustomCommand', $zigoData['room_id'], $zigoData['sender_id'], $json);
            if ($totalPrice >= 2000) {
                $d = [
                    "messageContent" => [
                        "msg" => "SHB",
                        'sv' => $zigoData['s_vip_level'],
                        'si' => $zigoData['s_image'],
                        'sn' => $zigoData['s_name'],
                        'ssl' => $zigoData['s_sender_level'],
                        'srl' => $zigoData['s_receiver_level'],
                        'rv' => $zigoData['r_vip_level'],
                        'rn' => $zigoData['r_name'],
                        'ri' => $zigoData['r_image'],
                        'rsl' => $zigoData['r_sender_level'],
                        'rrl' => $zigoData['r_receiver_level'],
                        'oId' => (int) $zigoData['owner_id'],
                        'isPass' => $zigoData['is_password'],
                    ]
                ];
                $json = json_encode($d);

                //                Common::sendToZego('SendCustomCommand', $zigoData['room_id'], $zigoData['sender_id'], $json);

                dispatchJobToQueue(new AllOpeningRoomsZegoRequest($json, $zigoData['sender_id'], $zigoData['room_id']), 'heavyProcessing');
            }
        }
        return @$jsons ?? [];
    }
    public function gift_queue_cp(Request $request, UpdateUserWhenSendGift $updateUserWhenSendGift)
    {
        $close_open_gifts = settings()->get('close_open_gifts');

        if ($close_open_gifts == 1) {
            return Common::apiResponse(0, __('Send gift stopped by admin'));
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'owner_id' => 'nullable',
            'toUid' => 'required',
            'num' => 'required|integer|min:1',
            'type' => 'nullable',
        ]);

        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }

        try {
            $message = $this->giftLogService->sendGift($request, $updateUserWhenSendGift);
        } catch (\Exception $e) {
            return Common::apiResponse(false, $e->getMessage());
        }

        if (!settings()->get('gift_send')) {
            settings()->set('gift_send', true);
        }
        $tpUsers = $request->toUid;
        $idsArray = explode(',', $tpUsers);

        $idsArray = array_map('intval', $idsArray);

        $data = [
            'ids' => $idsArray
        ];
        return Common::apiResponse(true, $message, $data);
    }




    public function giftLogsList(Request $request)
    {
        $userId = $request->user()->id;
        if ($request->user_id) {
            $user = User::where('id', $request->user_id)->exists();
            if (!$user)
                return Common::apiResponse(0, 'not found', null, 404);
            $userId = $request->user_id;
        }
        $giftTotal = GiftLog::where(function ($q) use ($userId) {
            $q->where('receiver_id', $userId)
                ->orWhere('sender_id', $userId);
        })
            ->selectRaw('receiver_id, sender_id, SUM(giftPrice) as totalPrice')
            ->groupBy('receiver_id', 'sender_id')
            ->get();

        $gl = GiftLog::select('giftId', DB::raw('SUM(giftNum) as t'))
            ->where('receiver_id', $userId)
            ->with('receiver:id,sub_receiver_level,sub_sender_level')
            ->whereHas('gift')
            ->where('giftId', '!=', 0)
            ->groupBy('giftId')
            ->orderByDesc('t')
            ->with('gift.category')
            ->get();

        GiftLogResource::setGiftTotal($giftTotal);

        return Common::apiResponse(1, 'ok', GiftLogResource::collection($gl));
    }

    /**
     * @param $userId
     * @param $room
     * @param $totalPrice
     * @return void
     */
    public function updateRoomCoinsToUser($userId, $room, $totalPrice): void
    {
        $topUser = $this->roomTopUsersRepository->findOrCreate($room->id, $userId);
        $topUser->coins += $totalPrice;
        $topUser->save();
    }




    public function is_winner($gift): bool
    {
        if (!$gift)
            abort(404);
        $win_probability = ((int) $gift->luckyGift?->win_probability ?? 70) / 100;
        $randomValue = mt_rand(0, 100) / 100;
        return $randomValue <= $win_probability;
    }




    public function sendLuckyGift(Request $request, UpdateUserWhenSendGift $updateUserWhenSendGift)
    {
       // $luckyStatus = Common::getSettingValue('lucky_gifts_action');
        $luckyStatus = Common::getSettingValue('lucky_gift_version');
        if ($luckyStatus == 4) {
            Log::channel('lucky_gift')->info('Lucky gift send attempt while lucky gifts are disabled', [
                'user_id' => $request->user()->id,
                'request_data' => $request->all()
            ]);
            return $this->sendLuckyGiftV2($request, $updateUserWhenSendGift);
        }
         return $this->sendLuckyGiftV1($request, $updateUserWhenSendGift);
    }


    public function sendLuckyGiftV1(Request $request, UpdateUserWhenSendGift $updateUserWhenSendGift)
    {

        $stopLucky = settings()->get('stop_luckyGift');
        if ($stopLucky == 1) {
            return Common::apiResponse(0, __('api_responses.try_again'));
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'owner_id' => 'nullable',
            'toUid' => 'required',
            'num' => 'required|integer|min:1',
            'count' => 'sometimes|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }

        $data = $request->all();
        $user = $request->user();

        try {
            $data = (new \App\Services\Gifts\LuckyGiftService())->sendLuckyGiftV1($data, $user, $updateUserWhenSendGift);
        } catch (\Exception $e) {
            return Common::apiResponse(0, $e->getMessage());
        }
        return Common::apiResponse(1, __('api_responses.success'), $data);
    }






    public function sendLuckyGiftV2(Request $request, UpdateUserWhenSendGift $updateUserWhenSendGift)
    {
        $stopLucky = settings()->get('stop_luckyGift');
        if ($stopLucky == 1) {
            return Common::apiResponse(0, __('api_responses.try_again'));
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'owner_id' => 'nullable',
            'toUid' => 'required',
            'num' => 'required|integer|min:1',
            'count' => 'sometimes|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }

        $data = $request->all();
        $user = $request->user();

        try {
            $data = $this->luckyGiftService->sendLuckyGiftV2($data, $user, $updateUserWhenSendGift);
        } catch (\Exception $e) {
            return Common::apiResponse(0, $e->getMessage());
        }
        return Common::apiResponse(1, __('api_responses.success'), $data);
    }


    public function getReceivedAndSanderPercentage(): array
    {
        $keys = ['sender_percentage', 'received_percentage'];
        $collection = Common::getConfFromKey($keys);
        $values = [];
        foreach ($keys as $key) {
            $config = $collection->where('name', $key)->first();
            $values[] = $config ? $config->value : 0;
        }
        unset($collection);
        return $values;
    }

    /**
     * @param array $probability
     * @return mixed
     */
    public function getCashbackPercentage(array $probability): mixed
    {
        $luckyRandom = rand(1, 10);
        $index = $luckyRandom <= 5 ? 0 : (($luckyRandom <= 8) ? 1 : 2);

        $arr = $probability[$index];
        $randomIndex = rand(0, (count($arr) - 1));
        return $arr[$randomIndex];
    }

    public function ofLucky()
    {
        return Common::apiResponse(0, __('api_responses.update_your_version'));
    }

    public function myGiftInfo($id, Request $request)
    {

        $validator = Validator::make($request->all(), [
            'end_date' => 'nullable|date_format:Y-m-d',
            'start_date' => 'nullable|date_format:Y-m-d',
        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }

        try {
            $data = $this->giftLogService->userGiftIfo($id, $request->type, $request->start_date, $request->end_date, $request->per_page, $request->page);
            return Common::apiResponse(1, __('api_responses.success'), GiftLogUtdResource::collection($data));
        } catch (\Exception $e) {
            return Common::apiResponse(0, $e->getMessage());
        }
    }


    public function cleanGiftLogsForAllUsers()
    {
        CleanGiftLogsJob::dispatch()->onQueue('clean_gift_logs');


        return response()->json([
            'status' => 'success',
            'message' => 'Gift logs cleanup job has been dispatched for all users.'
        ]);
    }


    public function increaseMonthlyDiamond()
    {
        $remainingDiamonds = RemainingDiamond::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->pluck('user_id')->toArray();

        UserSallary::where([
            'month' => 11,
            'year' => 2025,
            'is_finished' => 0
        ])->whereNotIn('user_id', $remainingDiamonds)
            ->with('user')
            ->chunk(100, function ($userSalaries) {

                $userIds = $userSalaries->pluck('user_id')->toArray();
                $existingDiamondUserIds = RemainingDiamond::whereIn('user_id', $userIds)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->pluck('user_id')
                    ->toArray();

                foreach ($userSalaries as $userSalary) {

                    try {
                        $user = $userSalary->user;
                        $diamonds = $userSalary->remaining_diamond ?? 0;

                        if (!$user || $diamonds <= 0) {
                            continue;
                        }
                        if (in_array($user->id, $existingDiamondUserIds)) {
                            continue;
                        }

                        // Wrap in DB::transaction to ensure data consistency
                        DB::transaction(function () use ($user, $diamonds) {
                            $this->processDiamonds($user, $diamonds, Carbon::now(), 11, 2025);
                        });
                    } catch (\Throwable $e) {

                        \Log::error("Monthly diamond add ERROR for user_id = {$userSalary->user_id}", [
                            'error' => $e->getMessage()
                        ]);

                        if ($userSalary->user_id == 580) {
                            \Log::error("User 580 ERROR DETAILS", [
                                'diamonds' => $userSalary->remaining_diamond,
                                'exception' => $e->getMessage()
                            ]);
                        }
                    }
                }
            });

        return response()->json([
            'status' => 'success',
            'message' => 'All diamonds processed.'
        ]);
    }



    private function processDiamonds($user, int $diamonds, Carbon $dt, $month, $year)
    {
        $monthDiamondReceive = MonthlyDiamondReceive::firstOrNew(
            [
                'user_id' => $user->id,
                'month' => $dt->month,
                'year' => $dt->year,
            ]
        );

        $monthDiamondReceive->monthly_diamond_received += $diamonds;
        $monthDiamondReceive->save();

        GiftLog::create([
            'giftId' => 0,
            'roomowner_id' => 0,
            'giftPrice' => $diamonds,
            'giftNum' => 1,
            'sender_id' => 0,
            'receiver_id' => $user->id,
        ]);

        RemainingDiamond::create([
            'user_id' => $user->id,
            'amount' => $diamonds,
            'type' => 'diamonds',
            'remaining' => $diamonds,
            'month' => $month,
            'year' => $year,
        ]);
        CustomNotification::remainingDiamonds($user, 'diamonds', $month, $diamonds);
    }



    public function totalRoomGift()
    {
        $start = Carbon::createFromFormat('d/m/Y', '09/02/2026')->startOfDay();
        $end = Carbon::now()->endOfDay();

        GiftLog::query()
            ->selectRaw('room_id, SUM(giftPrice) AS total')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('room_id')
            ->orderBy('room_id') // required for chunk
            ->chunk(50, function ($giftLogs) {

                $roomIds = $giftLogs->pluck('room_id')->toArray();
                // Batch fetch for existence check (optimized)
                $existingTotals = TotalRoomGift::whereIn('room_id', $roomIds)
                    ->whereDate('created_at', now())
                    ->get()
                    ->keyBy('room_id');

                foreach ($giftLogs as $log) {
                    // Use lockForUpdate() to prevent race condition on concurrent requests
                    $totalRoomGift = TotalRoomGift::where('room_id', $log->room_id)
                        ->whereDate('created_at', now())
                        ->lockForUpdate()
                        ->first();

                    if (!$totalRoomGift) {
                        $totalRoomGift = TotalRoomGift::create([
                            'room_id' => $log->room_id,
                            'current_total' => 0,
                        ]);
                    }
                    $totalRoomGift->current_total += $log->total;
                    $totalRoomGift->save();
                }
            });


        return response()->json([
            'status' => 'success',
            'message' => 'done total gift from ' . $start->format('d/m/Y') . ' to ' . $end->format('d/m/Y') . '.'
        ]);
    }
}
