<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Pk;
use Carbon\Carbon;
use App\Models\Gift;
use App\Models\Room;
use App\Models\User;
use App\Models\Agency;
use App\Helpers\Common;
use App\Models\GiftLog;
use App\Models\AppFeature;
use App\Models\CoreWallet;
use App\Helpers\UserCommon;
use Illuminate\Http\Request;
use GuzzleHttp\Promise\Utils;
use App\Services\LuckyGiftService;
use Illuminate\Support\Facades\DB;
use App\Jobs\UpdatePkAndSendToZigo;
use App\Services\RoomLevelServices;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Tik\Services\GiftLogService;
use App\Classes\Gifts\SendGiftService;
use App\Exceptions\NotInfMoneyException;
use App\Jobs\AllOpeningRoomsZegoRequest;
use App\Jobs\UpdateUserDataWhenSendGift;
use Modules\CP\Http\Services\CpServices;
use Illuminate\Support\Facades\Validator;
use App\Classes\Gifts\UpdateUserWhenSendGift;
use App\Http\Resources\Api\V1\GiftLogResource;
use App\Repositories\Room\RoomTopUsersRepository;
use Modules\Achievement\Jobs\CalculateAchievement;
use App\Http\Services\RoomAchievementTargetService;
use Modules\Public\Http\Services\UpgradeRoomLevelServices;
use Modules\Charizma\Jobs\UpdateUsersAndSendCharismaToZigo;

class GiftLogController extends Controller
{

    private $roomTopUsersRepository;
    public function __construct(RoomTopUsersRepository $roomTopUsersRepository,
    private GiftLogService $giftLogService,
    )
    {

        $this->roomTopUsersRepository = $roomTopUsersRepository;
    }



    public function gift_queue_cp(Request $request, UpdateUserWhenSendGift $updateUserWhenSendGift)
    {
        //update when send the gift
        $validator = Validator::make($request->all(), [
            'id'       => 'required',
            'owner_id' => 'required',
            'toUid'    => 'required',
            'num'      => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }

       
      return  $this->giftLogService->sendGift($request , $updateUserWhenSendGift);
    }



    // public function giftLogsList(Request $request)
    // {
    //     $user = $request->user();
    //     if ($request->user_id) {
    //         $user = User::query()->find($request->user_id);
    //         if (!$user) return Common::apiResponse(0, 'not found', null, 404);
    //     }
    //     $gl = GiftLog::select('giftId', DB::raw('SUM(giftNum) as t'))
    //         ->where('receiver_id', $user->id)
    //         ->whereHas('gift')
    //         ->where('giftId', '!=', 0)
    //         ->groupBy('giftId')
    //         ->orderByDesc('t')
    //         ->with('gift')
    //         ->get();
    //     return Common::apiResponse(1, 'ok', GiftLogResource::collection($gl));
    // }

    /**
     * @param $userId
     * @param $room
     * @param $totalPrice
     * @return void
     */
    public function updateRoomCoinsToUser($userId, $room, $totalPrice): void
    {
        $topUser         = $this->roomTopUsersRepository->findOrCreate($room->id, $userId);
        $topUser->coins  += $totalPrice;
        $topUser->save();
    }


    

    public function is_winner($gift): bool
    {
        if (!$gift) abort(404);
        $win_probability = ((int)$gift->luckyGift?->win_probability ?? 70) / 100;
        $randomValue = mt_rand(0, 100) / 100;
        return $randomValue <= $win_probability;
    }

    public function sendLuckyGift2(Request $request, UpdateUserWhenSendGift $updateUserWhenSendGift)
    {
        $stopLucky = settings()->get('stop_luckyGift');
        if ($stopLucky == 1) {
            return Common::apiResponse(0, __('api_responses.try_again'));
        }

        $validator = Validator::make($request->all(), [
            'id'       => 'required',
            'owner_id' => 'required',
            'toUid'    => 'required',
            'num'      => 'required|integer|min:1',
            'count'    => 'sometimes|integer|min:1',
        ]);

        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }

        $data = $request->all();
        $user = $request->user();

        try {
            $data = (new \App\Services\Gifts\LuckyGiftService())->sendLuckyGift2($data, $user, $updateUserWhenSendGift);
        } catch (\Exception $e) {
            return Common::apiResponse(0, $e->getMessage());
        }
        return Common::apiResponse(1, __('api_responses.success'), $data);
    }

