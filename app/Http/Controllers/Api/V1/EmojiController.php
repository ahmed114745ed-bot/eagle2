<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Emoji;
use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Tik\Services\EmojiService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\EmojiResource;

class EmojiController extends Controller
{
    public function __construct(private EmojiService $emojiService) {}
    public function index(Request $request)
    {
        $data = $this->emojiService->index($request);

        return Common::apiResponse(1, '', EmojiResource::collection($data), 200);
    }

    public function show(Request $request, $id)
    {
        if (!$request->user_id || !$request->room_id) {
            return Common::apiResponse(0, 'user_id , room_id are required');
        }
        $data = $this->emojiService->show($id);

        $data->user_id = $request->user_id;

        if ($request->to_zego == 1) {
            $d = [
                "messageContent" => [
                    "message" => "showEmojie",
                    "id" => $data->id,
                    "emoji" => $data->emoji,
                    "t_length" => $data->t_length,
                    "id_user" => $request->user_id
                ]
            ];
            $json = json_encode($d);
            $res = Common::sendToZego('SendCustomCommand', $request->room_id, $request->user_id, $json);
        }

        return Common::apiResponse(1, '', $data);
    }
}
