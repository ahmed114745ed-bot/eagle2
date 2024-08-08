<?php

namespace App\Http\Services;

use App\Http\Resources\Api\V1\UserRelationsResource;
use App\Models\Follow;
use App\Models\User;
use App\Models\Vip;
use Illuminate\Database\Query\JoinClause;

class ProfileRelationsService
{

    public function getData(User $user, $type = 1)
    {
        $userId = $user->id;

        if ($type == 1){
            $data = Follow::query()->where('user_id' , $userId)->whereHas('followed')->with('followed', function ($query) {
                $query->with([
                                 'room' => function ($query) {
                                     return $query->withoutAppends()->select(['id', 'room_pass', 'uid']);
                                 }, 'followPacks', 'profile', 'ware', 'UserVip'
                             ]);
            })->orderByDesc('id')->paginate(15);
            $collect = collect($data->items());
            $users    = $collect->pluck('followed');
            // dd($users);


        }elseif ($type == 2){
            $data = Follow::query()->whereHas('follower')->where('followed_user_id' , $userId)->whereHas('follower')->with('follower', function ($query) {
                $query->with([
                                 'room' => function ($query) {
                                     return $query->withoutAppends()->select(['id', 'room_pass', 'uid']);
                                 }, 'followPacks', 'profile', 'ware', 'UserVip'
                             ]);
            })->orderByDesc('id')->paginate(15);
            $collect = collect($data->items());
            $users    = $collect->pluck('follower');
        } elseif ($type == 3) {
            $data = Follow::query()->whereHas('followed')->whereHas('follower')->join('follows as f1', function (JoinClause $join){
                $join->on('follows.user_id', '=', 'f1.followed_user_id')
                     ->on('f1.user_id', '=','follows.followed_user_id');
            })->where('follows.user_id', $userId)->with('follower', function ($query) {
                $query->with([
                                 'room' => function ($query) {
                                     return $query->withoutAppends()->select(['id', 'room_pass', 'uid']);
                                 }, 'followPacks', 'profile', 'ware', 'UserVip'
                             ]);
            })->orderByDesc('follows.id')->paginate(15);

            $collect = collect($data->items());
            $users    = $collect->pluck('follower');
        }else{
            $users = collect([]);
        }


        [$userFollowers, $vipsSenderImages, $vipsReceivedImages] = $this->getHelperArrays($user, $users);


        UserRelationsResource::initializeData($vipsReceivedImages, $vipsSenderImages, $userFollowers);

        return UserRelationsResource::collection($users);
    }
    public function getData0(User $user, $type = 1)
    {
        $query = User::withoutAppends()
                     ->with([
                                'room' => function ($query) {
                                    return $query->withoutAppends()->select(['id', 'room_pass', 'uid']);
                                }, 'followPacks', 'profile', 'ware', 'UserVip'
                            ]);

        if ($type != 2) {
            $query->whereHas('followeds', function ($q) use ($user, $type) {
                switch ($type) {
                    case 1:
                    case 3:
                        $q->where('followed_user_id', $user->id)->where('user_id', '!=', $user->id)->orderByDesc('id');
                        break;

                }
            });
        }

        if ($type == 2 || $type == 3) {
            $query->whereHas('followers', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('followed_user_id', '!=', $user->id)->orderByDesc('id');
            });
        }
        $data = $query
            ->selectRaw('id,uuid,name,sender_level,sub_sender_level,received_level,sub_receiver_level,dress_1,online_time')
            ->paginate(15);

        [$userFollowers, $vipsSenderImages, $vipsReceivedImages] = $this->getHelperArrays($user, $data);


        UserRelationsResource::initializeData($vipsReceivedImages, $vipsSenderImages, $userFollowers);

        return UserRelationsResource::collection($data);
    }

    /**
     * @param User $user
     * @param $data
     * @return array
     */
    public function getHelperArrays(User $user, $data): array
    {
//        $userFollowers      = $user->followers->pluck('user_id')->toArray();
        $userFollowers      = Follow::query()->where('user_id',$user->id)->pluck('followed_user_id')->toArray();


        [$vipsSenderImages, $vipsReceivedImages] =
            $this->getLevelsSenderAndReceiver($data);

        return [$userFollowers, $vipsSenderImages, $vipsReceivedImages];
    }

    /**
     * @param $vipsSenderImages
     * @param $vipsReceivedImages
     * @return array
     */
    public function getLevelsSenderAndReceiver($data): array
    {
        $vipsSenderImages   = $data->pluck('total_sender_level');
        $vipsReceivedImages = $data->pluck('total_received_level');

        $vipsSenderImages   = $this->getLevel($vipsSenderImages, 2);
        $vipsReceivedImages = $this->getLevel($vipsReceivedImages);
        return [$vipsSenderImages, $vipsReceivedImages];
    }

    /**
     * @param $vipsSenderImages
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getLevel($levelsList, $type = 1)
    {
        return Vip::query()->whereIn('level', $levelsList)
                  ->where('type', $type)->select('img', 'level')->get();
    }

}