    // public function sendLuckyGift(Request $request, UpdateUserWhenSendGift $updateUserWhenSendGift)
    // {
    //     //update when send the gift
    //     $data    = $request->all();
    //     $user    = $request->user();
    //     $userId  = $user->id;
    //     $ownerId = $data['owner_id'];
    //     $giftId  = $data['id'];
    //     $number  = $data['num'];

    //     //validation parameter
    //     if (!$data['id'] || !$data['owner_id'] || !$data['toUid'] || !$data['num'])
    //         return Common::apiResponse(0, __('api_responses.missing_params'), $data);

    //     //validation if pass num < 1
    //     if ($data['num'] < 1) return Common::apiResponse(0, 'The number of gifts cannot be less than 1', null, 422);

    //     //get the gift data from id in the parameter
    //     $gift = Gift::query()->select([
    //         'id', 'name', 'type', 'price', 'vip_level', 'is_play', 'img', 'show_img',
    //         'show_img2'
    //     ])->where('type', 6)->where('id', $giftId)->where('enable', 1)->first();
    //     // Validation if gift return null
    //     if (!$gift) return Common::apiResponse(0, 'Gift does not exist or has been removed', null, 404);
    //     // receivers ids
    //     $receiversIds = explode(',', $data['toUid']);
    //     $numberOfGift = $number * count($receiversIds);
    //     $totalPrice   = $gift->price * $numberOfGift;

    //     // if user didn't have inf coins throw exception
    //     if ($user->di < $totalPrice) return Common::apiResponse(0, 'Insufficient balance, please go to recharge!', null, 407);

    //     $roomKey = '';
    //     // Get Room Data
    //     $room = Room::withoutAppends()
    //         ->where(['uid' => $ownerId])
    //         ->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,microphone,charizma_status')
    //         ->first();
    //     // Validation if no room
    //     if (!$room) return Common::apiResponse(0, 'room does not exist', null, 404);

    //     // if not a visitor in this room
    //     $roomVisitors   = explode(",", $room->room_visitor);
    //     $roomVisitors[] = $ownerId;
    //     if (!in_array($userId, $roomVisitors)) return Common::apiResponse(0, 'you are not in this room', null, 403);

    //     // validation if this gift vip < user vip then throw Exception
    //     /*if (@$user->UserVip->level ?? 0 < $gift->vip_level) return Common::apiResponse(0, 'vip ' . $gift->vip_level . ' to send this gift');*/

    //     $wallets = CoreWallet::query()->whereIn('id', [1, 2])->get();
    //     $owner_wallet = $wallets->where('id', 2)->first();
    //     $app_wallet   = $wallets->where('id', 1)->first();
    //     //decrement the user coins
    //     try {
    //         $updateUserWhenSendGift->send($totalPrice, $user);
    //     } catch (NotInfMoneyException $e) {
    //         return Common::apiResponse(0, 'Insufficient balance, please go to recharge!', null, 407);
    //     }
    //     // get received users data
    //     $receivedUsers = User::withoutAppends()->whereIn('id', $receiversIds)->select(['id', 'name'])->get();

    //     //update user ids
    //     $receiversIds        = $receivedUsers->pluck('id')->toArray();
    //     $coinsForReceiver                   = $number * ($gift->price * 0.1);
    //     $price               = $coinsForReceiver * $receivedUsers->count();
    //     $owner_wallet->coins += $price;
    //     $app_wallet->coins   += $price * 8;
    //     $app_wallet->save();
    //     $owner_wallet->save();

