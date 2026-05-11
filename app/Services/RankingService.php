<?php

namespace App\Services;

use App\Models\Pk;;
use App\Models\User;
use App\Helpers\Common;
use App\Helpers\LogHelper;
use App\helper\RankingHelper;

use Illuminate\Log\LogManager;
use App\Helpers\UserPackHelper;
use App\Helpers\UserLevelHelper;
use Illuminate\Support\Facades\Cache;

use Illuminate\Pagination\Paginator;
use App\Http\Resources\TopUserResource;
use App\Repositories\RankingRepository;
use App\Http\Resources\Api\V1\RoomResource;
use App\Http\Resources\GameRankingResource;

use App\Tik\Repositories\GiftLogRepository;
use Modules\CP\Transformers\RankingResource;
use App\Http\Resources\RankingUserV2Resource;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\CP\Transformers\TopRankingResource;
use App\Tik\Repositories\CoinGameUserRepository;
use App\Http\Resources\Api\V1\MangerTypeResource;
use App\Http\Resources\Api\V1\RoomRankingResource;
use App\Http\Resources\Api\V1\UserRankingCollection;
use App\Http\Resources\Api\V1\UsersRankingCollection;
use App\Http\Resources\RankingGameCollectionResource;
use Modules\Achievement\Http\Services\UserAchievementService;
use Modules\Achievement\Transformers\UserAchievementLevelsResource;
use Modules\CP\Repositories\CpRepository as RepositoriesCpRepository;

class RankingService
{
    protected $rankingRepo, $cpRepository;


    public function __construct(
        RankingRepository $rankingRepo,
        private readonly GiftLogRepository $GiftLogRepository,
        private readonly CoinGameUserRepository $coinGameUserRepository,
        public UserAchievementService $achievementService,
        RepositoriesCpRepository $cpRepository
    ) {
        $this->cpRepository = $cpRepository;
        $this->rankingRepo = $rankingRepo;
    }


    public function getRoomRanking($roomOwnerId, $type, $limit, $userId)
    {
        $data = $this->GiftLogRepository->getRoomRankingData($roomOwnerId, $type, $limit);
        $position = 0;
        $currentUserRank = 0;

        foreach ($data as $index => $item) {
            $user = $item->sender;

            if (!$user) {
                $this->setDefaultUserData($item);
                continue;
            }

            $position++;
            $this->populateUserData($item, $user);

            if ($user->id == $userId) {
                $currentUserRank = $position;
            }

            unset($item->sender); // Remove unnecessary loaded relation
        }

        unset($item);

        return $data->toArray();
    }

    private function setDefaultUserData(&$item)
    {
        $item->user_id  = 0;
        $item->name     = '';
        $item->avatar   = '';
        $item->frame    = '';
        $item->frame_id = 0;
        $item->type_user = 0;
        $item->manger_type = null;
        $item->vip = null;
        $item->has_color_name = null;
        $item->data_achivement = null;
    }

    private function populateUserData(&$item, $user)
    {
        $item->user_id  = $user->id;
        $item->name     = $user->name;
        $item->avatar   = optional($user->profile)->avatar ?? '';
        $item->frame    = Common::getUserDress($user->id, $user->dress_1, 4, 'img2', true);
        $item->frame_id = $user->dress_1 ?? 0;
        $item->type_user = intval($user->type_user ?? 0);
        $item->manger_type = $user->mangerType ? new MangerTypeResource($user->mangerType) : null;
        $item->vip = Common::ovip_center($user);
        $item->has_color_name = Common::hasInPack($user->id, 18, true);
        $item->data_achivement = UserAchievementLevelsResource::collection(
            $this->achievementService->getUserAchievement($user)
        );
    }

    public function getRanking22(int $class, int $type, $user, int $limit)
    {
        switch ($class) {
            case 4:
                return $this->handleLuckyGiftRanking($type, $limit, $user, $class);

            case 6:
                return $this->handleGameCoinRanking($type, $limit, $user, $class);

            case 5:
                return $this->handleAgencyRanking($type, $limit);

            default:
                return $this->handleUserRanking($class, $type, $limit, $user);
        }
    }

    protected function handleLuckyGiftRanking(int $type, int $limit, $user, int $class)
    {
        $data = $this->rankingRepo->getUserLuckyGifts($type, $limit);
        $this->transformData($data, $class, 'user_id', 'user');

        return $this->prepareResponse($data, $user, $type, 'user_id', $user->id, $class, $limit);
    }

    protected function handleGameCoinRanking(int $type, int $limit, $user, int $class)
    {
        $data = $this->rankingRepo->getUserGameCoins($type, $limit);
        return new RankingGameCollectionResource($data);
    }

    protected function handleAgencyRanking(int $type, int $limit)
    {
        $types = [
            1 => 'daily',
            2 => 'weekly',
            3 => 'monthly'
        ];

        return $this->rankingRepo->getAgencyRanking('agency', $types[$type], $limit,$type);
    }



