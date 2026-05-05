<?php

namespace App\Classes\Gifts;

use App\Helpers\Common;
use App\Interfaces\RoomJobInterface;
use App\Models\Room;
use Modules\Charizma\Http\Services\UserCharismaService;

class CharismaWork implements RoomJobInterface

{

    public function work($roomJob) : array
    {
        $room = Room::query()->find($roomJob->room_id);
        if(!$room) throw new \Exception('Room not found');
        $userIds = unserialize($roomJob->data, ['allowed_classes' => false]);
        $earnedCoinsPerUser = $roomJob->coins;

        $data =(new UserCharismaService())->addTotalEarnedCoinsInUserRoom($room, $userIds, $earnedCoinsPerUser);
        return ['room_id'=> $room->id, ...$data];
    }

    public function sendToZego($data, int $roomId,int $user_id):  string
    {
        $data = array_map(function($user) {
            if (isset($user['total'])) {
                \Log::info('sendToZego - Before format', [
                    'user_id' => $user['user_id'] ?? 'N/A',
                    'total_raw' => $user['total'],
                    'total_type' => gettype($user['total'])
                ]);
                info('formatTotalInService', [UserCharismaService::formatTotalInService()]);
                $user['total'] = UserCharismaService::formatTotalInService()
                    ? numToStringNew($user['total'])
                    : (int) $user['total'];
                \Log::info('sendToZego - After format', [
                    'user_id' => $user['user_id'] ?? 'N/A',
                    'total_formatted' => $user['total']
                ]);
            }
            return $user;
        }, $data);

        $ms   = [
            'messageContent' => [
                "message" => "updateCharisma",
                "data"    => $data,
            ]
        ];
        $json = json_encode($ms);

        return $json;
        return Common::sendToZego3('SendCustomCommand', $roomId, $user_id, $json);
    }

    public function prepareDataToZego($data) : array
    {
        $result = [];

        foreach ($data['charisma'] as $item) {
            $room_id = $item["room_id"]; // Assuming the first entry of each item contains the room_id and user data
            foreach ($item as $userData) {
                if (is_array($userData)) { // Ensure we're working with the user data arrays
                    $user_id = $userData['user_id'];

                    \Log::info('prepareDataToZego - userData', [
                        'user_id' => $user_id,
                        'total' => $userData['total'] ?? 'NOT SET',
                        'total_type' => isset($userData['total']) ? gettype($userData['total']) : 'N/A'
                    ]);

                    if (!isset($result[$room_id])) {
                        $result[$room_id] = [
                            'room_id' => $room_id,
                            'data' => [],
                            'total' => 0,
                        ];
                    }
                    if (isset($result[$room_id]['data'][$user_id])) {
                        // If user exists, sum their totals
                        $result[$room_id]['data'][$user_id]['total'] = max($result[$room_id]['data'][$user_id]['total'], $userData['total']);
                    } else {
                        // If user does not exist, add them to the data
                        $result[$room_id]['data'][$user_id] = $userData;
                    }
                    // Always add to the room's total
                    $result[$room_id]['total'] += (int) ($userData['total'] ?? 0);
                }
            }
        }

        $finalResult = [];
        foreach ($result as $room_id => $info) {
            // Extract user data as array values to discard the user_id keys used for summing duplicates
            $users_data = array_values($info['data']);
            $finalResult[] = [
                'room_id' => $info['room_id'],
                'data' => $users_data,
                'total' => $info['total'], // Keep as integer, will be formatted in sendToZego
            ];
        }

        return $finalResult;
    }

    public function getVariables($data): array
    {
        return [$data['data'], $data['room_id'], @$data['data'][0]['user_id'] ?? 0];
    }
}