    //     dispatchJobToQueue((new UpdateUserDataWhenSendGift($userId, $room->id, $receiversIds, $giftId, $number, $price)), 'luckyGift');

    //     //update receivers diamonds
    //     $updateUserWhenSendGift->updateUsers($coinsForReceiver, $receiversIds);

    //     if ($receivedUsers?->count() > 1) {
    //         $to = 'الغرفة';
    //     } else {
    //         $to = @$receivedUsers->first()->name;
    //     }

    //     // Sender cashback
    //     // Code to be executed in case of Lucky Gifts, and the sender is a winner
    //     $isWinner  = $this->is_winner($gift);
    //     $isPopular = false;
    //     if ($isWinner && $app_wallet->coins > ($gift->price * $number)) {
    //         $valueTimes = floor($app_wallet->coins / ($gift->price * $number));
    //         $times = min($valueTimes, 1010);
    //         $probability = collect([
    //             [5, 10, 20],
    //             [50, 100],
    //             [250, 500, 1000]
    //         ]);


    //         $properties          = $gift->luckyGift?->min_percentage;
    //         $cashback_percentage = $properties ? $valueTimes : rand(1, $times);
    //         $properties1         = $properties ? explode(',', $properties) : null;

    //         $cashback_percentage = (new LuckyGiftService())->getRandomDuplicate($probability, $cashback_percentage, $properties1);

    //         $cashback_value = $cashback_percentage * $gift->price * $number;
    //         if ($cashback_percentage > 0) {
    //             $user->enableSaving = false;
    //             $user->di           += $cashback_value;
    //             $user->save();
    //             $app_wallet->coins -= $cashback_value;
    //             $app_wallet->save();
    //             if ($cashback_percentage > 1) {
    //                 $message = "مبروووك .. كسبت " . $cashback_percentage . " ضعف قيمة الهدية";
    //             }
    //         } else {
    //             $isWinner = false;
    //         }

    //         //send to zigo this data to show in all rooms if cashback percentage > 20
    //         $isPopular = $cashback_percentage >= 250;
    //         if ($isPopular) {
    //             $zigoData = [
    //                 'user_id'      => $userId,
    //                 'user_image'   => @$user->avatar->image ?? '',
    //                 'gift_image'   => @$gift->img ?? '',
    //                 'owner_id'     => $ownerId,
    //                 'user_name'    => $user->name ?? '',
    //                 'room_id'      => $room->id,
    //                 'percentage'   => $cashback_percentage,
    //                 'is_room_pass' => ($room->room_pass != null && $room->room_pass != ''),

    //             ];
    //             $this->sendToZegoLuckyGift($zigoData);
    //         }
    //     } else {
    //         $cashback_percentage = 0;
    //     }
    //     UserCommon::UserLuckyGift($isWinner, $userId, $gift, ($gift->price * $number * $cashback_percentage), $number);

    //     $commentMessage = "  {$number} x ارسل هدية حظ " . " قيمتها {$gift->price} " . " الى {$to}";

    //     $sendMessage = !isset($message) ? $commentMessage : $message;


    //     $positions = [];
    //     $userInMic = [];
    //     $mics =  explode(',', $room->microphone);

    //     foreach ($mics as $key => $value) {
    //         $founded = in_array($value, $receiversIds);
    //         if ($founded) {
    //             $userInMic[] = $value;
    //             $positions[] = $key;
    //         }
    //     }

    //     if (count(array_diff($receiversIds, $userInMic)) > 0) {
    //         $positions[] = -1;
    //     }

