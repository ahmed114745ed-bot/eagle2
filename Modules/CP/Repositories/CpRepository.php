<?php

namespace Modules\CP\Repositories;

use App\Models\GiftLog;
use App\Models\Ware;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\CP\Entities\Cp;
use Modules\CP\Entities\CpRelation;
use Modules\CP\Entities\UserRelationAvilable;
use Modules\CP\Enums\CpStatus;

/// todo remove rename import
class CpRepository
{
    public function getCpRelationById($id)
    {
        return CpRelation::find($id);
    }

    public function findStoppedRelationBetweenTwoUsers($userOne, $userTwo, $type)
    {
        return Cp::where(function ($q) use ($userOne, $userTwo) {
            $q->where(function ($q) use ($userOne, $userTwo) {
                $q->where('user_one_id', $userOne)->where('user_two_id', $userTwo);
            })
                ->orWhere(function ($q) use ($userOne, $userTwo) {
                    $q->where('user_one_id', $userTwo)->where('user_two_id', $userOne);
                });
        })
            ->whereHas('cpRelation', function ($q) use ($type) {
                $q->where('type', $type);
            })
            ->where("status", CpStatus::STOPED)
            ->first();
    }

    public function findCpBetweenUsers($userOne, $userTwo, $type = null)
    {
        return Cp::where(function ($q) use ($userOne, $userTwo) {
            $q->where(function ($q) use ($userOne, $userTwo) {
                $q->where('user_one_id', $userOne)->where('user_two_id', $userTwo);
            })
                ->orWhere(function ($q) use ($userOne, $userTwo) {
                    $q->where('user_one_id', $userTwo)->where('user_two_id', $userOne);
                });
        })
            ->whereHas("cpRelation", function ($q) {
                $q->where('type', '!=', 'solution');
            })
            ->whereIn("status", [CpStatus::ACTIVE->value, CpStatus::RESTORED->value])
            ->first();
    }

    public function getCpCount($userId)
    {
        return Cp::where(function ($query) use ($userId) {
            $query->where("user_one_id", $userId)
                ->orWhere("user_two_id", $userId);
        })
            ->whereHas("cpRelation", function ($q) {
                $q->where('type', '!=', 'solution');
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
            ->whereHas("cpRelation", function ($q) {
                $q->where('type', '!=', 'solution');
            })
            ->whereIn("status", [CpStatus::PENDING->value, CpStatus::ACTIVE->value, CpStatus::RESTORED->value])
            ->first();
    }

    public function checkExistingCpLovlyForUser($userId)
    {
        return Cp::where(function ($query) use ($userId) {
            $query->where("user_one_id", $userId)
                ->orWhere("user_two_id", $userId);
        })
            ->whereHas('relation', function ($q) {
                $q->where('relations_number', 1);
            })
            ->whereIn("status", [CpStatus::PENDING->value, CpStatus::ACTIVE->value, CpStatus::RESTORED->value])
            ->first();
    }

    public function checkExistingCpLovly($userId, $otherUserId)
    {
        return Cp::where(function ($query) use ($userId, $otherUserId) {
            $query->where("user_one_id", $userId)
                ->where("user_two_id", $otherUserId)
                ->orWhere(function ($query) use ($userId, $otherUserId) {
                    $query->where("user_two_id", $userId)
                        ->where("user_one_id", $otherUserId);
                });
        })->relation()
            /// TODO convert these status to enum
            ->whereIn("status", [CpStatus::PENDING->value, CpStatus::ACTIVE->value, CpStatus::RESTORED->value])
            // ->where("cp_relation_id",5)
            ->first();
    }

    public function countExistingCpSameRelation($userId, $relationId)
    {
        return Cp::where(function ($query) use ($userId,) {
            $query->where(function ($query) use ($userId,) {
                $query->where("user_two_id", $userId);
            });
        })->where('cp_relation_id', $relationId)
            /// TODO convert these status to enum
            ->whereIn("status", [CpStatus::PENDING->value, CpStatus::ACTIVE->value, CpStatus::RESTORED->value])
            ->count();
    }

    public function checkExistingCpOne($userId, $cpId)
    {
        return Cp::where("cp_relation_id", $cpId)->where(function ($query) use ($userId) {
            $query->where('user_one_id', $userId)
                ->orWhere('user_two_id', $userId);
        })->whereIn("status", [CpStatus::ACTIVE->value, CpStatus::RESTORED->value])->first();
    }

    public function getUserRelationAvailable($userId, $cpRelationId)
    {
        return UserRelationAvilable::where(["user_id" => $userId, "cp_relation_id" => $cpRelationId])
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
        return UserRelationAvilable::updateOrCreate(
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
                /// todo update this filter
                switch ($type) {
                    case 1:
                        return $query->whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()]);
                    case 2:
                        return $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    case 3:
                        return $query->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
                }
            })
            ->groupBy('cp_id')
            ->orderByDesc('total_gifts')
            ->take(20)
            ->get();
    }

    public function getCpRankingWithOutRelation($type)
    {
        return GiftLog::selectRaw('cp_id, SUM(giftNum * giftPrice) as total_gifts')
            ->whereNotNull("cp_id")
            ->with(['cp' => function ($query) {
                $query->select('id', 'di', 'level_id', 'user_one_id', 'user_two_id', 'cp_relation_id')
                    ->with(['relation' => function ($query) {
                        $query->select('id', 'type');
                    }]);
            }])
            ->whereHas("cp.relation", function ($q) {
                $q->whereNotNull('type');
            })
            ->when($type, function ($query) use ($type) {
                /// todo update this filter
                switch ($type) {
                    case 1:
                        return $query->whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()]);
                    case 2:
                        return $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    case 3:
                        return $query->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
                }
            })
            ->groupBy('cp_id')
            ->orderByDesc('total_gifts')
            // ->take(1)
            ->get()
            ->groupBy(function ($item) {
                return $item->cp->relation->type;
            })
            ->map(function ($groupedLogs) {
                return $groupedLogs->sortByDesc('total_gifts')->first();
            });
    }

    public function getCpList($userId, $activeOnly = false)
    {
        $var = $activeOnly ? [1, 4] : [0, 1, 4];

        return Cp::where(function ($query) use ($userId) {
            $query->where("user_one_id", $userId)
                ->orWhere(function ($query) use ($userId) {
                    $query->where("user_two_id", $userId);
                });
        })
            ->whereIn("status", $var)
            ->get();
    }

    public function findWare($wareId)
    {
        return Ware::find($wareId);
    }

    public function getUserCpProfiles($userId, $statuses, $count = 9)
    {
        return Cp::with('relation:id,title,type', 'toUser', 'fromUser')
            ->whereHas("cpRelation", function ($q) {
                $q->where('type', "!=", 'solution');
            })
            ->where(function ($query) use ($userId) {
                $query->where('user_one_id', $userId)
                    ->orWhere('user_two_id', $userId);
            })
            ->whereIn('status', $statuses)
            ->orderByDesc('di')
            ->take($count)
            ->get();
    }

    public function getByUser($userId)
    {
        return Cp::where(function ($query) use ($userId) {
            $query->where("user_one_id", $userId)
                ->orWhere("user_two_id", $userId);
        })->with('relation:id,title,type', 'toUser', 'fromUser')->get();
    }
}
