<?php
namespace App\Helpers;

use App\Models\OVip;
use App\Models\Vip;
use App\Models\Gift;
use App\Models\Pack;
use App\Models\Room;
use App\Models\User;
use App\Models\Ware;
use App\Models\Agency;
use App\Models\Config;
use App\Models\Target;
use Encore\Admin\Show;
use GuzzleHttp\Client;
use App\Models\Country;
use App\Models\GiftLog;
use App\Models\PackLog;
use App\Models\UserVip;
use App\Models\UserSallary;
use Illuminate\Support\Str;
use GuzzleHttp\Psr7\Request;
use Kreait\Firebase\Factory;
use App\Models\UserLuckyGift;
use Illuminate\Support\Carbon;
use App\Models\OfficialMessage;
use Encore\Admin\Facades\Admin;
use App\Models\Owner_pid_target;
use App\Models\UserCodeInvitation;
use App\Models\UserEarnInvitation;
use Illuminate\Support\Facades\DB;
use App\Models\AgencyMangerPullingOut;
use App\Traits\HelperTraits\InfoTrait;
use App\Traits\HelperTraits\RoomTrait;
use App\Traits\HelperTraits\ZegoTrait;
use Twilio\Rest\Client as TwilioClint;

use App\Http\Resources\CountryResource;
use App\Traits\HelperTraits\AdminTrait;
use App\Traits\HelperTraits\CalcsTrait;
use App\Traits\HelperTraits\MoneyTrait;
use App\Traits\HelperTraits\FilterTrait;
use App\Traits\HelperTraits\AttributesTrait;
use Illuminate\Database\Eloquent\Collection;
use App\Classes\Facades\Agency as FacadesAgency;
use Modules\Public\Http\Services\UserCounterServices;
use App\Models\CoinTarget;
use App\Models\PeriodTarget;
use App\Models\UserCoinTarget;
use App\Models\UserTargetCoin;

class UserCommon{


    public static function specialTransfer($amount)
    {
        $usd_trans = Config::where("name",'special_transfer_to_usd')->first();
        $data = 0;
        if ($usd_trans != null) {
            $data= $amount / $usd_trans->value;
        }
        return $data;
    }

    public static function CheckUserNew($userId)
    {
        $user = User::where("id",$userId)->first();
        $createdAt = new \Carbon\Carbon($user->created_at);
        $now = \Carbon\Carbon::now();
        $data=UserCodeInvitation::with("user")->where(['invited_id'=>$userId])->first();
        if ($createdAt->diffInHours($now) <= 48 && $data == null ) {
            return true;
        } else {
            return false;
        }
    }

    public static function CheckUserParent($userId)
    {
        $data=UserCodeInvitation::with("user")->where(['invited_id'=>$userId])->first();
        return $data;
    }

    public static function UserStatistic($userId,$type, bool $reals = false)
    {
        $period = UserCommon::getPeriodTarget();

        // $type = 0;

        $user = User::withCount(['reals' => function ($reals) use ($type, $period) {

            $reals->whereBetween('created_at', [$period['start_at'], $period['end_at']]);
        },'real_comments'=>function($real_comments) use ($period){

            $real_comments->whereBetween('created_at', [$period['start_at'], $period['end_at']]);

        },'real_likes'=>function($real_likes) use ($period){
            $real_likes->whereBetween('created_at', [$period['start_at'], $period['end_at']]);

        },'moments'=>function($moments) use ($period){
            $moments->whereBetween('created_at', [$period['start_at'], $period['end_at']]);
        },'moment_comments'=>function($moment_comments) use ($period){

            $moment_comments->whereBetween('created_at', [$period['start_at'], $period['end_at']]);

        },'moment_likes'=>function($moment_likes) use ($period){

            $moment_likes->whereBetween('created_at', [$period['start_at'], $period['end_at']]);

        }])->find($userId);

        $key = ($reals)? 'reals' : 'reel';
        $key2 = ($reals)? 'moments' : 'moment';
        $key3 = ($reals)? 'like' : 'likes';
        $key4 = ($reals)? 'comment' : 'comments';

        $data=[
            $key2=>[
                'upload'=>$user->moments_count ?? 0,
                $key3=>$user->moment_likes_count ?? 0,
                $key4=>$user->moment_comments_count ?? 0,
            ],
            $key=>[
                'upload'=>$user->reals_count ?? 0,
                $key3=>$user->real_likes_count ?? 0,
                $key4=>$user->real_comments_count ?? 0,
            ],

        ];
        return $data;
    }

