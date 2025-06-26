<?php

namespace App\Models;

use App\Helpers\Common;
use App\Traits\FollowTrait;
use App\Traits\MomentRelationshipTrait;
use App\Traits\PaymentGetWayTrait;
use App\Traits\TimestampsWithTimezone;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Log;
use Modules\Achievement\Http\Traits\AchievementUser;
use Modules\AgencyApp\Entities\AdditionalInfo;
use Modules\Chat\Traits\ChatUserTrait;
use Modules\Moment\Entities\Moment;
use Modules\Moment\Entities\MomentUserGift;
use Modules\Reals\Entities\Real;
use Modules\Reals\Traits\RealRelationshipTrait;
use Modules\SalaryTransaction\Entities\ChargeAgency;
use Modules\SalaryTransaction\Traits\UserTransferTrait;
use Modules\SpecialId\Traits\SpecialId;
use App\Models\Config as ConfigModel;

/**
 * @method static withoutAppends()
 */
class User extends Authenticatable
{
    use AchievementUser, ChatUserTrait, FollowTrait, HasApiTokens, HasFactory, MomentRelationshipTrait, Notifiable, PaymentGetWayTrait, RealRelationshipTrait, SoftDeletes, SpecialId, TimestampsWithTimezone, UserTransferTrait;

    /*
     * To enable and disable observer saving and updating methods
     */
    public static $withoutAppends = false;

    public $enableSaving = true;

    protected $loadedPacks = null;

