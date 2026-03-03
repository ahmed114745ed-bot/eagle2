<?php

namespace Utd\Moments\Http\Controllers;

use App\Contracts\GiftsContract;
use App\Enums\UserDiamondLogType;
use App\Exceptions\NotInfMoneyException;
use App\Facades\CustomNotification;
use App\Helpers\Common;
use App\Helpers\UserDiamondLogHelper;
use App\Models\User;
use DB;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Utd\Gifts\Services\UpdateUserWhenSendGift;
use Utd\Moments\Entities\Moment;
use Utd\Moments\Entities\MomentUserGift;

use Utd\Moments\Transformers\MomentGiftUserResource;

class MomentUserGiftsController extends Controller
{
    protected $giftsService;

    public function __construct(GiftsContract $giftsService)
    {
        $this->giftsService = $giftsService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index($momentId)
    {
        //        Moment::query()->where('id')

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     */
    public function create()
    {
        return view('moment::create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Renderable
     */
    public function store(Request $request, $moment_id, UpdateUserWhenSendGift $updateUserWhenSendGift)
    {
        $data = $request;
        $user = $request->user();
        // $userId  = $user->id;
        $moment_id = $moment_id;
        $giftId = $data['gift_id'];
        $number = $data['num'];
        $moment = Moment::find($moment_id);
        // Validation if moment return null
        if (!$moment) {
            return Common::apiResponse(0, 'Moment does not exist or has been removed', null, 404);
        }

        // validation parameter
        if (!$data['gift_id'] || !$data['num']) {
            return Common::apiResponse(0, __('missing params'), $data->all());
        }

        // validation if pass num < 1
        if ($data['num'] < 1) {
            return Common::apiResponse(0, 'The number of gifts cannot be less than 1', null, 422);
        }

        $gift = $this->giftsService->getGift($giftId);

        if (!$gift || !$gift->enable) {
            return Common::apiResponse(0, 'Gift does not exist or has been removed', null, 404);
        }

        // attached Moments and gifts
        $moment->gifts()->attach($gift, ['user_id' => $user->id, 'num' => $number]);
        // receivers ids
        $receiversIds = explode(',', $data['toUid']);
        $numberOfGift = $number * count($receiversIds);

        $totalPrice = $gift->price * $numberOfGift;

        // if user didn't have inf coins throw exception
        if ($user->di < $totalPrice) {
            return Common::apiResponse(0, 'Insufficient balance, please go to recharge!', null, 407);
        }

        // validation if this gift vip < user vip then throw Exception
        $vip_level = @Common::ovip_center($user);
        if (@$vip_level->level < $gift->vip_level) {
            return Common::apiResponse(0, 'vip ' . $gift->vip_level . ' to send this gift');
        }

        try {
            $updateUserWhenSendGift->send($totalPrice, $user);
        } catch (NotInfMoneyException $e) {
            return Common::apiResponse(0, 'Insufficient balance, please go to recharge!', null, 407);
        }

        // get received users data
        $receivedUsers = $moment->user;
        // User::withoutAppends()->with(['agency', 'profile'])->whereIn('id', $receiversIds)->get();
        $to = $receivedUsers->name;
        $fromName = $user->name;

        $this->sendGift($number, $moment_id, $gift, $user, $receivedUsers);
        $price = $number * $gift->price;
        $updateUserWhenSendGift->update($price, $receivedUsers);

        UserDiamondLogHelper::logByType(
            $receivedUsers->id,
            $price,
            $receivedUsers->monthly_diamond_received,
            UserDiamondLogType::MOMENT,
            $user->id,

        );

        return Common::apiResponse(1, "  {$number} x ارسل هدية  " . " قيمتها {$gift->price} " . " الى {$to}");
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('moment::show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('moment::edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function sendGift($number, $momentId, $gift, User $senderUser, $receivedUser, $isPlay = 0, $totalPrice = null)
    {
        if ($totalPrice === null) {
            $totalPrice = $gift->price * $number;
        }

        $info = [
            'giftId' => $gift->id,
            'roomowner_id' => 0,
            'giftNum' => $number,
            'giftName' => $gift->name ?: '_',
            'giftPrice' => $totalPrice,
            'app_profit_coins' => $totalPrice,
            'sender_id' => $senderUser->id,
            'receiver_id' => $receivedUser->id,
            'is_play' => $isPlay ? 2 : 1,
            'type' => 2,
            'moent_id' => $momentId,
            'created_at' => date('Y-m-d H:i:s', time()),
            'updated_at' => date('Y-m-d H:i:s', time()),
        ];

        $this->giftsService->createGiftLog($info);

        CustomNotification::sendMomentGift($senderUser, $gift, $receivedUser, $momentId);
    }

    public function getGifts($id)
    {
        $moment = Moment::with('gifts')->find($id);
        if (!$moment) {
            return Common::apiResponse(0, 'Moment does not exist or has been removed', null, 404);
        }

        $giftModel = config('moments.models.gift', 'Utd\Gifts\Entities\Gift');
        $giftsTable = 'gifts'; // Default table name

        if (class_exists($giftModel)) {
            $giftsTable = (new $giftModel)->getTable();
        }

        $data = $moment->gifts()
            ->select("{$giftsTable}.img", DB::raw('CAST(sum(moment_user_gifts.num) AS INT) as num_gift'))
            ->groupBy("{$giftsTable}.id", "{$giftsTable}.img", 'moment_user_gifts.moment_id', 'moment_user_gifts.gift_id')
            ->orderByDesc('num_gift')
            ->get();

        return Common::apiResponse(1, 'successful', $data, 200);
    }

    public function userGift($id)
    {
        $momentsGift = MomentUserGift::selectRaw('user_id, moment_id, SUM(num) as num')
            ->where('moment_id', $id)
            ->groupBy('user_id', 'moment_id')
            ->with('user')
            ->get();

        return Common::apiResponse(1, 'successful', MomentGiftUserResource::collection($momentsGift), 200);
    }
}