    public static function UserEarnedInvitation($userId,$amount)
    {
        $invitation=UserCodeInvitation::where("invited_id",$userId)->first();
        if ($invitation) {
            $invitationDate = Carbon::parse($invitation->created_at)->format("Y-m-d");
            $oneMonthAgo = Carbon::parse($invitationDate)->addMonths(settings()->get('invitation_code_date') ?? 1); // This subtracts one month from the current date
            $formattedDate = $oneMonthAgo->format('Y-m-d');
            if (date("Y-m-d") <  $formattedDate) {
                $config_earn_from_invitation=Config::where("name","earn_from_invitation")->first();
                $earn_from_invitation_host_agency=Config::where("name","earn_from_invitation_host_agency")->first();
                $parent=User::find($invitation->user_id);
                if ($parent) {
                    $precentage=0;
                    if ($parent->type_user == 3 || $parent->type_user == 4) {
                        if ($earn_from_invitation_host_agency != null) {
                            $precentage=$earn_from_invitation_host_agency->value;
                        }else{
                            return '';
                        }

                    }else{
                        if ($config_earn_from_invitation != null) {
                            $precentage=$config_earn_from_invitation->value;
                        }else{
                            return '';
                        }
                    }

                    // percentage vlaue
                    $parent_win = ($amount * $precentage)/100;

                    // add to parent value earn
                    $parent->di += $parent_win;
                    $parent->save();

                    // add in total
                    $invitation->invited_charge +=$amount;
                    $invitation->user_percentage +=$parent_win;
                    $invitation->save();

                    // add in charge details
                    UserEarnInvitation::create([
                        "parent_id"=>$parent->id,
                        "user_id"=>$invitation->invited_id,
                        "user_charge"=>$amount,
                        "parent_percentage"=>$parent_win,
                    ]);
                }
            }
        }
    }

