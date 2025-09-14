<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\UserPackHelper;
use App\Helpers\UserLevelHelper;
use App\Http\Resources\Api\V1\MangerTypeResource;
use Illuminate\Pagination\LengthAwarePaginator;

class RankingUserV2Resource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data     = collect($this->resource['data'] ?? []);
        $user     = $this->resource['user'] ?? null;
        $userExp  = $this->resource['userExp'] ?? null;
        $key      = $this->resource['key'] ?? 'user_id';
        $class    = $this->resource['class'] ?? 0;
    
        $this->loadUserRelations($user);
    
        $data     = $this->normalizeTopThree($data);
        $userData = $data->where($key, $user->id)->first();
        $userArr  = $this->formatUser($user, $userExp, $userData);
    
        [$top, $otherData] = $this->splitTopAndOther($data);
        $paginatedOther    = $this->paginateOther($otherData);
    
        return [
            'class'             => $class,
            'user'              => $userArr,
            'top'               => $top,
            'other'             => $paginatedOther->items(),
            'others_pagination' => $this->formatPagination($paginatedOther),
        ];
    }
    
    private function loadUserRelations($user): void
    {
        $user->load([
            'packs' => fn($q) => $q
                ->where('type', 25)
                ->where('is_used', true)
                ->with('ware'),
        ]);
    }
    
    private function normalizeTopThree($data): \Illuminate\Support\Collection
    {
        $kong = [
            'user_id'            => 0,
            'uuid'               => '',
            'exp'                => '0',
            'exp_int'            => 0,
            'remaining'          => '0',
            'remaining_int'      => 0,
            'name'               => '',
            'avatar'             => '',
            'frame'              => '',
            'frame_id'           => 0,
            'sender_img'         => '',
            'reseverimg'         => '',
            'vip_level'          => 0,
            'sender_level'       => 0,
            'reciver_level'      => 0,
            'vip_level_img'      => '',
            'sender_level_img'   => '',
            'reciver_level_img'  => '',
            'age'                => 0,
            'type_user'          => 0,
            'manger_type'        => null,
            'achievement_images' => [],
            'color_name'         => '',
        ];
    
        if ($data->count() > 0) {
            $data[0] = $data[0] ?? $kong;
            $data[1] = $data[1] ?? $kong;
            $data[2] = $data[2] ?? $kong;
        }
    
        return $data;
    }
    
    private function formatUser($user, $userExp, $userData): array
    {
        return [
            'user_id'           => $user->id,
            'uuid'              => $user->uuid,
            'exp'               => $userExp ?: ($userData['total_gifts'] ?? '0'),
            'name'              => $user->name,
            'avatar'            => $user->relationLoaded('profile') ? $user->profile?->avatar : null,
            'frame'             => $user->frame,
            'frame_id'          => $user->frame_id,
            'manger_type_id'    => $user->manger_type_id,
            'age'               => $user->relationLoaded('profile') ? ($user->profile?->age ?? '') : null,
            'vip_level'         => $user->relationLoaded('UserVip') ? ($user->UserVip?->level ?? 0) : 0,
            'sender_level'      => $user->total_sender_level ?? '',
            'reciver_level'     => $user->total_received_level ?? '',
            'vip_level_img'     => UserPackHelper::getVipIcon($user),
            'sender_level_img'  => $user->relationLoaded('senderLevel') ? $user->senderLevel?->img : null,
            'reciver_level_img' => $user->relationLoaded('receiverLevel') ? $user->receiverLevel?->img : null,
            'type_user'         => intval(@$user->type_user) ?: 0,
            'country'           => $user->relationLoaded('country') ? $user->country : null,
            'manger_type'       => $user->relationLoaded('mangerType') ? new MangerTypeResource($user->mangerType) : null,
            'color_name'        => UserPackHelper::getColorName($user),
            'achievement_images'=> [],
        ];
    }
    
    private function splitTopAndOther($data): array
    {
        $dataArray  = $data->toArray();
        $countData  = count($dataArray);
    
        $top        = $countData < 4 ? $dataArray : array_slice($dataArray, 0, 3);
        $otherData  = $countData < 4 ? [] : array_slice($dataArray, 3);
    
        return [$top, $otherData];
    }
    
    private function paginateOther(array $otherData): LengthAwarePaginator
    {
        $perPage     = request('per_page', 10);
        $currentPage = LengthAwarePaginator::resolveCurrentPage() ?: 1;
    
        $currentItems = array_slice($otherData, ($currentPage - 1) * $perPage, $perPage);
    
        return new LengthAwarePaginator(
            $currentItems,
            count($otherData),
            $perPage,
            $currentPage,
            [
                'path'     => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }
    
    private function formatPagination(LengthAwarePaginator $paginator): array
    {
        return [
            'total'        => $paginator->total(),
            'per_page'     => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'next_page'    => $paginator->nextPageUrl(),
            'prev_page'    => $paginator->previousPageUrl(),
        ];
    }
    
}