    protected $specialPack = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',

    ];

    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'di' => 'integer',
        'received_level' => 'integer',
        'sender_level' => 'integer',
        'charge_status' => 'boolean',
        'transfer_salary' => 'boolean',
    ];

    protected $appends = [
        'user_diamond',
        'total_sender_level',
        'total_received_level',
        'original_uuid',
        'is_frozen',
        'total_charge_level',
        'photo',
    ];

    /* protected $appends = [
         'my_store',
         'lang',
         'avatar',
         'gender',
         'flag',
         'usd',
         'is_family_admin',
         'is_family_owner',
         'intro',
         'frame',

     ];*/

    public function images()
    {
        return $this->hasMany(ProfileGallary::class);
    }

    public function mutualFollows()
    {
        return $this->belongsToMany(self::class, 'follows', 'user_id', 'followed_user_id')
            ->withPivot('status', 'created_at')
            ->wherePivot('status', 1)
            ->whereIn('followed_user_id', function ($query) {
                $query->select('user_id')
                    ->from('follows')
                    ->whereColumn('follows.user_id', 'follows.followed_user_id')
                    ->where('follows.status', 1);
            });
    }

    public function ignores()
    {
        return $this->belongsToMany(self::class, 'profile_user_ignores', 'user_id', 'ignore_user_id')
            ->withTimestamps();
    }

    public function ignoredBy()
    {
        return $this->belongsToMany(self::class, 'profile_user_ignores', 'ignore_user_id', 'user_id')
            ->withTimestamps();
    }

    public function sameDeviceUsers()
    {
        return $this->hasMany(self::class, 'device_token', 'device_token');
    }

    public function likes()
    {
        return $this->belongsToMany(self::class, 'profile_user_likes', 'user_id', 'liked_user_id')
            ->withTimestamps();
    }

    public function likedBy()
    {
        return $this->belongsToMany(self::class, 'profile_user_likes', 'liked_user_id', 'user_id')
            ->withTimestamps();
    }

    public function agencyUserJob()
    {
        return $this->hasOne(AgencyUserJob::class, 'user_id', 'id');
    }

    public function agencyJoinRequest()
    {
        return $this->hasMany(AgencyJoinRequest::class, 'user_id');
    }

    public function userAgencyJoined()
    {
        return $this->hasMany(UsersJoinedAgency::class, 'user_id');
    }

    public function timeLog()
    {
        return $this->hasMany(TimeLog::class, 'user_id');
    }

    public function vipImage()
    {
        return $this->hasOne(Vip::class, 'level', 'total_received_level')
            ->where('type', 1);
    }

    public function getUserTypeAttribute()
    {
        return match ((int) ($this->type_user)) {
            0 => 'user',
            1 => 'host',
            2 => 'Host agent',
            3 => 'freight forwarder',
            4 => 'freight forwarder and Host agent',
            5 => 'Administrative',
            default => 'user',
        };
    }

    public function getFamilyIdAttribute($value)
    {
        return $value === 0 ? null : $value;
    }

    public function getTotalDays()
    {
        $month = @request()->month;
        $year = @request()->year;
        if (! $month) {
            $month = now()->month;
        }
        if (! $year) {
            $year = now()->year;
        }
        $subQuery = DB::table('live_times')
            ->select('uid', DB::raw('COUNT(*) AS entry_count'))
            ->whereMonth('created_at', $month)->whereYear('created_at', $year)
            ->where('uid', $this->id)
            ->groupBy('uid', DB::raw('DATE(created_at)')) // Group by uid and date
            ->havingRaw('SUM(hours) > 1')
            ->get();

        return $subQuery->count('entry_count');
    }

    public function getTotalDaysJoinedAgency($from_date = null)
    {
        $month = @request()->month;
        $year = @request()->year;
        if (! $month) {
            $month = now()->month;
        }
        if (! $year) {
            $year = now()->year;
        }

        $query = $this->liveTime()
            ->selectRaw('DATE(created_at) as date, SUM(hours) as total_hours')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year);

        if ($from_date) {
            $query->where('created_at', '>=', $from_date);
        }
        $hours_days = \Cache::get('hours_days') ?? 2;

        $days = $query
            ->groupBy('date')
            ->having('total_hours', '>=', $hours_days)
            ->get();

        return $days->count();
    }

    public function getSallaryInfo(): array
    {
        $month = (int) @request()->month;
        $year = (int) @request()->year;
        if (! $month) {
            $month = now()->month;
        }
        if (! $year) {
            $year = now()->year;
        }

        $userSallary = UserSallary::query()
            ->selectRaw('sum(sallary) as total_salary, sum(cut_amount) as total_cut_amount')
            ->where(function ($query) use ($year, $month) {
                $query->whereRaw('(year < ? OR (year = ? AND month <= ?))', [$year, $year, $month]);
            })
            ->where('user_id', $this->id)
            ->first();

        return $userSallary?->toArray() ?? [];
    }

    public function getSallaryInfoByMonth(): array
    {
        $month = now()->month;
        $year = now()->year;

        $userSallary = UserSallary::query()
            ->selectRaw('sum(sallary) as total_salary, sum(cut_amount) as total_cut_amount')
            ->where('user_id', $this->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        return $userSallary?->toArray() ?? [];
    }

    public function getSallaryInfoByMonth2($month, $year,$agencyId): array
    {
        $userSallary = UserSallary::query()
            ->selectRaw('sum(sallary) as total_salary, sum(cut_amount) as total_cut_amount')
            ->where('user_id', $this->id)
            ->where('user_agency_id', $agencyId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();
            
        return $userSallary?->toArray() ?? [];
    }

    public function additionalInfo()
    {
        return $this->hasMany(AdditionalInfo::class, 'user_id');
    }

    public function mangerType()
    {
        return $this->belongsTo(MangerType::class, 'manger_type_id');
    }

    public function manager()
    {
        return $this->hasOne(Admin::class, 'app_id');
    }

    public function requestBackgroundImages()
    {
        return $this->hasMany(RequestBackgroundImage::class, 'owner_room_id');
    }

    public function giftLogsSender()
    {
        return $this->hasMany(GiftLog::class, 'sender_id');
    }

    public function luckyGifts()
    {
        return $this->hasMany(UserLuckyGift::class);
    }

    public function follows()
    {
        return $this->hasMany(Follow::class, 'user_id');
    }

    public function coinGameUser()
    {
        return $this->hasMany(CoinGameUser::class);
    }

    public function exchangeLogs()
    {
        return $this->hasMany(ExchangeLog::class);
    }

    public function coinLogs()
    {
        return $this->hasMany(CoinLog::class);
    }

    public function charges()
    {
        return $this->hasMany(Charge::class);
    }

    public function chat_settings()
    {
        return $this->hasMany(ChatSetting::class);
    }

    public function userSetting()
    {
        return $this->hasOne(UserSetting::class, 'user_id');
    }

    public function codeInvitations()
    {
        return $this->belongsToMany(self::class, 'user_code_invitations', 'user_id', 'id')->withPivot('updated_at');
    }

    public function codeInvitationsEarn()
    {
        return $this->hasMany(UserEarnInvitation::class, 'parent_id');
    }

    public function scopeWithoutAppends($query)
    {
        self::$withoutAppends = true;

        return $query;
    }

    public function dress1()
    {
        return $this->hasOne(Ware::class, 'id', 'dress_1')->where('wares.type', 4)->where('wares.enable', 1)->select('id', 'img1', 'img2', 'image_type');
    }

    public function dress2()
    {
        return $this->hasOne(Ware::class, 'id', 'dress_2')->where('wares.type', 5)->where('wares.enable', 1)->select('id', 'img1', 'img2', 'show_img', 'image_type');
    }

    public function dress3()
    {
        return $this->hasOne(Ware::class, 'id', 'dress_3')->where('wares.type', 6)->where('wares.enable', 1)->select('id', 'img1', 'img2', 'image_type');
    }

    public function is_in_live()
    {
        return $this->rooms()->where('room_status', 1)->where('room_visitor', '!=', '')->exists();
    }

    public function ips()
    {
        return $this->hasMany(Ip::class, 'uid');
    }

    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    public function getMyStoreAttribute()
    {
        if (self::$withoutAppends) {
            return;
        }

        return Common::my_store($this->attributes['id']);
    }

    public function getAvatarAttribute()
    {
        if (self::$withoutAppends) {
            return;
        }

        return @$this->profile()->first()->avatar ?: Common::getConf('default_img');
    }

    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id');
    }

    public function getGenderAttribute()
    {
        if (self::$withoutAppends) {
            return;
        }

        return @$this->profile->gender ?? 1;
    }

    public function getFlagAttribute()
    {
        if (self::$withoutAppends) {
            return;
        }

        return @$this->country()->first()->flag ?: '';
    }

    public function country()
    {
        return $this->belongsTo(Country::class)->select('id', 'name', 'flag', 'language', 'e_name', 'phone_code', 'iso');
    }

    public function getLangAttribute()
    {
        if (self::$withoutAppends) {
            return;
        }

        return @$this->country()->language ?: 'en';
    }

    public function getNicknameAttribute($val)
    {
        return $val ?: '';
    }

    public function profileVisits()
    {
        return $this->belongsToMany(self::class, 'profile_visitors', 'user_id', 'visitor_id', 'id', 'id')->orderByDesc('profile_visitors.updated_at')->withPivot(['updated_at', 'created_at']);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class, 'uid');
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function agencies()
    {
        return $this->hasMany(Agency::class, 'agency_manger_id');
    }

    public function liveTime()
    {
        return $this->hasMany(LiveTime::class, 'uid');
    }

    public function UserliveTime()
    {
        return $this->hasMany(LiveTime::class);
    }

    public function scopeOfAgency($q)
    {
        $user = Auth::user();
        if (Auth::user()->isRole('agency')) {
            $q->whereNotNull('agency_id')->where('agency_id', '=', @$user->agency_id);
        }
    }

    public function getUsdAttribute()
    {
        if (self::$withoutAppends) {
            return;
        }

        return $this->old_usd + $this->target_usd - $this->target_token_usd;
    }

    //    protected static function booted()
    //    {
    //        if (Auth::user ()->isRole('agency')){
    //            static::addGlobalScope('of_agency', function (Builder $builder){
    //                $builder->where('agency_id', '=', Auth::id ());
    //            });
    //        }
    //
    //    }

    // Dashboard Relations
    public function user_family()
    {
        return $this->hasOne(Family::class, 'user_id');
    }

    public function getIsFamilyAdminAttribute()
    {
        if (self::$withoutAppends) {
            return;
        }
        $family_user = FamilyUser::query()->where('user_id', $this->id)->where('status', 1)->first();
        if ($family_user) {
            if ($family_user->user_type === 1) {
                return true;
            }

            return false;
        }

        return false;
    }

    public function getIsFamilyOwnerAttribute()
    {
        if (self::$withoutAppends) {
            return;
        }
        $family = Family::query()->where('user_id', $this->id)->exists();
        if ($family) {
            return true;
        }

        return false;
    }

    public function getImgAttribute()
    {
        return $this->avatar;
    }

    public function targets()
    {
        return $this->hasMany(UserTarget::class);
    }

    public function bans()
    {
        return $this->hasMany(Ban::class, 'uid', 'uuid');
    }

    public function getFollowDate($id)
    {
        $f =
            Follow::query()->where('user_id', $this->id)->where('followed_user_id', @request()->user()->id)->value('created_at');
        if ($f) {
            return $f;
        }

        return '';
    }

    public function getFollowDateAttribute()
    {
        $f =
            Follow::query()->where('user_id', $this->id)->where('followed_user_id', @request()->user()->id)->value('created_at');
        if ($f) {
            return $f;
        }

        return '';
    }

    public function getFollowedDateAttribute()
    {
        $f =
            Follow::query()->where('user_id', @request()->user()->id)->where('followed_user_id', $this->id)->value('created_at');
        if ($f) {
            return $f;
        }

        return '';
    }

    public function getIntroAttribute()
    {
        if (self::$withoutAppends) {
            return;
        }

        return Common::getUserDress($this->id, $this->dress_3, 6, 'show_img', true);
    }

    public function getFrameAttribute()
    {
        if (self::$withoutAppends) {
            return;
        }

        return Common::getUserDress($this->id, $this->dress_1, 4, 'show_img', true);
    }

    public function getBubbleAttribute()
    {
        return Common::getUserDress($this->id, $this->dress_2, 5, 'show_img', true);
    }

    public function intros_count()
    {
        return Pack::query()->where('user_id', $this->id)->where('type', 6)->where(function ($q) {
            $q->where('expire', 0)->orWhere('expire', '>=', now()->timestamp);
        })->count();
    }

    public function frames_count()
    {
        return Pack::query()->where('user_id', $this->id)->where('type', 4)->where(function ($q) {
            $q->where('expire', 0)->orWhere('expire', '>=', now()->timestamp);
        })->count();
    }

    public function bubble_count()
    {
        return Pack::query()->where('user_id', $this->id)->where('type', 5)->where(function ($q) {
            $q->where('expire', 0)->orWhere('expire', '>=', now()->timestamp);
        })->count();
    }

    public function hasRoom()
    {
        return Room::query()->where('uid', $this->id)->exists();
    }

    public function ownerRoom()
    {
        return $this->hasOne(Room::class, 'uid', 'id');
    }

    public function familyType()
    {
        return $this->hasOne(FamilyUser::class, 'user_id', 'id');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function getIsAgentAttribute()
    {
        return $this->ownAgency()->exists();
    }

    public function ownAgency()
    {
        return $this->hasOne(Agency::class, 'app_owner_id', 'id');
    }

    public function UserVip()
    {
        return $this->hasOne(UserVip::class, 'user_id')->where(function ($q) {
            $q->where('is_used', 1)->where(fn($q) => $q->where('expire', 0)->orWhere('expire', '>=', now()->timestamp));
        })->with('OVip')->orderByDesc('level');
    }

    public function userHaveVip()
    {
        return $this->hasMany(UserVip::class, 'user_id')->where(function ($q) {
            $q->where('is_used', 1)->where(fn($q) => $q->where('expire', 0)->orWhere('expire', '>=', now()->timestamp));
        })->with('OVip')->orderByDesc('level');
    }

    public function haveVip()
    {
        return $this->hasMany(UserVip::class, 'user_id');
    }

    public function getImageReceiverOrSender($name, $type)
    {
        $amount =
            $type === 2 ? $this->sender_level + $this->sub_sender_level : $this->received_level + $this->sub_receiver_level;

        $level = Vip::query()->where('type', $type)->where('level', $amount)->orderByDesc('exp')->first();

        return $level;
    }

    public function followers()
    {
        return $this->hasMany(Follow::class, 'followed_user_id', 'id');
    }

    public function followeds()
    {
        return $this->hasMany(Follow::class, 'user_id', 'id');
    }

    public function isFollowedBy($userId): bool
    {
        return $this->followers()
            ->where('user_id', $userId)
            ->where('status', 1)
            ->exists();
    }

    public function followBack(self $user)
    {
        $userId = $user->id;

        return $this->followers()->where('user_id', $userId)->exists();
    }

    public function followersMoment()
    {
        return $this->belongsToMany(self::class, 'follows', 'followed_user_id', 'user_id');
    }

    public function canJoinRoom(int $roomId): bool
    {
        return $this->id === $roomId;
    }

    public function getSalaryAttribute()
    {

        //        if ($this->agency_id) {
        $userSallary = UserSallary::query()

            ->where('user_id', $this->id)
            //   ->where('is_paid', 0)
            //   ->where('user_agency_id', $this->agency_id)
            ->orderByDesc('id')
            ->sum(DB::raw('sallary - cut_amount'));
        $roomSalary = RoomSalary::query()->whereHas('room', function ($q) {
            $q->where('uid', $this->id);
        })
            ->orderByDesc('id')
            ->sum(DB::raw('salary - cut_amount'));

        return floor($userSallary + (int) $roomSalary);
        //        } else {
        //            return 0;
        //        }
    }

    public function getSalaryWithoutCutAmountAttribute()
    {
        $userSallary = UserSallary::query()
            ->where('user_id', $this->id)
            ->sum(DB::raw('sallary'));



        return round($userSallary, 2);
    }

    public function setTotalChargeLevelAttribute(float $value)
    {
        $level = @$this->charge_level + $this->sub_charger_level;
        if ($level === $value) {
            return;
        }

        $this->sub_charger_level = $value - @$this->charge_level ?? 0;
        $diamonds = (@Vip::query()->where('type', 5)->where('level', '=', $value)->orderByDesc('exp')->limit(1)->first())?->exp ?? 0;
        $this->sub_charger_coins = $diamonds - $this->total_charge_coins;
    }

    public function getTotalChargeLevel(float $value)
    {
        $diamonds = (@Vip::query()->where('type', 5)->where('level', '=', $value)->orderByDesc('exp')->limit(1)->first()) ?? 0;

        return $diamonds;
    }

    public function getTotalChargeLevelAttribute()
    {
        return $this->charge_level + $this->sub_charger_level;
    }

    public function setSalaryAttribute()
    {
        if ($this->agency_id) {
            $salary =
                UserSallary::query()->where('user_id', $this->id)->where('is_paid', 0)->sum(DB::raw('sallary - cut_amount'));
            $this->attributes['salary'] = $salary;
        } else {
            $this->attributes['salary'] = 0;
        }
    }

    public function getNowRoomUidAttribute($value)
    {
        return $value === 0 ? null : $value;
    }

    public function room()
    {
        return $this->hasOne(Room::class, 'uid', 'now_room_uid');
    }

    public function myroom()
    {
        return $this->hasOne(Room::class, 'uid', 'id');
    }

    public function color_image()
    {
        return $this->hasOne(ImageColor::class, 'id', 'image_color_id');
    }

    public function getOldAttribute()
    {
        $currentYear = date('Y');
        $currentMonth = date('m');
        $old =
            UserSallary::query()->where('user_id', $this->id)->whereRaw("CONCAT(year, LPAD(month, 2, '0')) != CONCAT('$currentYear', LPAD('$currentMonth', 2, '0'))")->where('is_paid', 0)->sum(DB::raw('sallary - cut_amount'));

        return $old;
    }

    public function setOldUsdAttribute()
    {
        $currentYear = date('Y');
        $currentMonth = date('m');
        $old =
            UserSallary::query()->where('user_id', $this->id)->whereRaw("CONCAT(year, LPAD(month, 2, '0')) != CONCAT('$currentYear', LPAD('$currentMonth', 2, '0'))")->where('is_paid', 0)->sum(DB::raw('sallary - cut_amount'));
        $this->attributes['old_usd'] = $old;
    }

    public function target($month = null, $year = null)
    {
        if (! $month) {
            $month = date('m');
        }
        if (! $year) {
            $year = date('Y');
        }

        return $this->hasMany(UserSallary::class)->where('month', $month)->where('year', $year)->first();
    }

    public function family()
    {
        return $this->belongsTo(Family::class, 'family_id');
    }

    public function followPacks()
    {
        return $this->hasMany(Pack::class, 'user_id', 'id')->whereIn('packs.type', [4, 18])->where(function ($q) {
            $q->where('packs.expire', 0)->orWhere('packs.expire', '>=', time());
        });
    }

    public function ware()
    {
        return $this->hasOne(Ware::class, 'id', 'dress_1')->where('wares.type', 4)->where('wares.enable', 1)->select('id', 'img1', 'img2');
    }

    public function getCoinsStringAttribute()
    {
        return numToString($this->di);
    }

    public function storeLastMonthlyDiamondReceivedInHistory()
    {
        $lastDiamondReceived = $this->monthly_diamond_received;

        // Get the last history record
        $lastHistory = $this->history()->latest()->first();

        // Compare with the last history record to avoid duplications
        if (! $lastHistory || $lastDiamondReceived !== $lastHistory->diamond) {
            $this->history()->create([
                'user_id' => $this->id,
                'agency_id' => $this->agency_id,
                'diamond' => $lastDiamondReceived,
                'month' => now()->month,
                'year' => now()->year,
                'pid' => $this->id,
            ]);
        }
    }

    public function history()
    {
        return $this->hasMany(History::class);
    }

    public function storeMonthlyDiamondReceivedInHistory()
    {
        $this->history()->create([
            'diamond' => $this->monthly_diamond_received,
        ]);
    }

    public function userSallary()
    {
        return $this->hasOne(UserSallary::class, 'user_id', 'id');
    }

    public function totalUserSalary()
    {
        return $this->hasMany(UserSallary::class, 'user_id', 'id');
    }

    public function userPacks()
    {
        return $this->hasMany(Pack::class, 'user_id');
    }

    public function packs()
    {
        return $this->hasMany(Pack::class)->where(function ($q) {
            $q->where('expire', 0)->orWhere('expire', '>=', now()->timestamp);
        });
    }

    public function packsUser()
    {
        return $this->hasMany(Pack::class, 'user_id')->whereIn('type', [4, 5, 6, 25])->where('get_type', '!=', 1)->where('is_used', 1)->where(function ($q) {
            $q->where('expire', 0)->orWhere('expire', '>=', now()->timestamp);
        });
    }

    public function sendPacks()
    {
        return $this->hasMany(Pack::class, 'sender_id');
    }

    public function scopeIsFollow($query, $userId)
    {
        return $query->withExists('followeds', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        });
    }

    public function scopeGetFollowers(Builder $query, $userId): Builder
    {
        return $query->whereHas('followers', function ($query) use ($userId) {
            $query->where('user_id', $userId)->where('status', 1);
        });
    }

    public function getTotalSenderLevelAttribute()
    {
        return $this->sender_level;
    }

    public function agencyAdmins()
    {
        return $this->hasOne(AgencyUserJob::class, 'user_id')->where('type', 'requestManger');
    }

    public function getTotalSenderDiamondsAttribute()
    {
        return $this->total_diamond_send + $this->sub_sender_num;
    }

    public function getTotalReceivedDiamondsAttribute()
    {
        return $this->total_diamond_received + $this->sub_receiver_num;
    }

    public function getTotalReceivedLevelAttribute()
    {
        return $this->received_level;
    }

    public function setTotalSenderLevelAttribute(float $value)
    {
        $level = $this->sender_level;
        if ($level === $value) {
            return;
        }
        $expPercentages = Config::get('exp_percentages') ?? [0, 0];

        $this->sub_sender_level = 0;
        $this->sender_level = $value;
        $diamonds = (@Vip::query()->where('type', 2)->where('level', '=', $value)->orderByDesc('exp')->limit(1)->first())?->exp ?? 0;
        //        $this->sub_sender_num   = $diamonds - $this->total_diamond_send;
        //        $this->sub_sender_num   = ($diamonds - $this->total_diamond_send) + (( (( $diamonds - $this->total_diamond_send) * ( 2 * ($expPercentages[0] / 100)))) );
        /*if ($expPercentages[0] <= 1) {
            $this->sub_sender_num = ($diamonds - $expPercentages[0] * $this->total_diamond_send) / $expPercentages[0];
        } else {*/

        $this->sub_sender_num = ceil(($diamonds / $expPercentages['exp_sender_percentage'])) - $this->total_diamond_send;

        //        }
    }

    public function setTotalReceivedLevelAttribute(float $value)
    {
        $level = $this->received_level;
        if ($level === $value) {
            return;
        }
        $expPercentages = Config::get('exp_percentages') ?? [0, 0];

        $this->sub_receiver_level = 0;
        $this->received_level = $value;
        $diamonds = (@Vip::query()->where('type', 1)->where('level', '=', $value)->orderByDesc('exp')->limit(1)->first())?->exp ?? 0;
        //        $this->sub_receiver_num = $diamonds - $this->total_diamond_received;
        /*if ($expPercentages[0] <= 1) {
            $this->sub_receiver_num = ($diamonds - $this->total_diamond_received * $expPercentages[1]) / $expPercentages[1];
        } else {*/
        $this->sub_receiver_num = (ceil(($diamonds / $expPercentages['exp_received_percentage'])) - $this->total_diamond_received);

        //        }
    }

    public function interests()
    {
        return $this->hasManyThrough(Interest::class, InterestUser::class, 'user_id', 'id', 'id', 'interests');
    }

    public function managedAgencies()
    {
        return $this->hasMany(Agency::class, 'agency_dash_manger_id');
    }

    public function reals()
    {
        return $this->hasMany(Real::class, 'user_id');
    }

    public function moments()
    {
        return $this->hasMany(Moment::class, 'user_id');
    }

    public function admenUsersAPP()
    {
        return $this->hanMany(AdminUser::class);
    }

    public function getUserDiamondAttribute()
    {
        if ($this->type_user === 0 || $this->type_user === 3) {
            return $this->total_diamond_received;
        }

        return $this->monthly_diamond_received;
    }

    public function setUserDiamondAttribute($value)
    {
        if ($this->type_user === 0) {
            $this->total_diamond_received = $value;

            return;
        }
        $this->monthly_diamond_received = $value;
        $diff =
            $this->monthly_diamond_received - $this->getOriginal('monthly_diamond_received');
        $this->total_diamond_received += $diff;
        if ($this->total_diamond_received < 0) {
            $this->total_diamond_received = 0;
        }
    }

    public function getTotalSallary($month = null, $year = null)
    {
        if ($month === null) {
            $month = now()->month;
        }
        if ($year === null) {
            $year = now()->year;
        }
        if ($this->agency_id) {
            $userSallary = UserSallary::query()->where(function ($query) use ($year, $month) {
                $query->where(DB::raw('concat(year,"-", month)'), '<=', $year . '-' . $month);
            })->where('user_id', $this->id)
                ->where('is_paid', 0)
                ->where('user_agency_id', $this->agency_id)
                ->orderByDesc('id')
                ->sum(DB::raw('sallary'));

            return floor($userSallary ?? 0);
        }

        return 0;
    }

    public function getTotalDiamond($month = null, $year = null)
    {
        if ($month === null) {
            $month = now()->month;
        }
        if ($year === null) {
            $year = now()->year;
        }

        if ($this->agency_id) {
            $userSallary = UserTarget::query()
                ->where(function ($query) use ($year, $month) {
                    $query->where(DB::raw('concat(add_year,"-", add_month)'), '=', $year . '-' . $month);
                })->where('user_id', $this->id)
                ->where('agency_id', $this->agency_id)
                ->orderByDesc('id')
                ->sum(DB::raw('user_diamonds'));
            return floor($userSallary ?? 0);
        }

        return 0;
    }

    public function getTotalCutAmount($month = null, $year = null)
    {
        if ($month === null) {
            $month = now()->month;
        }
        if ($year === null) {
            $year = now()->year;
        }
        if ($this->agency_id) {
            $userSallary = UserSallary::query()->where(function ($query) use ($year, $month) {
                $query->where(DB::raw('concat(year,"-", month)'), '<=', $year . '-' . $month);
            })->where('user_id', $this->id)
                ->where('is_paid', 0)
                ->where('user_agency_id', $this->agency_id)
                ->orderByDesc('id')
                ->sum(DB::raw('cut_amount'));

            return floor($userSallary ?? 0);
        }

        return 0;
    }

    public function getOld($month = null, $year = null)
    {
        $currentYear = date('Y');
        $currentMonth = date('m');
        $old =
            UserSallary::query()->when(isset($month), function ($query) use ($month) {
                $query->where('month', '<=', $month);
            })->when(isset($year), function ($query) use ($year) {
                $query->where('year', '<=', $year);
            })->where('user_id', $this->id)->whereRaw("CONCAT(year, LPAD(month, 2, '0')) != CONCAT('$currentYear', LPAD('$currentMonth', 2, '0'))")->where('is_paid', 0)->sum(DB::raw('sallary - cut_amount'));

        return $old;
    }

    public function getSalary($month = null, $year = null)
    {
        if ($month === null) {
            $month = now()->month;
        }
        if ($year === null) {
            $year = now()->year;
        }
        if ($this->agency_id) {
            $userSallary = UserSallary::query()->where(function ($query) use ($year, $month) {
                $query->where(DB::raw('concat(year,"-", month)'), '<=', $year . '-' . $month);
            })
                ->where('user_id', $this->id)
                ->where('is_paid', 0)
                //                ->where('user_agency_id', $this->agency_id)
                ->orderByDesc('id')
                ->sum(DB::raw('sallary - cut_amount'));

            return round($userSallary, 2) ?? 0;
        }

        return 0;
    }

    public function carousels(): HasMany
    {
        return $this->hasMany(HomeCarousel::class, 'owner_id');
    }

    public function getLoadedPacks()
    {
        if ($this->loadedPacks === null) {
            //            $this->loadedPacks = $this->packs()->whereIn('type', [20, 18, 17, 20, 19, 16, 13, 3, 4, 5, 25, 6, 12])->where('is_used', 1)->with('ware')->get();
            $this->loadedPacks = $this->packs()->where('is_used', 1)->with('ware')->get();
        }

        return $this->loadedPacks;
    }

    public function eligiblePacks(): HasMany
    {
        return $this->hasMany(Pack::class)
            ->where('is_used', 1)
            ->where(function ($q) {
                $q->where('expire', 0)->orWhere('expire', '>=', now()->timestamp);
            })
            ->with('ware');
    }

    public function getUuidV2Attribute()
    {
        $pack = $this->eligiblePacks
            ->where('ware.value', $this->special_id)
            ->first();

        return ($this->special_id && $pack && $pack->is_used === 1) ? $this->special_id : $this->original_uuid;
    }

    public function getUuidV3Attribute()
    {
        $pack = $this->eligiblePacks->where('type', 25)
            ->where('ware.value', $this->special_id)
            ->first();
        return ($this->special_id && $pack && $pack->is_used === 1) ? $this->special_id : '';
    }

    /**
     * Custom accessor for UUID with special pack conditions.
     */
    public function getUuidAttribute($value)
    {
        $pack = $this->getLoadedPacks()
            ->where('ware.value', $this->special_id)
            ->first();

        return ($this->special_id && $pack && $pack->is_used === 1) ? $this->special_id : $this->original_uuid;
    }

    // originalUuid
    public function getOriginalUuidAttribute()
    {
        return @$this->attributes['uuid'] ?? '';
    }

    // aprilSalary

    public function getAprilSalaryAttribute()
    {
        $userSallary = UserSallary::query()->where(function ($query) {
            $query->where(DB::raw('concat(year,"-", month)'), '<', '2024-4');
        })
            ->where('user_id', $this->id)
            ->where('is_paid', 0)
            //                ->where('user_agency_id', $this->agency_id)
            ->orderByDesc('id')
            ->sum(DB::raw('sallary - cut_amount'));

        return floor($userSallary ?? 0);
    }

    public function getOnlineTimeAttribute($value)
    {
        if ($this->getPackWithType(20)) {
            return null;
        }

        return $value;
    }

    public function getRealOnlineTimeAttribute()
    {
        return @$this->attributes['online_time'] ?? $this->online_time;
    }

    public function getPackWithType($type)
    {

        $packs = $this->getLoadedPacks();

        /** @var \Illuminate\Database\Eloquent\Collection $packs */
        return $packs->where('type', $type)->isNotEmpty();
    }

    public function getPackWithTypeV2($type)
    {
        return $this->eligiblePacks->where('type', $type)->isNotEmpty();
    }

    public function nowGame()
    {
        return $this->belongsTo(AllGame::class, 'game_id');
    }

    public function userVips()
    {
        return $this->hasMany(UserVip::class, 'user_id');
    }

    public function getIsFrozenAttribute()
    {
        return optional($this->agency)->is_frozen;
    }

    public function getPhotoAttribute()
    {
        return @$this->profile->avatar;
    }

    public function userType()
    {
        switch ($this->type_user) {
            case 0:
                $userType = __('User');
                break;
            case 1:
                $userType = __('host');
                break;
            case 2:
                $userType = __('Host Agent');
                break;
            case 3:
                $userType = __('Shipping Agent');
                break;
            case 4:
                $userType = __('Resort & Shipping Agent');
                break;
            case 5:
                $userType = __('Admin');
                break;
            default:
                $userType = $this->type_user; // Keep the original value if no match is found
                break;
        }

        return $userType;
    }

    public function userTypeBadge()
    {
        $lang = app()->getLocale() ?? 'en';

        $types = [
            1 => 'agency_owner',
            2 => 'host',
            3 => 'shipping',
            4 => 'bd',
        ];

        $userType = $this->type_user;

        if (!isset($types[$userType])) {
            return $lang === 'ar' ? 'مستخدم' : 'User';
        }

        $applicableTypes = array_filter($types, function ($key) use ($userType) {
            return $key <= $userType;
        }, ARRAY_FILTER_USE_KEY);

        $configKeys = [];
        foreach ($applicableTypes as $key => $type) {
            $configKeys[] = "{$lang}_{$type}";
            $configKeys[] = "en_{$type}"; // fallback
        }

        $configs = ConfigModel::whereIn('name', $configKeys)->get()->keyBy('name');

        $html = '<div class="user-type-badges">';

        foreach ($applicableTypes as $typeKey => $typeName) {
            $localizedKey = "{$lang}_{$typeName}";
            $fallbackKey = "en_{$typeName}";

            $url = $configs[$localizedKey]->value ?? $configs[$fallbackKey]->value ?? null;
            $url = getImagePath($url);
            if ($url) {
                 $html .= '<img src="' . e($url) . '" alt="' . e($typeName) . '" style="width: 50%; height: 50%; object-fit: cover; border-radius: 4px; margin-right: 4px;">';
              //  $html .= '<img src="' . e($url) . '" alt="' . e($typeName) . '">';
            }
        }
        $html .= '</div>';

        return $html ?: ($lang === 'ar' ? 'مستخدم' : 'User');
    }
    public function wallet()
    {
        return $this->hasOne(UserWallet::class);
    }

    public function momentUserGift()
    {
        return $this->hasMany(MomentUserGift::class, 'user_id');
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function walletTransactionBackups()
    {
        return $this->hasMany(WalletTransactionBackup::class);
    }

    public function shippingAgency()
    {
        return $this->hasOne(ShippingAgency::class, 'app_owner_id');
    }

    public function hasShippingAgency()
    {
        return $this->shippingAgency()->exists();
    }

    public function hostAgency()
    {
        return $this->hasOne(Agency::class, 'app_owner_id');
    }

    public function hasHostAgency()
    {
        return $this->shippingAgency()->exists();
    }

    public function bdSalaries()
    {
        return $this->hasMany(BDSallary::class, 'bd_id');
    }

    public function getBdSalaryAttribute()
    {
        $userSallary = $this->bdSalaries()
            ->sum(DB::raw('sallary - cut_amount'));

        return floor($userSallary);
    }

    public function incrementCutAmountInBdSallary(int $amount)
    {
        $lastBdSalary = $this->bdSalaries()->latest()->first();

        if ($lastBdSalary) {
            $newAmount = max(0, $lastBdSalary->cut_amount + $amount);
            $lastBdSalary->update(['cut_amount' => $newAmount]);

            return true;
        }

        return false;
    }

    public function getUserTypesAttribute(): array
    {
        $userTypes = match (true) {
            in_array($this->type_user, [2, 4]) => [1, 2],
            $this->type_user === 1 => [1],
            default => []
        };

        if ($this->is_bd) {
            return [4];
        }

        if ($this->hasShippingAgency()) {
            $userTypes[] = 3;
        }

        $userTypes = array_unique($userTypes);

        return empty($userTypes) ? [0] : $userTypes;
    }

    public function sallariesByMonth()
    {
        return $this->hasMany(UserSallary::class, 'user_id')
            ->where('user_agency_id', $this->agency_id);
    }

    public function latestJoin()
    {
        return $this->hasOne(UsersJoinedAgency::class, 'user_id')
            ->where('agency_id', $this->agency_id)
            ->latestOfMany('join_date');
    }

    public function lastSallary()
    {
        $join = $this->latestJoin()->first();

        return $this->hasOne(UserSallary::class, 'user_id')
            ->where(function ($query) use ($join) {
                if ($join) {
                    $start = $join->join_date;
                    $end = $join->leave_date ?? now()->endOfMonth();
                    $query->where('user_agency_id', $this->agency_id)
                        ->whereBetween('created_at', [$start, $end]);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->latestOfMany();
    }

    public function type16Packs()
    {
        return $this->hasMany(Pack::class, 'user_id')
            ->where('type', 16)
            ->where('is_used', 1)
            ->where(function ($q) {
                $q->where('expire', 0)
                    ->orWhere('expire', '>=', now()->timestamp);
            });
    }

    protected static function boot()
    {
        parent::boot();

        self::saving(function ($model) {
            if (request()->has('is_frozen')) {

                if ($model->agency) {
                    $model->agency->update(['is_frozen' => request()->is_frozen]);
                } else {
                    Log::warning("User ID {$model->id} does not have an agency.");
                }
                request()->request->remove('is_frozen');
            }
            if (request()->has('charge_agency')) {
                if (request('charge_agency') === 1) {
                    // لو مش موجود، أضيف
                    ChargeAgency::firstOrCreate([
                        'agency_id' => $model->agency_id,
                    ]);
                } else {
                    // لو السويتش = 0، أمسح السطر لو موجود
                    ChargeAgency::where('agency_id', $model->agency_id)->delete();
                }
            }

            $originalProfile = $model->profile;
            $newAvatar = request()->input('photo'); // still okay if tightly coupled
            if ($originalProfile && $newAvatar && $originalProfile->avatar !== $newAvatar) {

                $newCount = $model->profile_count + 1;
                $model->profile_count = $newCount;

                $file = request('photo');
                if ($file instanceof UploadedFile) {

                    $url = Common::uploadProfileUser('profile', $file, $originalProfile->id, $newCount);
                    Storage::delete($model->profile->avatar);
                }
                if ($model->profile) {
                    $model->profile->avatar = $url ?? '';
                }
            } else {
                if ($model->profile) {
                    $model->profile->avatar = $model->profile->avatar;
                }
            }
            unset($model->photo);
            unset($model->original_uuid);
        });

        self::updating(function ($user) {
            // Check if coins increased
            $originalCoins = $user->getOriginal('di');
            $newCoins = $user->di;

            if ($newCoins > $originalCoins) {
                $user->new_gift = true;
            }
            if ($user->agency_id) {
                clearAgencyCache($user->agency_id);
            }

            static::deleted(function ($user) {
                if ($user->agency_id) {
                    clearAgencyCache($user->agency_id);
                }
            });
            // Handle profile.avatar update

        });
    }
    public function blockedUsers()
    {
        return $this->hasMany(BlackList::class, 'user_id');
    }

    public function blockedMe()
    {
        return $this->hasMany(BlackList::class, 'from_uid');
    }

    public function getProfileFrame(): Ware | null
    {
        return $this->packs?->where('type', 28)->where('is_used', 1)->first()?->ware;
    }
}
