<?php

namespace Modules\CP\Http\Services;

use App\Repositories\CpRepository;
use App\Models\User;
use App\Helpers\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Chat\Entities\ChatMessage;
use Modules\Chat\Entities\ChatRoom;
use Modules\Chat\Events\Chat;
use Modules\Chat\Events\Conversation;
use Modules\Chat\Events\OpenChat;
use Modules\Chat\Http\Resources\ChatMessageResource;
use Modules\Chat\Http\Resources\ChatRoomResourcePusher;
use Modules\CP\Entities\CpRelation;
use Modules\CP\Repositories\CpRepository as RepositoriesCpRepository;
use Modules\CP\Transformers\CpListResource;
use Modules\CP\Transformers\RankingResource;
use Modules\CP\Transformers\RequestCpResource;

class CpserviceCo
{
    protected $cpRepository;

    public function __construct(RepositoriesCpRepository $cpRepository)
    {
        $this->cpRepository = $cpRepository;
    }

    public function makeRequestCp($request, $user)
    {


        $cpRelation = $this->cpRepository->getCpRelationById($request->cp_relation_id);

        if (!$cpRelation) {
            return Common::apiResponse(0, 'لا يوجد cp relations');
        }

        if ($cpRelation->type == 'solution') {
            $findRelationBetweenUsers = $this->cpRepository->findCpBetweenUsers($user->id, $request->user_id);
            if (!$findRelationBetweenUsers) {
                return Common::apiResponse(0, 'لا يوجد cp relations بين المستخدمين');
            }
        }

        $cpCount = $this->cpRepository->getCpCount($user->id);

        if ($cpCount >= 15) {
            return Common::apiResponse(0, 'لقد تعديت العدد المسموح به!');
        }
        if ($cpRelation->cp_one == 1) {
            $existingCpOne = $this->cpRepository->checkExistingCpOne($user->id, $cpRelation->id);
            $existingCptwo = $this->cpRepository->checkExistingCpOne($request->user_id, $cpRelation->id);

            if ($existingCpOne) {
                return Common::apiResponse(0, 'لقد قمت بارسال  طلب cp من قبل ');
            }

            if ($existingCptwo) {
                return Common::apiResponse(0, 'قام بارسال طلب cp  لك اقبله');
            }
        }



        $existingCp = $this->cpRepository->checkExistingCp($user->id, $request->user_id);
        /*         $existingLovelyOneCp = $this->cpRepository->checkExistingCpLovlyForUser($user->id);
        $existingLovelyTwoCp = $this->cpRepository->checkExistingCpLovlyForUser($request->user_id); */

        if ($existingCp && $cpRelation->type != 'solution') {
            return Common::apiResponse(0, 'لا يمكنك تقديم cp مع هذا المستخدم حاليا!');
        }

        /*         if ($existingLovelyOneCp || $existingLovelyTwoCp) {
            return Common::apiResponse(0, 'لا يمكن للمستخد الدخول ف اكتر من علاقه من نوع احبه');
        } */

        $countRequestUserOne = $this->cpRepository->countExistingCpSameRelation($user->id,  $request->cp_relation_id);
        $countRequestUserTwo = $this->cpRepository->countExistingCpSameRelation($request->user_id,  $request->cp_relation_id);
        if (($cpRelation->relations_number > 0) &&
            (($countRequestUserOne >= $cpRelation->relations_number) ||
                ($countRequestUserTwo >= $cpRelation->relations_number)) && $cpRelation->type != 'solution'
        ) {
            return Common::apiResponse(0, ' cp لقد تخطيت طلب ');
        }

        $userRelation = $this->cpRepository->getUserRelationAvailable($user->id, $request->cp_relation_id);

        if ($userRelation) {
            $this->cpRepository->decrementUserRelationCount($userRelation);
        } else {
            if ($user->di < $cpRelation->price) {
                return Common::apiResponse(0, 'لا يوجد رصيد كافي من الكوينات برجاء الشحن!');
            }
            $user->di -= $cpRelation->price;
            $user->save();
        }

        $stoppedelation = $this->cpRepository->findStoppedRelationBetweenTwoUsers($user->id, $request->user_id, $cpRelation->type);
        if ($stoppedelation) {
            $stoppedelation->status = 5;
            $stoppedelation->save();
            $cp_request = $stoppedelation;
        } else {
            $cp_request = $this->cpRepository->createCp([
                "cp_relation_id" => $request->cp_relation_id,
                "user_one_id" => $user->id,
                "user_two_id" => $request->user_id,
                "price" => $cpRelation->price,
            ]);
        }

        $chatRoom = ChatRoom::BetweenUsers($user->id, $request->user_id)->first();


        if (!$chatRoom) {

            $chatRoom = ChatRoom::create([
                'user_id' => $user->id,
                'user_id2' => $request->user_id
            ]);

            $user->current_room_chat = $chatRoom->id;

            /* return response()->json([
                'status' => 404,
                'status' => 'Chat not Found',
            ], 404); */
        }


        $user2 = User::find($request->user_id);

        $data = [
            'id' => $cp_request->id,
            'title' => $cpRelation->description,
            'price' => $cpRelation->price,
            'image' => $cpRelation->image,
            'status' => 0
        ];

        $key = env('MESSAGE_KEY');

        $message = json_encode($data);

        $chatMessageData = [
            'chat_room_id' => $chatRoom->id,
            'user_id' => $user->id,
            'message' => $message,
            'type' => 'CP'
        ];

        if ($user2->online == 1 && $user2->current_room_chat == $chatRoom->id) {

            $chatMessageData['status'] = 'seen';
        } else if ($user2->online == 1) {
            $chatMessageData['status'] = 'received';
        }
        $chatMessage = ChatMessage::create($chatMessageData);

        if ($user2->is_logout != 1) {
            $tokens_notfacion[] = \DB::table('users')->where('id', $user2->id)->value('notification_id');
            $title = $user->name;
            $body = $message;
            $type = $message->type ?? 'text';
            Common::send_firebase_notification($tokens_notfacion, $title, $body, messageType: $type);
        }

        $message_resource = new ChatMessageResource($chatMessage);
        $room_resource =  new ChatRoomResourcePusher($chatRoom);

        if ($chatRoom->user_id == $user->id) {
            $chatuser = User::find($chatRoom->user_id2);
        } else {
            $chatuser = User::find($chatRoom->user_id);
        }

        try {
            event(new OpenChat($room_resource->toResponse(request())->getData()->data, $chatuser, $chatRoom));
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return $th->getMessage();
        }

        event(new Conversation($message_resource->toResponse(request())->getData()->data, $user2, $room_resource));

        event(new Chat($room_resource->toResponse(request())->getData()->data, $user2));

        return Common::apiResponse(1, 'تم الاضافه بنجاح');
    }