    protected function handleUserRanking(int $class, int $type, int $limit, $user)
    {
        [$keywords, $rel] = $this->getClassKeywordsAndRelation($class);

        $types = [
            1 => 'daily',
            2 => 'weekly',
            3 => 'monthly',
        ];

        $data = $this->rankingRepo->getUserRanking($rel, $types[$type], $limit,$type);
      
      if($rel == 'roomId') return RoomRankingResource::collection($data);
        $userExp = $data->firstWhere('ranker_id', $user->id)?->total_gifts ?? 0;

        $key = $types[$type] . '_' . $class;

        $this->transformData3($data, $class, $keywords, $rel);


        $currentUser = new RankingUserV2Resource([
            'user'    => $user,
            'data'    => $data,
            'userExp' => $userExp,
            'key'     => $key,
            'class'   => $class,
        ]);

        $topUsers = $data->take(3);
        $topResources = $topUsers->map(fn($item) => new TopUserResource($item));


        $otherUsers = $data->slice(3);

        $perPage = request('per_page', 10);
        $currentPage = LengthAwarePaginator::resolveCurrentPage() ?: 1;
        $currentItems = $otherUsers->forPage($currentPage, $perPage);

        $paginatedOther = new LengthAwarePaginator(
            $currentItems->values(),
            $otherUsers->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        $otherResources = collect($paginatedOther->items())->map(fn($item) => new TopUserResource($item));

        return [
            'user'  => $currentUser,
            'top'   => $topResources,
            'other' => $otherResources,
            'others_pagination' => [
                'total'        => $paginatedOther->total(),
                'per_page'     => $paginatedOther->perPage(),
                'current_page' => $paginatedOther->currentPage(),
                'last_page'    => $paginatedOther->lastPage(),
                'next_page'    => $paginatedOther->nextPageUrl(),
                'prev_page'    => $paginatedOther->previousPageUrl(),
            ]
        ];
    }

    protected function prepareResponse3($data, User $user, $type, $key, $userId, $class, $limit, $userExp = null)
    {
        $achievement_images = [];

        $kong['user_id']    = 0;
        $kong['uuid']       = '';
        $kong['exp']        = '0';
        $kong['exp_int']        = 0;
        $kong['remaining']        = '0';
        $kong['remaining_int']        = 0;
        $kong['name']       = '';
        $kong['avatar']     = '';
        $kong['frame']      = '';
        $kong['frame_id']   = 0;
        $kong['sender_img'] = '';
        $kong['reseverimg'] = '';
        $kong['vip_level']  =  0;
        $kong['sender_level'] = 0;
        $kong['reciver_level'] = 0;

        $kong['vip_level_img'] = '';
        $kong['sender_level_img'] = '';
        $kong['reciver_level_img'] = '';
        $kong['age'] = 0;

        $kong['type_user'] = 0;
        $kong['manger_type'] = null;
        $kong['achievement_images'] = [];
        $kong['color_name'] = '';

        if ($data->count() > 0) {
            $data[0] = $data[0] ?? $kong;
            $data[1] = $data[1] ?? $kong;
            $data[2] = $data[2] ?? $kong;
        }

        $user->sort = $this->getUserSortValue($data, $userId);
        $user->user_id = $user->id;

        $arr['user'] = $user->only('user_id', 'uuid', 'exp', 'name', 'avatar', 'frame', 'frame_id', 'manger_type_id', 'age');

        $userData = $data->where($key, $user->id)->first();

        $arr['user']['exp'] = ($userExp != null) ? (@$userExp->total_gifts ?? '0') : (@$userData->total_gifts ?? '0');
        $arr['user']['sender_img'] = UserLevelHelper::getSenderImage($user);
        $arr['user']['vip_level']  = $user->UserVip?->level;
        $arr['user']['sender_level']  = $user->total_sender_level ?? '';
        $arr['user']['reciver_level']  = $user->total_received_level ?? '';
        $arr['user']['vip_level_img']  = UserPackHelper::getVipIcon($user);
        $arr['user']['sender_level_img']  = UserLevelHelper::getSenderImage($user);
        $arr['user']['reciver_level_img']  = UserLevelHelper::getReceiverImage($user);
        $arr['user']['type_user'] =  intval(@$user->type_user) ?: 0;
        $arr['user']['country'] =  @$user->country;
        $arr['user']['manger_type'] = !$user->mangerType ? null : new MangerTypeResource(@$user->mangerType);
        $arr['user']['age'] = @$user->profile?->age ?? '';
        $arr['user']['color_name'] = UserPackHelper::getColorName($user);
        $arr['user']['achievement_images'] = $achievement_images;

        $dataArray = $data->toArray();
        $countData = count($dataArray);

        $arr['top'] = $countData < 4 ? $dataArray : array_slice($dataArray, 0, 3);

        $otherData = $countData < 4 ? [] : array_slice($dataArray, 3);

        $perPage = request('per_page', 10);
        $currentPage = LengthAwarePaginator::resolveCurrentPage() ?: 1;

        $currentItems = array_slice($otherData, ($currentPage - 1) * $perPage, $perPage);

        $paginatedOther = new LengthAwarePaginator(
            $currentItems,
            count($otherData),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );

        $arr['other'] = $paginatedOther->items();
        $arr['others_pagination'] = [
            'total'        => $paginatedOther->total(),
            'per_page'     => $paginatedOther->perPage(),
            'current_page' => $paginatedOther->currentPage(),
            'last_page'    => $paginatedOther->lastPage(),
            'next_page'    => $paginatedOther->nextPageUrl(),
            'prev_page'    => $paginatedOther->previousPageUrl(),
        ];
        //        $arr['other'] = $countData < 4 ? [] : array_slice($dataArray, 3);

        return $arr;
    }



    public function getRanking66($class, $type, $user, $limit)
    {
        if ($class == 4) {
            $data = $this->rankingRepo->getUserLuckyGifts($type, $limit);
            $this->transformData($data, $class, 'user_id', 'user');
            return $this->prepareResponse($data, $user, $type, 'user_id', $user->id, $class, $limit);
        } elseif ($class == 6) {
            $data = $this->rankingRepo->getUserGameCoins($type, $limit);
            return $this->prepareResponse2($data, $user, $type, $user->id, $class);
            return \App\Http\Resources\RankingResource::collection($data);
        }

        [$keywords, $rel] = $this->getClassKeywordsAndRelation($class);
        $types = [
            1 => 'daily',
            2 => 'weekly',
            3 => 'monthly'
        ];
        if ($class == 5) {
            return $this->rankingRepo->getAgencyRanking($rel, $types[$type], $limit);
        }
        $data = $this->rankingRepo->getUserRanking($rel, $types[$type], $limit);

        return new UsersRankingCollection($data, $user, $keywords);
    }

    protected function transformData3(&$data, $class, $key, $relation)
    {

        $data = $data->values()->map(function ($item, $key) use ($data) {
            if ($key === 0) {
                $item->exp_diff = 0;
            } else {
                $item->exp_diff = $data[$key - 1]->total_gifts - $item->total_gifts  + 1;
            }
            return $item;
        });


        $data = $data->map(function ($v) use ($key, $class, $relation) {
            $achievement_images = [];
            $user = $v->ranker;

            if ($user == null) {
                return null;
            }

            $color_name = UserPackHelper::getColorName($user);

            if ($user->medals) {
                foreach ($user->medals as $medal) {
                    $achievementData = [
                        'image' => @$medal->custom_image ?? @$medal->achievementLevel->valid_image,
                        'title' => @$medal->achievementLevel?->achievement?->name ?? 'Reward',
                        'created_at' => @$medal->created_at,
                    ];
                    $achievement_images[] = $achievementData;
                }
            }

            $v->user_id = $user->id;
            $v->color_name = $color_name;

            $value = $v->total_gifts;
            $v->exp = numToString(ceil((float)$value));
            $v->exp_int = ceil($value);

            $value2 = $v->exp_diff;
            $v->remaining = numToString(ceil($v->exp_diff));
            $v->remaining_int = ceil($value2);

            $v->name = $class == 3 ? (@$user->ownerRoom?->room_name ?? '') : $user->name;
            $v->avatar = $class == 3 ? (@$user->ownerRoom?->room_cover ?? '') : $user->profile?->avatar;
            $v->frame = UserPackHelper::getFrameImage($user);
            $v->frame_id = UserPackHelper::getFrameId($user);

            $v->type_user =  intval(@$user->type_user) ?: 0;
            $v->manger_type =  !$user->mangerType ? null : new MangerTypeResource(@$user->mangerType);

            $v->vip_level = @$user->UserVip->level ?? 0;
            $v->sender_level = @$user->total_sender_level;
            $v->reciver_level = @$user->total_received_level;


            $v->vip_level_img = @$user->UserVip?->OVip?->img ?? '';
            $v->sender_level_img = UserLevelHelper::getSenderImage($user);
            $v->reciver_level_img = UserLevelHelper::getReceiverImage($user);

            $v->country = @$user->country;
            $v->age = @$user->profile->age ?? '';
            $v->achievement_images = $achievement_images;
            $v->room = $class == 3 ? $this->roomData(@$user->ownerRoom) : null;
            //unset($v->ranker);
            return $v;
        })->reject(function ($v) {
            return $v == null;
        });
    }

    public function getRankingV2($class, $type, $user, $limit, $room_uid, $sent_to_owner)
    {
        $user->loadMissing('profile', 'medals', 'medals.achievementLevel.achievement', 'UserVip.OVip');
        if ($class == 4) {
            $data = $this->rankingRepo->getUserLuckyGifts($type, $limit);
            $this->transformDataV2($data, $class, 'user_id', 'user');
            return $this->prepareResponseV2($data, $user, $type, 'user_id', $user->id, $class, $limit);
        } elseif ($class == 6) {
            $data = $this->rankingRepo->getUserGameCoinsV2($type, $limit);
            return $this->prepareResponse2($data, $user, $type, $user->id, $class);
            return \App\Http\Resources\RankingResource::collection($data);
        }

        [$keywords, $rel] = $this->getClassKeywordsAndRelation($class);

        $data = $this->rankingRepo->getGiftLogsV2($class, $rel, $type, $limit, $keywords);
        $this->transformDataV2($data, $class, $keywords, $rel);

        return $this->prepareResponseV2($data, $user, $type, $keywords, $user->id, $class, $limit);
    }

    protected function roomData($ownerRoom)
    {
        if (!$ownerRoom) return null;
        $data = [];
        $pks = !is_null($ownerRoom?->id) ? $this->getRoomTwoLastPk($ownerRoom->id) : null;
        $data =  [
            "id" => @$ownerRoom->id ?? 0,
            "owner_uuid" => @@$ownerRoom->owner->uuid ?? 0,
            "room_name" => @$ownerRoom->room_name ?? '',
            "room_cover" => @$ownerRoom->room_cover ?? '',
            "room_background" => @$ownerRoom->final_room_image ?? '',
            "mode" => @$ownerRoom->mode ?? 0,
            'giftPrice' => @$ownerRoom->session_string ?? "0",
            "is_pk"               => (@$pks[0]) && @$pks[0]->end_at >= now() ? @$pks[0]->status : 0,
            "show_pk"             => @$ownerRoom->is_show_pk ?? 0,
            'password_status'     => !(@$ownerRoom->room_pass == ""),
            'type-number'                => @$ownerRoom->room_type ?? 0,
            'type' => @$ownerRoom->myType ?: new \stdClass(),

        ];

        return $data;
    }
    private function getRoomTwoLastPk(int $roomId)
    {
        return Pk::query()
            ->where('room_id', $roomId)
            ->orderByDesc('created_at')
            ->limit(2)
            ->get();
    }

    protected function transformData(&$data, $class, $key, $relation)
    {
        $data = $data->reject(function ($q) {
            return $q->exp == 0;
        });

        $data = $data->values()->map(function ($item, $key) use ($data) {
            if ($key === 0) {
                $item->exp_diff = 0;
            } else {
                $item->exp_diff = $data[$key - 1]->exp - $item->exp  + 1;
            }
            return $item;
        });


        $data = $data->map(function ($v) use ($key, $class, $relation) {
            $achievement_images = [];
            $user = $v->$relation;

            if ($user == null) {
                return null;
            }

            $hasColor = Common::hasInPack($user->id, 18, true);

            $color_name = $hasColor ? common::wareUserVip($user->id, 18, 'color') : null;
            $color_name = is_string($color_name) ? $color_name : '';
            if ($user->medals) {
                foreach ($user->medals as $medal) {
                    if ($medal->achievementLevel) {
                        $achievementData = [
                            'image' => @$medal->achievementLevel->valid_image,
                            'title' => @$medal->achievementLevel?->achievement?->name ?? '',
                            'created_at' => @$medal->created_at,
                        ];
                        $achievement_images[] = $achievementData;
                    }
                }
            }

            $v->user_id = $user->id;
            $v->color_name = $color_name;

            $value = $v->exp;
            $v->exp = numToString(ceil((float)$v->exp));
            $v->exp_int = ceil($value);

            $value2 = $v->exp_diff;
            $v->remaining = numToString(ceil($v->exp_diff));
            $v->remaining_int = ceil($value2);

            $v->name = $class == 3 ? (@$user->ownerRoom?->room_name ?? '') : $user->name;
            $v->avatar = $class == 3 ? (@$user->ownerRoom?->room_cover ?? '') : $user->profile->avatar;
            $v->frame = Common::getUserDress($user->id, $user->dress_1, 4, 'img2', true) ?: Common::getUserDress($user->id, $user->dress_1, 4, 'img1', true);
            $v->frame_id = $user->dress_1;
            $v->type_user =  intval(@$user->type_user) ?: 0;
            $v->manger_type =  !$user->mangerType ? null : new MangerTypeResource(@$user->mangerType);

            $v->vip_level = @$user->UserVip->level ?? 0;
            $v->sender_level = @$user->total_sender_level;
            $v->reciver_level = @$user->total_received_level;

            $total_received_level_img = Common::getImageTotalReceiverOrSender($user->total_received_level);
            $total_sender_level_img = Common::getImageTotalReceiverOrSender($user->total_sender_level);

            $v->vip_level_img = @$user->UserVip?->OVip?->img ?? '';
            $v->sender_level_img = @$total_received_level_img->img ?? '';
            $v->reciver_level_img = @$total_sender_level_img->img ?? '';

            $v->country = @$user->country;
            $v->age = @$user->profile->age ?? 'P';
            $v->achievement_images = $achievement_images;
            $v->room = $class == 3 ? $this->roomData(@$user->ownerRoom) : null;
            unset($v->$relation);
            return $v;
        })->reject(function ($v) {
            return $v == null;
        });
    }

    protected function transformDataV2(&$data, $class, $key, $relation)
    {
        $data = $data->reject(function ($q) {
            return $q->exp == 0;
        });

        $data = $data->values()->map(function ($item, $key) use ($data) {
            if ($key === 0) {
                $item->exp_diff = 0;
            } else {
                $item->exp_diff = $data[$key - 1]->exp - $item->exp  + 1;
            }
            return $item;
        });


        $data = $data->map(function ($v) use ($key, $class, $relation) {
            $achievement_images = [];
            $user = $v->$relation;
            $user->loadMissing('packs', 'profile', 'medals.achievementLevel.achievement', 'UserVip.OVip');

            if ($user == null) {
                return null;
            }

            $hasColor = Common::hasInPackV2($user->packs, 18, true);

            $color_name = $hasColor ? common::wareUserVipV2($user->id, 18, 'color') : null;
            $color_name = is_string($color_name) ? $color_name : '';
            if ($user->medals) {
                foreach ($user->medals as $medal) {
                    if ($medal->achievementLevel) {
                        $achievementData = [
                            'image' => @$medal->achievementLevel->valid_image,
                            'title' => @$medal->achievementLevel?->achievement?->name ?? '',
                            'created_at' => @$medal->created_at,
                        ];
                        $achievement_images[] = $achievementData;
                    }
                }
            }

            $v->user_id = $user->id;
            $v->color_name = $color_name;

            $value = $v->exp;
            $v->exp = numToString(ceil($v->exp));
            $v->exp_int = ceil($value);

            $value2 = $v->exp_diff;
            $v->remaining = numToString(ceil($v->exp_diff));
            $v->remaining_int = ceil($value2);

            $v->name = $class == 3 ? (@$user->ownerRoom?->room_name ?? '') : $user->name;
            $v->avatar = $class == 3 ? (@$user->ownerRoom?->room_cover ?? '') : $user->profile->avatar;
            $v->frame = Common::getUserDressV2($user->packs, $user->dress_1, 4, 'img2', true) ?: Common::getUserDressV2($user->packs, $user->dress_1, 4, 'img1', true);
            $v->frame_id = $user->dress_1;
            $v->type_user =  intval(@$user->type_user) ?: 0;
            $v->manger_type =  !$user->mangerType ? null : new MangerTypeResource(@$user->mangerType);

            $v->vip_level = @$user->UserVip->level ?? 0;
            $v->sender_level = @$user->total_sender_level;
            $v->reciver_level = @$user->total_received_level;

            $total_received_level_img = Common::getImageTotalReceiverOrSender($user->total_received_level);
            $total_sender_level_img = Common::getImageTotalReceiverOrSender($user->total_sender_level);

            $v->vip_level_img = @$user->UserVip?->OVip?->img ?? '';
            $v->sender_level_img = @$total_received_level_img->img ?? '';
            $v->reciver_level_img = @$total_sender_level_img->img ?? '';

            $v->country = @$user->country;
            $v->age = @$user->profile->age ?? 'P';
            $v->achievement_images = $achievement_images;
            $v->room = $class == 3 ? $this->roomData(@$user->ownerRoom) : null;
            unset($v->$relation);
            return $v;
        })->reject(function ($v) {
            return $v == null;
        });
    }



    protected function prepareResponse2($data, $user)
    {
        $emptyItems = collect(array_fill(0, 4, [
            'user_id' => 0,
            'uuid' => '',
            'exp' => '0',
            'exp_int' => 0,
            'remaining' => '0',
            'remaining_int' => 0,
            'name' => '',
            'avatar' => '',
            'frame' => '',
            'frame_id' => 0,
            'sender_img' => '',
            'reseverimg' => '',
            'vip_level' => 0,
            'sender_level' => 0,
            'reciver_level' => 0,
            'vip_level_img' => '',
            'sender_level_img' => '',
            'reciver_level_img' => '',
            'age' => 0,
            'type_user' => 0,
            'manger_type' => null,
            'achievement_images' => [],
            'color_name' => ''
        ]));

        $toArray = $data->toArray();
        $countData = count($data);
        $firstThree = $data->take(3);
        $fromThird = $data->slice(3)->values();
        $arr['user'] = new \stdClass();
        $arr['top'] =   \App\Http\Resources\RankingResource::collection($firstThree);
        $arr['other'] = \App\Http\Resources\RankingResource::collection($fromThird);
        return $arr;
    }


    protected function prepareResponse($data, $user, $type, $key, $userId, $class, $limit, $userExp = null)
    {

        $data->each(function ($item) {
            $hasColor = Common::hasInPack($item->user_id, 18, true) ?? '';
            $color = $hasColor ? Common::wareUserVip($item->user_id, 18, 'color') : null;
            $item->color_name = (is_string($color) && $color !== 'NULL') ? $color : '';
        });

        $achievement_images = [];
        if ($user->medals) {
            foreach ($user->medals as $medal) {
                if ($medal->achievementLevel) {
                    $achievementData = [
                        'image' => @$medal->achievementLevel->valid_image,
                        'title' => @$medal->achievementLevel?->achievement?->name ?? '',
                        'created_at' => @$medal->created_at,
                    ];
                    $achievement_images[] = $achievementData;
                }
            }
        }
        $kong['user_id']    = 0;
        $kong['uuid']       = '';
        $kong['exp']        = '0';
        $kong['exp_int']        = 0;
        $kong['remaining']        = '0';
        $kong['remaining_int']        = 0;
        $kong['name']       = '';
        $kong['avatar']     = '';
        $kong['frame']      = '';
        $kong['frame_id']   = 0;
        $kong['sender_img'] = '';
        $kong['reseverimg'] = '';
        $kong['vip_level']  =  0;
        $kong['sender_level'] = 0;
        $kong['reciver_level'] = 0;

        $kong['vip_level_img'] = '';
        $kong['sender_level_img'] = '';
        $kong['reciver_level_img'] = '';
        $kong['age'] = 0;

        $kong['type_user'] = 0;
        $kong['manger_type'] = null;
        $kong['achievement_images'] = [];
        $kong['color_name'] = '';



        $data[0] = isset($data[0]) ? $data[0] : $kong;
        $data[1] = isset($data[1]) ? $data[1] : $kong;
        $data[2] = isset($data[2]) ? $data[2] : $kong;
        //        if ($limit == 3) return $data;


        $user->sort = $this->getUserSortValue($data, $userId);
        $user->user_id = $user->id;

        $arr['user'] = $user->only('user_id', 'uuid', 'exp', 'name', 'avatar', 'frame', 'frame_id', 'manger_type_id', 'age');

        $sender_img = @$user->getImageReceiverOrSender('sender_id', 2)?->img ?? '';
        $total_received_level_img = Common::getImageTotalReceiverOrSender($user->total_received_level);
        $total_sender_level_img = Common::getImageTotalReceiverOrSender($user->total_sender_level);
        $vip_level  = Common::ovip_center_rank($arr['user']['user_id']);
        $vip_level_img  = Common::ovip_center_rank_img($arr['user']['user_id']);
        $hasColor = Common::hasInPack($user->id, 18, true);

        $color_name = $hasColor ? common::wareUserVip($user->id, 18, 'color') : null;
        $color_name = is_string($color_name) ? $color_name : '';

        // $levels =Common::getSenderAndReceiverLevels($user->id);
        if (gettype($vip_level) != 'integer') {
            $vip_level = 0;
        }

        if (is_object($vip_level_img) && get_class($vip_level_img) === 'stdClass') {
            $vip_level_img = 0;
        }

        $userData = $data->where($key, $user->id)->first();

        $arr['user']['exp'] = ($userExp != null) ? (@$userExp->exp ?? '0') : (@$userData->exp ?? '0');
        $arr['user']['sender_img'] = $sender_img;
        $arr['user']['vip_level']  = $vip_level ?? 0;
        $arr['user']['sender_level']  = $user->total_sender_level ?? '';
        $arr['user']['reciver_level']  = $user->total_received_level ?? '';
        $arr['user']['vip_level_img']  = $vip_level_img == 0 ? "" : $vip_level_img;
        $arr['user']['sender_level_img']  = $total_sender_level_img->img ?? '';
        $arr['user']['reciver_level_img']  = $total_received_level_img->img ?? '';
        $arr['user']['type_user'] =  intval(@$user->type_user) ?: 0;
        $arr['user']['country'] =  @$user->country;
        $arr['user']['manger_type'] = !$user->mangerType ? null : new MangerTypeResource(@$user->mangerType);
        $arr['user']['age'] = @$user->profile?->age ?? '';
        $arr['user']['color_name'] = $color_name ?? '';
        $arr['user']['achievement_images'] = $achievement_images;


        $toArray = $data->toArray();
        $countData = count($data);
        $arr['top'] = $countData < 4 ? $data : array_slice($toArray, 0, 3);
        $arr['other'] = $countData < 4 ? [] : array_slice($toArray, 3);
        return $arr;
    }


    protected function prepareResponseV2($data, $user, $type, $key, $userId, $class, $limit, $userExp = null)
    {
        $data->each(function ($item) use ($user) {
            $hasColor = Common::hasInPackV2($user->packs, 18, true) ?? '';
            $color = $hasColor ? Common::wareUserVipV2($item->user_id, 18, 'color') : null;
            $item->color_name = (is_string($color) && $color !== 'NULL') ? $color : '';
        });

        $achievement_images = [];
        if ($user->medals) {
            foreach ($user->medals as $medal) {
                if ($medal->achievementLevel) {
                    $achievementData = [
                        'image' => @$medal->achievementLevel->valid_image,
                        'title' => @$medal->achievementLevel?->achievement?->name ?? '',
                        'created_at' => @$medal->created_at,
                    ];
                    $achievement_images[] = $achievementData;
                }
            }
        }
        $kong['user_id']    = 0;
        $kong['uuid']       = '';
        $kong['exp']        = '0';
        $kong['exp_int']        = 0;
        $kong['remaining']        = '0';
        $kong['remaining_int']        = 0;
        $kong['name']       = '';
        $kong['avatar']     = '';
        $kong['frame']      = '';
        $kong['frame_id']   = 0;
        $kong['sender_img'] = '';
        $kong['reseverimg'] = '';
        $kong['vip_level']  =  0;
        $kong['sender_level'] = 0;
        $kong['reciver_level'] = 0;

        $kong['vip_level_img'] = '';
        $kong['sender_level_img'] = '';
        $kong['reciver_level_img'] = '';
        $kong['age'] = 0;

        $kong['type_user'] = 0;
        $kong['manger_type'] = null;
        $kong['achievement_images'] = [];
        $kong['color_name'] = '';



        $data[0] = isset($data[0]) ? $data[0] : $kong;
        $data[1] = isset($data[1]) ? $data[1] : $kong;
        $data[2] = isset($data[2]) ? $data[2] : $kong;
        //        if ($limit == 3) return $data;


        $user->sort = $this->getUserSortValue($data, $userId);
        $user->user_id = $user->id;

        $arr['user'] = $user->only('user_id', 'uuid', 'exp', 'name', 'avatar', 'frame', 'frame_id', 'manger_type_id', 'age');

        $sender_img = @$user->getImageReceiverOrSender('sender_id', 2)?->img ?? '';
        $total_received_level_img = Common::getImageTotalReceiverOrSender($user->total_received_level);
        $total_sender_level_img = Common::getImageTotalReceiverOrSender($user->total_sender_level);
        $vip_level  = Common::ovip_center_rank_v2($arr['user']);
        $vip_level_img  = Common::ovip_center_rank_img_v2($arr['user']);
        $hasColor = Common::hasInPackV2($user->packs, 18, true);

        $color_name = $hasColor ? common::wareUserVipV2($user->id, 18, 'color') : null;
        $color_name = is_string($color_name) ? $color_name : '';

        // $levels =Common::getSenderAndReceiverLevels($user->id);
        if (gettype($vip_level) != 'integer') {
            $vip_level = 0;
        }

        if (is_object($vip_level_img) && get_class($vip_level_img) === 'stdClass') {
            $vip_level_img = 0;
        }

        $userData = $data->where($key, $user->id)->first();

        $arr['user']['exp'] = ($userExp != null) ? (@$userExp->exp ?? '0') : (@$userData->exp ?? '0');
        $arr['user']['sender_img'] = $sender_img;
        $arr['user']['vip_level']  = $vip_level ?? 0;
        $arr['user']['sender_level']  = $user->total_sender_level ?? '';
        $arr['user']['reciver_level']  = $user->total_received_level ?? '';
        $arr['user']['vip_level_img']  = $vip_level_img == 0 ? "" : $vip_level_img;
        $arr['user']['sender_level_img']  = $total_sender_level_img->img ?? '';
        $arr['user']['reciver_level_img']  = $total_received_level_img->img ?? '';
        $arr['user']['type_user'] =  intval(@$user->type_user) ?: 0;
        $arr['user']['country'] =  @$user->country;
        $arr['user']['manger_type'] = !$user->mangerType ? null : new MangerTypeResource(@$user->mangerType);
        $arr['user']['age'] = @$user->profile?->age ?? '';
        $arr['user']['color_name'] = $color_name ?? '';
        $arr['user']['achievement_images'] = $achievement_images;


        $toArray = $data->toArray();
        $countData = count($data);
        $arr['top'] = $countData < 4 ? $data : array_slice($toArray, 0, 3);
        $arr['other'] = $countData < 4 ? [] : array_slice($toArray, 3);
        return $arr;
    }

    protected function getClassKeywordsAndRelation($class)
    {
        if ($class == 1) {
            return ['receiver_id', 'receiver'];
        } elseif ($class == 2) {
            return ['sender_id', 'sender'];
        } elseif ($class == 3) {
             return ['roomowner_id', 'roomOwner'];
           // return ['room_id', 'roomId'];
        } elseif ($class == 5) {
            return ['agency_id', 'agency'];
        } else {
            return ['sender_id', 'sender'];
        }
    }

    private function getUserSortValue($data, $user_id)
    {
        $sort = 0;
        foreach ($data as $i => $v) {
            if (isset($v->receiver_id) && $v->receiver_id == $user_id) {
                $sort = $i + 1;
                break;
            }
        }
        return $sort ? (string) $sort : '99+';
    }

    public function topUser()
    {
        $giftLogs = $this->GiftLogRepository->topUser('sender', 'sender_id');
        $giftLogsReceiver = $this->GiftLogRepository->topUser('receiver', 'receiver_id');
        $giftLogsRooms = $this->GiftLogRepository->topUser('roomOwner', 'roomowner_id');
        $img      = [];
        foreach ($giftLogs as $giftLog) {
            $img[] = $giftLog?->sender?->profile?->avatar ?? '';
        }

        $receiverImage = [];
        foreach ($giftLogsReceiver as $giftLog) {
            $receiverImage[] = $giftLog?->receiver?->profile?->avatar ?? '';
        }

        $roomImage = [];
        foreach ($giftLogsRooms as $giftLogsRoom) {
            $roomImage[] = $giftLogsRoom?->roomOwner?->ownerRoom?->room_cover ?? '';
        }

        $data = $this->cpRepository->getCpRankingWithOutRelation(1);
        $cp_top_2 = $data->take(2);
        $topGamer = $this->coinGameUserRepository->topThree();

        return Common::apiResponse(
            1,
            '',
            [
                'sender' => $img,
                'receiver' => $receiverImage,
                'room' => $roomImage,
                'top_cp' => array_values(RankingResource::collection($cp_top_2)->toArray(request())),
                'top_gamer' => GameRankingResource::collection($topGamer),
            ]
        );
    }

    public function topUser2()
    {
        $giftLogs = $this->GiftLogRepository->topUser('sender.profile', 'sender_id');
        $giftLogsReceiver = $this->GiftLogRepository->topUser('receiver.profile', 'receiver_id');
        $giftLogsRooms = $this->GiftLogRepository->topUser('roomOwner', 'roomowner_id');
        $img      = [];
        foreach ($giftLogs as $giftLog) {
            $img[] = @$giftLog->sender->profile->avatar ?? '';
        }

        $receiverImage = [];
        foreach ($giftLogsReceiver as $giftLog) {
            $receiverImage[] = @$giftLog->receiver->profile->avatar ?? '';
        }

        $roomImage = [];
        foreach ($giftLogsRooms as $giftLogsRoom) {
            $roomImage[] = @$giftLogsRoom->roomOwner->ownerRoom->room_cover ?? '';
        }
        return Common::apiResponse(1, '', ['sender' => $img, 'receiver' => $receiverImage, 'room' => $roomImage]);
    }
    public function getRankingOneRoom($class, $type, $user, $limit, $room_id, $sent_to_owner)
    {

        [$keywords, $rel] = $this->getClassKeywordsAndRelation($class);

        $data = $this->rankingRepo->getGiftLogsForRoomOwnerId($class, $rel, $type, $limit, $room_id, $keywords);
        $userExp = $this->rankingRepo->getGiftLogsUserForRoomOwnerId($class, $rel, $type, $user->id, $room_id, $keywords);
        $this->transformData($data, $class, $keywords, $rel);
        return $this->prepareResponse($data, $user, $type, $keywords, $user->id, $class, $limit, $userExp);
    }


    public function getRankingOneRoom2($class, $type, $user, $limit, $room_id, $sent_to_owner)
    {

        [$keywords, $rel] = $this->getClassKeywordsAndRelation($class);

        $data = $this->rankingRepo->getGiftLogsForRoomOwnerId($class, $rel, $type, $limit, $room_id, $keywords);
        $userExp = $this->rankingRepo->getGiftLogsUserForRoomOwnerId($class, $rel, $type, $user->id, $room_id, $keywords);
        $this->transformData2($data, $class, $keywords, $rel);
        return $this->prepareResponse($data, $user, $type, $keywords, $user->id, $class, $limit, $userExp);
    }

    protected function transformData2(&$data, $class, $key, $relation)
    {
        $data = $this->removeZeroExp($data);
        $data = $this->calculateExpDifference($data);
        $data = $this->mapUserData($data, $class, $relation);

        // Re-index values after mapping and rejecting nulls
        $data = $data->values();
    }


    private function removeZeroExp($data)
    {
        return $data->reject(fn($item) => $item->exp == 0);
    }

    private function calculateExpDifference($data)
    {
        return $data->values()->map(function ($item, $index) use ($data) {
            $item->exp_diff = $index === 0 ? 0 : $data[$index - 1]->exp - $item->exp + 1;
            return $item;
        });
    }

    private function mapUserData($data, $class, $relation)
    {
        return $data->map(function ($item) use ($class, $relation) {
            $user = $item->$relation;

            if (!$user) {
                return null; // Skip if user data is missing
            }

            $this->populateUser2Data($item, $user, $class);

            // Safely remove the relation property
            if (property_exists($item, $relation)) {
                unset($item->$relation);
            }
            return $item;
        })->reject(fn($item) => is_null($item));
    }

    private function populateUser2Data(&$item, $user, $class)
    {
        $item->user_id = $user->id;

        // EXP transformations
        $item->exp_int = ceil($item->exp);
        $item->exp = numToString($item->exp_int);

        // Remaining EXP
        $item->remaining_int = ceil($item->exp_diff);
        $item->remaining = numToString($item->remaining_int);

        // Name and Avatar handling
        $item->name = $class == 3 ? optional($user->ownerRoom)->room_name ?? '' : $user->name;
        $item->avatar = $class == 3 ? optional($user->ownerRoom)->room_cover ?? '' : optional($user->profile)->avatar;

        // Frame and User Dress
        $item->frame = Common::getUserDress($user->id, $user->dress_1, 4, 'img2', true)
            ?: Common::getUserDress($user->id, $user->dress_1, 4, 'img1', true);
        $item->frame_id = $user->dress_1;

        // Additional User Info
        $item->type_user = intval(optional($user)->type_user) ?: 0;
        $item->manger_type = $user->mangerType ? new MangerTypeResource($user->mangerType) : null;

        $item->vip_level = optional($user->UserVip)->level ?? 0;
        $item->sender_level = $user->total_sender_level ?? 0;
        $item->reciver_level = $user->total_received_level ?? 0;
        $item->country = $user->country ?? null;
    }

    public function getRanking2($class, $type, $user, $limit, $room_uid, $sent_to_owner)
    {
        if ($class == 4) {
            $data = $this->rankingRepo->getUserLuckyGifts($type, $limit);
            $this->transformData2($data, $class, 'user_id', 'user');
            return $this->prepareResponse($data, $user, $type, 'user_id', $user->id, $class, $limit);
        }

        [$keywords, $rel] = $this->getClassKeywordsAndRelation($class);

        $data = $this->rankingRepo->getGiftLogs($class, $rel, $type, $limit, $keywords);
        $this->transformData2($data, $class, $keywords, $rel);

        return $this->prepareResponse($data, $user, $type, $keywords, $user->id, $class, $limit);
    }

    public function getTodayTopUsers()
    {
        $data = $this->cpRepository->getCpRankingWithOutRelation(1);
        $cp_top_2 = $data->take(3);
        //dd($cp_top_2 -> toArray());
        $topGamer = $this->coinGameUserRepository->topThree();
        return [
            'sender'    => $this->getRankUserAvatars('sender', 'daily'),
            'receiver'  => $this->getRankUserAvatars('receiver', 'daily'),
            'room'      => $this->getRankRoomAvatars('roomOwner', 'daily'),
            'top_cp' => array_values(TopRankingResource::collection($cp_top_2)->toArray(request())),
            'top_gamer' => GameRankingResource::collection($topGamer),
        ];
    }

    /**
     * Extract user avatars from ranking results.
     */
    protected function getRankUserAvatars(string $type, string $rankingType): array
    {
        return $this->rankingRepo
            ->getUserRankingImages($type, $rankingType)
            ->map(fn($item) => optional(@$item?->ranker?->profile)?->avatar)
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Extract room avatars from ranking results.
     */
    protected function getRankRoomAvatars(string $type, string $rankingType): array
    {
        return $this->rankingRepo
            ->getUserRankingImages($type, $rankingType)
            ->map(fn($item) => optional(@$item?->ranker?->ownerRoom)?->room_cover)
            ->filter()
            ->values()
            ->toArray();
    }
}
