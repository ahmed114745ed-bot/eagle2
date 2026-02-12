<?php

namespace Utd\Room\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $pk = $this->whenLoaded('lastPk', fn () => $this->lastPk);
        $isParty = $this->roomCategory && $this->roomCategory->type === 'party';

        $data = [
            'id' => $this->id,
            'owner_id' => $this->uid ?: 0,
            'owner_uuid' => $this->owner?->uuid_v2 ?: 0,
            'owner_name' => $this->owner?->name ?: '',
            'room_name' => $this->room_name ?: '',
            'owner_image' => $this->owner?->profile?->avatar ?: '',
            'room_id' => (string) ($this->id ?: 0),
            'name' => $this->room_name ?: '',
            'mode' => $this->mode,
            'visitors_count' => $this->count_room_socket_v2 ?? 0,
            'cover' => $this->room_cover ?: '',
            'is_hot' => $this->hot ?: 0,
            'session' => $this->session_string ?? '',
            'is_popular' => $this->is_popular ?: 0,
            'room_status' => $this->room_status,
            'password_status' => (bool) $this->room_pass,
            'room_intro' => $this->room_intro ?? '',
            'max_admin' => $this->max_admin ?: '',
            'is_recommended' => $this->is_recommended ?: 0,
            'lang' => $this->lang ?: '',
            'is_pk' => (bool) $pk,
            'is_party' => $isParty,
            'room_background' => $this->final_room_image ?? '',
            'stream_type' => $this->type ?? 'audio',
            'is_live' => (bool) $this->is_live,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Add microphone data if detailed view is requested
        if ($request->get('show')) {
            $micString = $this->microphones()
                ->orderBy('position')
                ->get()
                ->map(function ($mic) {
                    $userId = $mic->user_id ?? 0;
                    $status = $mic->status ?? 0;

                    return $userId > 0 ? "{$userId}#{$status}" : (string) $status;
                })
                ->implode(',');

            $data = array_merge($data, [
                'background' => $this->final_room_image ?: $this->room_background,
                'mics' => $micString ? explode(',', $micString) : [],
                'is_mics_free' => $this->free_mic ?: 0,
                'admins_ids' => $this->getAdminsIds(),
            ]);
        }

        return $data;
    }

    /**
     * Get admin IDs from room_admin string
     */
    protected function getAdminsIds(): array
    {
        $ids = explode(',', $this->room_admin ?? '');

        return array_filter(array_map('intval', $ids));
    }
}
