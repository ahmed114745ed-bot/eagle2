<?php

namespace Modules\Moment\Http\Controllers;

use App\Classes\Gifts\UpdateUserWhenSendGift;
use App\Exceptions\NotInfMoneyException;
use App\Facades\CustomNotification;
use App\Helpers\Common;
use App\Http\Resources\GiftResource;
use App\Models\Gift;
use App\Models\GiftLog;
use App\Models\User;
use App\Models\UserLuckyGift;
use DB;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Moment\Entities\Moment;

class MomentUserGiftsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index($momentId)
    {
//        Moment::query()->where('id')

    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('moment::create');
    }

    public function get_users_gifts($id){

        $types = [
            1 => __('normal'),
            2 => __('hot'),
            3 => __('country'),
            4 => __('Moment'),
            5 => __('Famous gifts'),
            6 => __('Lucky gifts'),
            7 => __('events')
        ];

        $data = GiftLog::select(
            'gifts.type',
            'gifts.id as gift_id',
            'gifts.*',
            DB::raw('COUNT(gift_logs.id) as send_count')
        )
        ->rightJoin('gifts', 'gifts.id', '=', 'gift_logs.giftId') // Use RIGHT JOIN to include all gifts
        ->where(function ($query) use ($id){
            $query->where('gift_logs.sender_id', $id) // Filter by specific user in gift_logs
                  ->orWhereNull('gift_logs.sender_id'); // Include gifts not in gift_logs
        })
        ->groupBy('gifts.type', 'gifts.id')
        ->orderByDesc(DB::raw('COUNT(gift_logs.id)')) // Order by send_count in descending order
        ->get()
        ->groupBy('type')
        ->map(function ($gifts, $type) use ($types) {
            return [
                $types[$type] => $gifts->map(function ($gift) {
                    return [
                        'id' => $gift->gift_id,
                        'name' => app()->getLocale() == 'ar' ? $gift->name : $gift->e_name,
                        'type' => $gift->type == 1 ? 'normal' : 'hot',
                        'price' => $gift->price ?: 0,
                        'img' => $gift->img ?: '',
                        'show_img' => $gift->show_img ?: '',
                        'show_img2' => $gift->show_img2 ?: '',
                        'vip_level' => $gift->vip_level ?: 0,
                        'is_on' => ($gift->vip_level <= Common::getLevel(request()->user()->id, 3)) ? 1 : 0,
                        'music_gift' => $gift->music_gift ? 1 : 0,
                        'international_gift' => $gift->international_gift ? 1 : 0,
                        'image_type' => $gift->image_type ?? '',
                        'send_count' => $gift->send_count ?: 0, // Default to 0 if no logs
                    ];
                }),
            ];
        })
        ->values();

        return response()->json([
            'data' => $data,
            'message' => 'user gifts returned successfully',
            'staus' => 200
        ]);
        //$user->luckyGifts
    }
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request, $moment_id, UpdateUserWhenSendGift $updateUserWhenSendGift)
    {
        $data    = $request;
        $user    = $request->user();
        // $userId  = $user->id;
        $moment_id = $moment_id;
        $giftId  = $data['gift_id'];
        $number  = $data['num'];
        $moment = Moment::find($moment_id);
        // Validation if moment return null
        if (!$moment) return Common::apiResponse(0, 'Moment does not exist or has been removed', null, 404);

        //validation parameter
        if (!$data['gift_id']  || !$data['num'])
        return Common::apiResponse(0, __('missing params'), $data->all());

        //validation if pass num < 1
        if ($data['num'] < 1) return Common::apiResponse(0, 'The number of gifts cannot be less than 1', null, 422);



        //get the gift data from id in the parameter
        $gift = Gift::query()->select([
            'id', 'name', 'type', 'price', 'vip_level', 'is_play', 'img', 'show_img',
            'show_img2'
        ])->where('id', $giftId)->where('enable', 1)->first();
        // Validation if gift return null
        if (!$gift) return Common::apiResponse(0, 'Gift does not exist or has been removed', null, 404);
       // attached moments and gifts
        $moment->gifts()->attach($gift, ['user_id' => $user->id , 'num' => $number]);
       // receivers ids
        $receiversIds = explode(',', $data['toUid']);
        $numberOfGift = $number * count($receiversIds);

        $totalPrice = $gift->price * $numberOfGift;

        // if user didn't have inf coins throw exception
        if ($user->di < $totalPrice) return Common::apiResponse(0, 'Insufficient balance, please go to recharge!', null, 407);

        // validation if this gift vip < user vip then throw Exception
        $vip_level = @Common::ovip_center($user);
        if (@$vip_level->level < $gift->vip_level) return Common::apiResponse(0, 'vip ' . $gift->vip_level . ' to send this gift');

        //decrement the user coins
        try {
            $updateUserWhenSendGift->send($totalPrice, $user);
        } catch (NotInfMoneyException $e) {
            return Common::apiResponse(0, 'Insufficient balance, please go to recharge!', null, 407);
        }

        // get received users data
        $receivedUsers = $moment->user;
        // User::withoutAppends()->with(['agency', 'profile'])->whereIn('id', $receiversIds)->get();
        $to    = $receivedUsers->name;
        $fromName = $user->name;

        $this->sendGift($number, $moment_id, $gift, $user, $receivedUsers);
        $price = $number * $gift->price;
        $updateUserWhenSendGift->update($price, $receivedUsers);

        return Common::apiResponse(1, "  {$number} x ارسل هدية  " . " قيمتها {$gift->price} " . " الى {$to}");
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('moment::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('moment::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }


    public function sendGift($number,$momentId ,Gift $gift, User $senderUser, $receivedUser,  $isPlay = 0, $totalPrice = null)
    {
        if ($totalPrice == null) $totalPrice = $gift->price * $number;

        $info['giftId']       = $gift->id;
        $info['roomowner_id'] =0;
        $info['giftNum']      = $number;
        $info['giftName']     = $gift->name ?: '_';
        $info['giftPrice']    = $totalPrice;
        $info['sender_id']    = $senderUser->id;
        $info['receiver_id']  = $receivedUser->id;
        $info['is_play']      = $isPlay ? 2 : 1;
        $info['type']         = 2;
        $info['moent_id']         = $momentId;
        $info['created_at']   = $info['updated_at'] = date('Y-m-d H:i:s', time());

        // $income = $this->calculate($room->uid,$senderUser->id,$info['giftPrice']);
        // $info['platform_obtain']=$income['platform'];   //platform
        // $info['receiver_obtain']=$income['toUid'];     //recipient
        // $info['roomowner_obtain']=$income['uid']+$income['uid_yj'];//homeowner

        // $info['agency_id']=$income['uid']+$income['uid_yj'];//homeowner


        GiftLog::query()->create($info);
        CustomNotification::sendMomentGift($senderUser, $gift, $receivedUser,$momentId);


    }

    public function getGifts($id)
    {

      $moment = Moment::with('gifts')->find($id);
      if (!$moment) {
        return Common::apiResponse(0, 'Moment does not exist or has been removed', null, 404);
    }
      $data = $moment->gifts()->select('gifts.img', DB::raw('CAST(sum(moment_user_gifts.num) AS INT) as num_gift'))
      ->groupBy('gifts.id', 'gifts.img', 'moment_user_gifts.moment_id', 'moment_user_gifts.gift_id' )->orderByDesc('num_gift')
      ->get();


      return Common::apiResponse(1, 'successful', $data, 200);
    }



}