    public function getRequestCp($user)
    {
        $data = $this->cpRepository->getRequestsForUser($user->id);
        return Common::apiResponse(1, '', RequestCpResource::collection($data));
    }

    public function respondToRequest(Request $request)
    {

        /// Todo get message_id to update status in this message
        $messageId = $request->message_id;

        $message = ChatMessage::find($messageId);

        $decryptedData = json_decode($message->message, true);

        $cp = $this->cpRepository->findCpById($request->cp_id);


        if ($cp->relation->type == 'solution') {
            if ($request->status == 1) {
                $cpBetweenUsers = $this->cpRepository->findCpBetweenUsers($cp->user_one_id, $cp->user_two_id, 'solution');
                $cpBetweenUsers->status = 3;
                $cpBetweenUsers->save();
            }

            /* $message->message = json_encode($decryptedData);

            $message->save();

            return Common::apiResponse(1, 'تم الرد علي الطلب بنجاح'); */
        }

        $user = $request->user();
        if (!$cp || ($cp->status != 0 && $cp->status != 5)) {
            return Common::apiResponse(0, 'لا يوجد cp');
        }

        if ($cp->user_two_id != $user->id) {
            return Common::apiResponse(0, 'هناك شئ ما خطا');
        }



        if ($request->status == 1) {
            if ($cp->status == 5) {
                $cp->status = 4; // restored
                $cp->price += $cp->cpRelation->price;
                $cp->save();

            } else {
                $this->cpRepository->updateCpStatus($cp, 1);
            }
            $decryptedData['status'] = 1;
        } elseif ($request->status == 2) {

            if ($cp->status == 5) {
                $cp->status = 3; // restored
                $cp->save();
            } else {
                $this->cpRepository->updateCpStatus($cp, 2);
            }
            $this->cpRepository->updateOrCreateUserRelation($cp->user_one_id, $cp->cp_relation_id);
            $decryptedData['status'] = 2;
        }

        $message->message = json_encode($decryptedData);

        $message->save();

        return Common::apiResponse(1, 'تم الرد علي الطلب بنجاح');
    }

    public function getCpRanking()
    {
        $relationType = request("relationType") ?? CpRelation::first()?->id;
        $type = request("type") ?? 1;

        $data = $this->cpRepository->getCpRanking($relationType, $type);

        $first = $data->take(3);
        $second = $data->skip(3);
        $result = [
            "firstThree" => RankingResource::collection($first),
            "remain" => RankingResource::collection($second),
        ];

        return Common::apiResponse(1, '', $result);
    }

    public function getCpList($userId)
    {
        $data = $this->cpRepository->getCpList($userId, true);
        return Common::apiResponse(1, '', CpListResource::collection($data));
    }
}
