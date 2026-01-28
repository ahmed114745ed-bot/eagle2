<?php

namespace Utd\Room\Entities;

use App\Models\User;
use App\Models\Family;
use App\Models\AllGame;
use App\Models\GiftLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Modules\LuckyBox\Traits\RoomBoxes;

/**
 * @method static withoutAppends()
 */
class Room extends Model
{
    use RoomBoxes;
    /**
     * To enable and disable observer saving and updating methods
     */
    public $enableSaving = true;

    public static $withoutAppends = false;

    protected $guarded = [];

    protected $appends = ['lang', 'country'];

    protected $casts = [
        'is_pk' => 'boolean',
        'is_comment_closed' => 'boolean',
        'is_live' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uid');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uid', 'id');
    }

    public function roomSalary(): HasMany
    {
        return $this->hasMany(RoomSalary::class, 'room_id');
    }

    public function getSalaryAttribute()
    {
        $salary = RoomSalary::query()
            ->where('room_id', $this->id)
            ->where('is_paid', 0)
            ->sum(DB::raw('salary - cut_amount'));

        return $salary;
    }

    public function enterRoom(): HasOne
    {
        return $this->hasOne(EnteredRoom::class, 'rid');
    }

    public function scopeWithoutAppends(Builder $query): Builder
    {
        self::$withoutAppends = true;

        return $query;
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(AllGame::class, 'game_id');
    }

    public function microphones(): HasMany
    {
        return $this->hasMany(RoomMicrophone::class);
    }

    public function getLangAttribute()
    {
        if (self::$withoutAppends) {
            return null;
        }

        if (!$this->relationLoaded('owner')) {
            return null;
        }

        if (!$this->owner || !$this->owner->relationLoaded('country')) {
            return null;
        }

        return $this->owner->country->language ?? null;
    }

    public function getCountryAttribute()
    {
        if (self::$withoutAppends) {
            return null;
        }

        if (!$this->relationLoaded('owner')) {
            return null;
        }

        $owner = $this->owner;

        if (!$owner || !$owner->relationLoaded('country')) {
            return null;
        }

        return $owner->country;
    }

    public function myClass(): BelongsTo
    {
        return $this->belongsTo(RoomCategory::class, 'room_class')->select('name', 'img');
    }

    public function myType(): BelongsTo
    {
        return $this->belongsTo(RoomCategory::class, 'room_type')->select('name', 'img');
    }

    public function pks(): HasMany
    {
        return $this->hasMany(Pk::class, 'room_id', 'id');
    }

    public function gifts(): HasMany
    {
        return $this->hasMany(GiftLog::class, 'roomowner_id', 'uid');
    }

    public function topUserGift(): HasOne
    {
        return $this->hasOne(GiftLog::class, 'roomowner_id', 'id')
            ->selectRaw('SUM(giftPrice) as exp, sender_id, roomowner_id')
            ->whereHas('sender')
            ->groupBy('sender_id', 'roomowner_id')
            ->orderByDesc('exp');
    }

    public function roomCategory(): BelongsTo
    {
        return $this->belongsTo(RoomCategory::class, 'room_type');
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class, 'uid', 'user_id');
    }

    public function lastPk(): HasOne
    {
        return $this->hasOne(Pk::class, 'room_id', 'id')
            ->where('status', 1)
            ->where('end_at', '>=', now())
            ->orderByDesc('id');
    }

    public function boxUse(): HasOne
    {
        return $this->hasOne(\Modules\LuckyBox\Entities\BoxUse::class, 'room_uid', 'uid');
    }

    public function getSessionStringAttribute(): string
    {
        return numToString($this->session);
    }

    public function getMicrophoneAttribute(): string
    {
        $microphones = $this->relationLoaded('microphones')
            ? $this->microphones
            : collect();

        return $microphones
            ->sortBy('position')
            ->map(function ($mic) {
                $userId = $mic->user_id ?? 0;
                $status = $mic->status ?? 0;

                return $userId > 0
                    ? "{$userId}#{$status}"
                    : (string) $status;
            })
            ->implode(',');
    }

    public function getMicrophoneOnlyUsersAttribute(): string
    {
        return $this->attributes['microphone'] ?? '';
    }

    public function getAllMicrophoneAttribute(): string
    {
        return $this->attributes['microphone'] ?? '';
    }

    public function getMainMicrophoneAttribute(): string
    {
        $microphoneWithOldSeat = array_key_exists('microphone', $this->attributes) 
            ? $this->attributes['microphone'] 
            : '';
        $microphoneWithOldSeat = explode(',', $microphoneWithOldSeat);
        $array = array_map(function ($id) {
            $arr = collect(explode('#', $id));
            $value = $arr->last();

            return $value > 0 ? 0 : $value;
        }, $microphoneWithOldSeat);

        return implode(',', $array);
    }

    public function getCountRoomSocketAttribute(): int
    {
        $ids = explode(',', $this->room_visitor);

        return count($ids);
    }

    public function getCountRoomSocketV2Attribute(): int
    {
        $validVisitors = $this->roomVisitors;

        return $validVisitors->count();
    }

    public function roomVisitors(): HasMany
    {
        return $this->hasMany(RoomVisitor::class, 'room_id');
    }

    public function roomVisitorUsers(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            RoomVisitor::class,
            'room_visitors.room_id',
            'id',
            'id',
            'room_visitors.user_id'
        );
    }

    public function getRoomVisitorAttribute(): string
    {
        $usersIds = $this->roomVisitors->pluck('user_id')->toArray();

        return count($usersIds) > 0 ? implode(',', $usersIds) : '';
    }

    public function getRoomVisitorNewAttribute(): string
    {
        return $this->visitor_ids ?? '';
    }

    public function topUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'top_user_id');
    }

    public function backgroundImage()
    {
        return $this->hasOneThrough(
            RequestBackgroundImage::class,
            User::class,
            'id',
            'owner_room_id',
            'uid',
            'id'
        )->where('request_background_images.status', 1)->where(function ($q) {
            $q->where('expair', '>=', now()->timestamp)
                ->orWhere('expair', 0);
        })->orderByDesc('id');
    }

    public function getVisitorsImages()
    {
        $visitors = $this->roomVisitorUsers;

        return $visitors->pluck('profile.avatar');
    }

    public function background(): BelongsTo
    {
        return $this->belongsTo(Background::class, 'room_background');
    }

    public function getFinalRoomImageAttribute()
    {
        if ($this->is_pk_custom && $this->mode === 3) {
            return defined('PK_IMAGE') ? PK_IMAGE : null;
        }
        if ($this->mode === 8) {
            return defined('BaCKGROUND_IMAGE_MODE_8') ? BaCKGROUND_IMAGE_MODE_8 : null;
        }

        return $this->backgroundImage?->img
            ?? $this->background?->img
            ?? $this->defaultBackground?->img
            ?? request()->default_background;
    }

    public function defaultBackground(): HasOne
    {
        return $this->hasOne(Background::class, 'id')->where('enable', 1)->orderBy('id');
    }

    public function getModeAttribute($value): int
    {
        return $value === 0 ? 3 : $value;
    }

    public function scopeWithoutVisitorsAndActiveMic($query)
    {
        return $query->whereDoesntHave('roomVisitors')
            ->where('microphone', '!=', '0,0,0,0,0,0,0,0,0,0');
    }

    public function bans(): HasMany
    {
        return $this->hasMany(BanRoom::class);
    }

    protected function getAdminsAttribute(): array
    {
        return explode(',', $this->room_admin);
    }

    public function admins(): HasMany
    {
        return $this->hasMany(User::class, 'id', 'room_admin');
    }

    protected static $microphoneCache = [];

    public static function cacheMicrophoneUsers($ids)
    {
        if (empty($ids)) return collect();

        $missingIds = array_diff($ids, array_keys(self::$microphoneCache));

        if (!empty($missingIds)) {
            $users = User::whereIn('id', $missingIds)
                ->with('profile:id,user_id,avatar')
                ->get()
                ->keyBy('id');

            foreach ($users as $id => $user) {
                self::$microphoneCache[$id] = $user;
            }
        }

        return collect(self::$microphoneCache)->only($ids);
    }

    public function scopeAudio(Builder $query): Builder
    {
        return $query->where('rooms.type', 'audio');
    }

    public function admins_v2()
    {
        return User::whereIn('id', explode(',', $this->room_admin ?? ''))
            ->get();
    }

    public function getTotalAdminsAttribute(): int
    {
        return (int) $this->max_admin + (int) $this->additional_admin;
    }
}
