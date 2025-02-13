<?php

namespace Modules\Moment\Http\Repositories;

use App\Models\Follow;
use Illuminate\Support\Facades\DB;
use Modules\Moment\Entities\Moment;
use Modules\Moment\Entities\MomentLikes;
use Modules\Moment\Entities\ReportMoment;

class MomentRepository
{

    public function findReportMomentById($id)
    {
        return ReportMoment::find($id);
    }

    public function deleteReportMoment(ReportMoment $reportMoment)
    {
        return $reportMoment->delete();
    }

    public function findMomentById($id)
    {
        return Moment::find($id);
    }

    public function deleteMoment(Moment $moment)
    {
        return $moment->delete();
    }

    public function getMomentById($id, $userId)
    {
        return Moment::where('id', $id)
            ->likeExists($userId)
            ->with('user')
            ->withCount(['likes', 'comments'])
            ->with(['gifts' => function ($query) {
                $query->select(DB::raw('sum(moment_user_gifts.num) as gifts_count'))
                    ->groupBy('moment_user_gifts.moment_id', 'moment_user_gifts.gift_id');
            }])
            ->first();
    }

    public function createMoment(array $data)
    {
        return Moment::create($data);
    }

    public function getUserMoments($userId, $page)
    {
        return Moment::where('user_id', $userId)
            ->whereHas('user')
            ->likeExists($userId)
            ->withCount(['likes', 'comments'])
            ->with(['user', 'gifts' => function ($query) {
                $query->select(DB::raw('sum(moment_user_gifts.num) as gifts_count'))
                    ->groupBy('moment_user_gifts.moment_id', 'moment_user_gifts.gift_id');
            }])
            ->orderByRaw('YEAR(created_at) DESC')
            ->orderByRaw('MONTH(created_at) DESC')
            ->when($page == 1, function ($query) {
                $seed = rand(1000, 2000);
                $query->orderBy(DB::raw('RAND(' . $seed . ')'));
            })
            ->paginate(10);
    }

    public function getLikedMoments($userId, $page)
    {
        return MomentLikes::with([
            'moment.user',
            'moment' => function ($query) use ($userId) {
                $query->likeExists($userId)
                    ->withCount(['likes', 'comments'])
                    ->with(['gifts' => function ($query) {
                        $query->select(DB::raw('sum(moment_user_gifts.num) as gifts_count'))
                            ->groupBy('moment_user_gifts.moment_id', 'moment_user_gifts.gift_id');
                    }]);
            }
        ])
            ->where('user_id', $userId)
            ->orderByRaw('YEAR(created_at) DESC')
            ->orderByRaw('MONTH(created_at) DESC')
            ->when($page == 1, function ($query) {
                $seed = rand(1000, 2000);
                $query->orderBy(DB::raw('RAND(' . $seed . ')'));
            })
            ->paginate(10);
    }

    public function getFollowedMoments($userId, $page)
    {
        return Follow::whereHas('moments')
            ->with([
                'moments.user',
                'moments' => function ($query) use ($userId) {
                    $query->likeExists($userId)
                        ->withCount(['likes', 'comments'])
                        ->with(['gifts' => function ($query) {
                            $query->select(DB::raw('sum(moment_user_gifts.num) as gifts_count'))
                                ->groupBy('moment_user_gifts.moment_id', 'moment_user_gifts.gift_id');
                        }])
                        ->orderByRaw("CASE WHEN (SELECT COUNT(*) FROM moment_user_likes WHERE moment_user_likes.moment_id = moment.id AND moment_user_likes.user_id = $userId) > 0 THEN 1 ELSE 0 END ASC");
                }
            ])
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function getAllMoments($userId, $page)
    {
        return Moment::likeExists($userId)
            ->whereHas('user')
            ->withCount(['likes', 'comments'])
            ->with(['user', 'gifts' => function ($query) {
                $query->select(DB::raw('sum(moment_user_gifts.num) as gifts_count'))
                    ->groupBy('moment_user_gifts.moment_id', 'moment_user_gifts.gift_id');
            }])
            ->orderByRaw("CASE WHEN (SELECT COUNT(*) FROM moment_user_likes WHERE moment_user_likes.moment_id = moment.id AND moment_user_likes.user_id = $userId) > 0 THEN 1 ELSE 0 END ASC")
            ->when($page == 1, function ($query) {
                $seed = rand(1000, 2000);
                $query->orderBy(DB::raw('RAND(' . $seed . ')'));
            })->paginate(10);
    }


    public function getNewMoments($userId)
    {
        return Moment::likeExists($userId)
            ->whereHas('user')
            ->withCount(['likes', 'comments'])
            ->with(['user', 'gifts' => function ($query) {
                $query->select(DB::raw('sum(moment_user_gifts.num) as gifts_count'))
                    ->groupBy('moment_user_gifts.moment_id', 'moment_user_gifts.gift_id');
            }])
            ->orderByRaw("CASE WHEN (SELECT COUNT(*) FROM moment_user_likes WHERE moment_user_likes.moment_id = moment.id AND moment_user_likes.user_id = $userId) > 0 THEN 1 ELSE 0 END ASC")
            ->take(10)->orderByDesc('id')->get();
    }


    public function momentUserFollow($userId)
    {
        $followId = Follow::where('user_id', $userId)->pluck('followed_user_id');
        return Moment::likeExists($userId)->whereIn('user_id', $followId)
            ->whereHas('user')
            ->withCount(['likes', 'comments'])
            ->with(['user', 'gifts' => function ($query) {
                $query->select(DB::raw('sum(moment_user_gifts.num) as gifts_count'))
                    ->groupBy('moment_user_gifts.moment_id', 'moment_user_gifts.gift_id');
            }])
            ->orderByRaw("CASE WHEN (SELECT COUNT(*) FROM moment_user_likes WHERE moment_user_likes.moment_id = moment.id AND moment_user_likes.user_id = $userId) > 0 THEN 1 ELSE 0 END ASC")->paginate(10);
    }
}
