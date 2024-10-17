<?php
namespace Modules\CP\Repositories;

use App\Models\Cp;
use App\Models\CpRelation;
use App\Models\GiftLog;
use App\Models\Ware;
use Illuminate\Support\Facades\DB;
use Modules\CP\Entities\CpRelation as EntitiesCpRelation;
use Modules\CP\Entities\UserRelationAvilable as EntitiesUserRelationAvilable;

class CpRepository
{
    public function getCpRelationById($id)
    {
        return EntitiesCpRelation::find($id);
    }

    public function getCpCount($userId)
    {
        return Cp::where(function ($query) use ($userId) {
            $query->where("user_one_id", $userId)
                  ->orWhere("user_two_id", $userId);
        })
        ->whereIn("status", [0, 1, 4])
        ->count();
    }

    public function checkExistingCp($userId, $otherUserId)
    {
        return Cp::where(function ($query) use ($userId, $otherUserId) {
            $query->where("user_one_id", $userId)
                  ->where("user_two_id", $otherUserId)
                  ->orWhere(function ($query) use ($userId, $otherUserId) {
                      $query->where("user_two_id", $userId)
                            ->where("user_one_id", $otherUserId);
                  });
        })
        ->whereIn("status", [0, 1, 4])
        ->first();
    }

    public function getUserRelationAvailable($userId, $cpRelationId)
    {
        return EntitiesUserRelationAvilable::where(["user_id" => $userId, "cp_relation_id" => $cpRelationId])
                                   ->where("count", ">", 0)
                                   ->first();
    }

    public function decrementUserRelationCount($relation)
    {
        $relation->count -= 1;
        $relation->save();
    }

    public function createCp($data)
    {
        return Cp::create($data);
    }

    public function getRequestsForUser($userId)
    {
        return Cp::where("user_two_id", $userId)
                 ->where("status", 0)
                 ->get();
    }

    public function findCpById($cpId)
    {
        return Cp::find($cpId);
    }

    public function updateCpStatus(Cp $cp, $status)
    {
        $cp->status = $status;
        return $cp->save();
    }

    public function updateOrCreateUserRelation($userId, $cpRelationId)
    {
        return EntitiesUserRelationAvilable::updateOrCreate(
            [
                "user_id" => $userId,
                "cp_relation_id" => $cpRelationId,
            ],
            [
                "count" => DB::raw('count + 1'),
            ]
        );
    }

    public function getCpRanking($relationType, $type)
    {
        return GiftLog::selectRaw('cp_id, SUM(giftNum * giftPrice) as total_gifts')
            ->whereNotNull("cp_id")
            ->with(['cp' => function ($query) use ($relationType) {
                $query->select('id', 'di', 'level_id', 'user_one_id', 'user_two_id')
                      ->where('cp_relation_id', $relationType);
            }])
            ->whereHas("cp", function ($q) use ($relationType) {
                $q->where('cp_relation_id', $relationType);
            })
            ->when($type, function ($query) use ($type) {
                switch ($type) {
                    case 1:
                        return $query->whereDay('created_at', now()->day);
                    case 2:
                        return $query->whereRaw('WEEK(created_at) = ?', [now()->week]);
                    case 3:
                        return $query->whereMonth('created_at', now()->month);
                }
            })
            ->groupBy('cp_id')
            ->orderByDesc('total_gifts')
            ->take(20)
            ->get();
    }

    public function getCpList($userId)
    {
        return Cp::where(function ($query) use ($userId) {
                $query->where("user_one_id", $userId)
                      ->orWhere(function ($query) use ($userId) {
                          $query->where("user_two_id", $userId);
                      });
            })
            ->whereIn("status", [0, 1, 4])
            ->get();
    }

    public function findWare($wareId)
    {
        return Ware::find($wareId);
    }

    public function getUserCpProfiles($userId, $statuses)
    {
        return Cp::with('relation:id,title')
            ->where(function ($query) use ($userId) {
                $query->where('user_one_id', $userId)
                      ->orWhere('user_two_id', $userId);
            })
            ->whereIn('status', $statuses)
            ->orderByDesc('di');
    }
}