    public static function UserLuckyGift($isWin, $userId, Gift $gift, $value, $number,$totalNumWin,$totalUserWin){
        $data = [
            'user_id' => $userId,
            'gift_id' => @$gift->id,
            'type' => $isWin ? 1 : 0,
            'value' => $value,
            'number' => $number,
            'total_num_win' => $totalNumWin,
            'total_win' => $totalUserWin,
            'gift_price' => @$gift->price,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('user_lucky_gifts')->insert($data);
    }

    public function userMoreStatistics(User $user)
    {

      $userRacksPrice =  $user->userPacks->whereIn('type',[4,5,6])->where('sender_id','null')->sum('price');
        // @dump($userRacksPrice);
      $senderPacksPrice =  $user->sendPacks->whereIn('type',[4,5,6])->sum('price');
      $totalPacksPrice = ($userRacksPrice ?? 0) + ($senderPacksPrice ?? 0);
        // earned
        $user_statistic['earned']['charges']=$user->charges?->where("amount",">=",0)->sum("amount");
        $user_statistic['earned']['coin_logs']=$user->coinLogs?->sum("obtained_coins");
        $user_statistic['earned']['exchange_logs']=$user->exchangeLogs?->sum("value");
        $user_statistic['earned']['coin_games']=$user->coinGameUser?->where("type",1)->sum("coins");
        $user_statistic['earned']['lucky_gifts']=$user->luckyGifts?->where('type',1)->where("value",">=",0)->sum("value");
        // losed
        $user_statistic['losed']['charges']=$user->charges?->where("amount","<",0)->sum("amount");
        $user_statistic['losed']['gift_logs']=$user->giftLogsSender?->sum("giftPrice");
        $user_statistic['losed']['packs'] = $totalPacksPrice ?? 0;
        $user_statistic['losed']['coin_games']=$user->coinGameUser?->where("type",0)->sum("coins");
        $user_statistic['losed']['request_background_images']=$user->requestBackgroundImages?->where("status",'!=',2)->sum("price");

        // total
        $user_statistic['total']['earned']=array_sum($user_statistic['earned']);
        $user_statistic['total']['losed']=array_sum($user_statistic['losed']);
        $user_statistic['total']['minus-between']=$user_statistic['total']['earned'] - ($user_statistic['total']['losed'] *-1);

        return $user_statistic;
    }

    public static function userVip(User $user)
    {
        $vip = OVip::query ()->first();
        $user_vip_check=UserVip::query ()->where ('user_id',$user->id)->where ('level','>=',$vip->level)->first();
        $expire = $vip->expire;
        if ($expire == 0){
            $ex = 0;
        }else{
            $ex = now ()->addDays ($expire)->timestamp;
        }

        if (!$user_vip_check) {
            UserVip::query ()->create (
                [
                    'type'=>0,
                    'sender_id'=>0,
                    'user_id'=>$user->id,
                    'vip_id'=>$vip->id,
                    'level'=>$vip->level,
                    'expire'=>$ex,
                    'qty'=>1,
                    'price'=>$vip->price,
                    'total'=>0
                ]
            );
            Common::handelVip ($vip,$user);
        }
    }

    public static function arabicToEnglishNumbers($string) {
        $newNumbers = range(0, 9);
        // الأرقام العربية
        $arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

        return str_replace($arabicNumbers, $newNumbers, $string);
    }

    // public static function englishToArabicNumbers($string) {
    //     // الأرقام الإنجليزية
    //     $englishNumbers = range(0, 9);
    //     // الأرقام العربية
    //     $arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

    //     return str_replace($englishNumbers, $arabicNumbers, $string);
    // }


    public static function englishToArabicNumbers($string) {
        $numbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

        $createdAt = Carbon::parse($string)->locale('ar_SA')->isoFormat('h:mm:ss A');
        $createdAt = str_replace($numbers, $arabicNumbers, $createdAt);

        return $createdAt;
    }

    public static function englishToArabicNumbersDate($string) {
        $numbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

        $createdAt = Carbon::parse($string)->locale('ar_SA')->format('Y-m-d');
        $createdAt = str_replace($numbers, $arabicNumbers, $createdAt);

        return $createdAt;
    }

    public static function arabicToEnglishNumbersDate($string) {
        $arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        // تحويل التاريخ إلى صيغة يمكن فهمها باستخدام Carbon
        $createdAt = Carbon::parse($string)->format('Y-m-d');

        // استبدال الأرقام العربية بالأرقام الإنجليزية
        $createdAt = str_replace($arabicNumbers, $englishNumbers, $createdAt);

        return $createdAt;
    }

    public static function convertArabicNumbers($string) {
        $newNumbers = range(0, 9);
        $arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        return str_replace($arabicNumbers, $newNumbers, $string);
    }

    public static function addVipToUser(User $user,OVip $vip,$expir)
    {
        DB::beginTransaction ();
//        try {
//            UserVip::query ()->create (
//                [
//                    'type'=>1,
//                    'sender_id'=>0,
//                    'user_id'=>$user->id,
//                    'vip_id'=>$vip->id,
//                    'level'=>$vip->level,
//                    'expire' => $expir,
//                    'qty'=>1,
//                    'price'=>0,
//                    'total'=>0
//                ]
//            );
        $vipp=new UserVip();
        $vipp->type=1;
        $vipp->sender_id=0;
        $vipp->user_id=$user->id;
        $vipp->vip_id=$vip->id;
        $vipp->level=$vip->level;
        $vipp->expire=now()->addDay($expir)->timestamp ;
        $vipp->qty=1;
        $vipp->price=0;
        $vipp->total=0;
        $vipp->save();
        Common::handelVip($vip,$user,$expir);
        DB::commit ();

        Common::sendOfficialMessage ($user->id,__('تهانينا'),__('لقد حصلت على مستوى VIP جديد كهدية'));
        $tokens_notfacion[] = DB::table('users')->where('id', $user->id)->value('notification_id');
        $title=config('app.name_ar');
        $body=__('لقد حصلت على مستوى VIP جديد كهدية') .$user->name ;
        Common::send_firebase_notification($tokens_notfacion,$title,$body);
//            CustomNotification::vips($user, $expir, $vip->img);

    }

    public static function addWareToUser(User $user,Ware $ware,$expir)
    {
        $pack = Pack::query ()->where ('user_id',$user->id)->where ('target_id',$ware->id)->first ();
        if($pack){
            if ($pack->expire == 0) return '';
            if ($pack->expire > now ()->timestamp) {
                if ($ware->expire != 0){
                    DB::beginTransaction ();
                    try {
                        $pack->expire += $expir ? ($expir * 86400) : ($ware->expire * 86400);
                        $pack->save ();
                        DB::commit ();
                        Common::sendOfficialMessage ($user->id,__('congratulations'),__('لقد حصلت على اهداء'));
                        (new UserCounterServices)->eventUser($user,'official-messages');

                        $tokens_notfacion[] = DB::table('users')->where('id', $user->id)->value('notification_id');
                        $title=config('app.name_ar');
                        $body=__('لقد حصلت على اهداء') .$user->name ;
                        Common::send_firebase_notification($tokens_notfacion,$title,$body);
                    }catch (\Exception $exception){
                        DB::rollBack ();
                    }

                }
            }else{
                $pack->delete ();
            }
        }
        DB::beginTransaction ();
        try {
            $arr['user_id']=$user->id;
            $arr['type']=$ware->type;
            $arr['get_type']=$ware->get_type;
            $arr['target_id']=$ware->id;
            $arr['num']=1;//$qty;
            $arr['expire']= $expir ? time() + ($expir * 86400) : ($ware->expire ? time()+($ware->expire * 86400) : 0);
            $arr['is_read']=1;
            Pack::query ()->create ($arr);
            DB::commit ();
//            \App\Helpers\CustomNotification::wareVip($user, $expir, $ware->name, $ware->show_img??'');
        }catch (\Exception $exception){
            DB::rollBack ();
        }
    }

    public static function addChargeLevel($userId, $amount)
    {
        $user = User::where("id", $userId)->first();
        $user->total_charge_coins += $amount;
        $level = Vip::where("exp", "<=", $user->total_charge_coins)->where('type', 5)->latest()->first();
        if ($level) {
            $user->charge_level = $level->level;
        }
        $user->save();
    }

    public static function updateUserTotalCoins($user_id, $total_coins)
    {
        $userTarget =  UserTargetCoin::updateOrCreate(
            ['user_id' => $user_id],
            ['total_coins' => \DB::raw("total_coins + {$total_coins}")]
        )->first();

        $coins = CoinTarget::with('gifts')
            ->where('coins', '<=', $userTarget->total_coins)
            ->orderBy('coins', 'desc')->first();

        $user = User::find($user_id);

        if ($coins) {
           $user_coin_target = UserCoinTarget::where([
                'user_id' => $user->id,
                'coin_target_id' => $coins->id,
            ])->first();

            if ($user_coin_target) {
                return true;
            }

            UserCoinTarget::create([
                'user_id' => $user->id,
                'coin_target_id' => $coins->id,
            ]);

            foreach ($coins->gifts as $reward) {
                switch ($reward->type) {
                    case 'coins':
                        CoinsTarget::assignCoinsUser($reward->item_id, $user);
                        break;
                    case 'vip':
                        CoinsTarget::assignVipUser($reward->item_id, $reward->expire, $user);
                        break;
                    case 'ware':
                        $ware = Ware::find($reward->item_id);
                        CoinsTarget::assignWareUser($ware, $reward, $user);
                        break;
                    case 'achievement':
                        CoinsTarget::assignAchievementUser($reward->item_id, $reward->expire, $user);
                        break;
                }
            }

        }

    }

    public static function getPeriodTarget()
    {
        $periodTarget =  PeriodTarget::latest()->first();
        if (!$periodTarget) {
            DB::table('period_target')->updateOrInsert([
                'start_at' => Carbon::now()->startOfMonth()->addDays(20)->subDay()->setHour(18)->setMinute(0)->setSecond(0),
                'end_at' => Carbon::now()->addMonth()->startOfMonth()->addDays(19)->setHour(17)->setMinute(59)->setSecond(59)
                ], ['created_at' => Carbon::now(),]);
            $periodTarget =  PeriodTarget::latest()->first();
        }
        $data['start_at'] = $periodTarget?->start_at ?? now()->toString();
        $data['end_at'] = $periodTarget?->end_at ?? now()->toString();
        $data['id'] = $periodTarget?->id ?? 0;
        return $data;
    }

    public static function getPeriodTargetIds($start_at ,$end_at)
    {
        $startDate = Carbon::parse($start_at);
        $endDate = Carbon::parse($end_at);

        $periodTarget = PeriodTarget::query()
            ->whereBetween('start_at', [$startDate, $endDate])
            ->orWhereBetween('end_at', [$startDate, $endDate])
            ->orWhere(function ($query) use ($startDate, $endDate) {
                $query->where('start_at', '<=', $startDate)
                      ->where('end_at', '>=', $endDate);
            })
            ->first();

        return $periodTarget;
    }



}