    //     return Common::apiResponse(1, $sendMessage, [
    //         'gift_image'      => $gift->img,
    //         'receiver_name'   => $to,
    //         'sender_name'  => $user->name ?? '',
    //         'sender_img'  => $user->profile?->avatar ?? '',
    //         'win_coins'       => (int)($gift->price * $number * $cashback_percentage),
    //         'is_win'          => $isWinner,
    //         'is_popular'      => $isPopular ?? false,
    //         'comment_message' => $commentMessage,
    //         'position' => $positions
    //     ]);
    // }
    // public function sendLuckyGift2(Request $request, UpdateUserWhenSendGift $updateUserWhenSendGift)
    // {
    //     $stopLucky = settings()->get('stop_luckyGift');
    //     if ($stopLucky == 1) {
    //         return Common::apiResponse(0, __('api_responses.try_again'));
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'id'       => 'required',
    //         'owner_id' => 'required',
    //         'toUid'    => 'required',
    //         'num'      => 'required|integer|min:1',
    //         'count'    => 'sometimes|integer|min:1',
    //     ]);

    //     if ($validator->fails()) {
    //         return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
    //     }

    //     $data = $request->all();
    //     $user = $request->user();

    //     try {
    //         $data = (new \App\Services\Gifts\LuckyGiftService())->sendLuckyGift2($data, $user, $updateUserWhenSendGift);
    //     } catch (\Exception $e) {
    //         return Common::apiResponse(0, $e->getMessage());
    //     }
    //     return Common::apiResponse(1, __('api_responses.success'), $data);
    // }

    // public function sendLuckyGift3(Request $request, UpdateUserWhenSendGift $updateUserWhenSendGift)
    // {
    //     $stopLucky = settings()->get('stop_luckyGift');
    //     if ($stopLucky == 1) {
    //         return Common::apiResponse(0, __('api_responses.try_again'));
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'id'       => 'required',
    //         'owner_id' => 'required',
    //         'toUid'    => 'required',
    //         'num'      => 'required|integer|min:1',
    //         'count'    => 'sometimes|integer|min:1',
    //     ]);

    //     if ($validator->fails()) {
    //         return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
    //     }

    //     $data = $request->all();
    //     $user = $request->user();

    //     try {
    //         $data = (new \App\Services\Gifts\LuckyGiftService())->sendLuckyGift3($data, $user, $updateUserWhenSendGift);
    //     } catch (\Exception $e) {
    //         return Common::apiResponse(0, $e->getMessage());
    //     }
    //     return Common::apiResponse(1, __('api_responses.success'), $data);
    // }

    // public function sendToZegoLuckyGift($zigoData)
    // {

    //     $d     = [
    //         "messageContent" => [
    //             "msg"     => "SHBL",
    //             'uid' => $zigoData['user_id'],
    //             'uImg' => $zigoData['user_image'],
    //             'gImg' => $zigoData['gift_image'],
    //             'ownerId' => $zigoData['owner_id'],
    //             'uName' => $zigoData['user_name'],
    //             'per'   => $zigoData['percentage'],
    //             'isPass' => $zigoData['is_room_pass']

    //         ]
    //     ];
    //     $json  = json_encode($d);

    //     dispatchJobToQueue(new AllOpeningRoomsZegoRequest($json, $zigoData['user_id'], $zigoData['room_id'], isExceptRoom: true), 'heavyProcessing');
    // }

    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|null
     */
    public function getReceivedAndSanderPercentage(): array
    {
        $keys       = ['sender_percentage', 'received_percentage'];
        $collection = Common::getConfFromKey($keys);
        $values = [];
        foreach ($keys as $key) {
            $config    = $collection->where('name', $key)->first();
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
        $index       = $luckyRandom <= 5 ? 0 : (($luckyRandom <= 8) ? 1 : 2);

        $arr                 = $probability[$index];
        $randomIndex         = rand(0, (count($arr) - 1));
        return $arr[$randomIndex];
    }

    public function ofLucky()
    {
        return Common::apiResponse(0, __('api_responses.update_your_version'));
    }
}
