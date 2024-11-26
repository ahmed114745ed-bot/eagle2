<?php

namespace Modules\CP\Http\Services;

use App\Repositories\CpRepository;
use App\Models\User;
use App\Helpers\Common;
use Illuminate\Http\Request;
use Modules\Chat\Entities\ChatMessage;
use Modules\Chat\Entities\ChatRoom;
use Modules\Chat\Events\Chat;
use Modules\Chat\Events\Conversation;
use Modules\Chat\Http\Resources\ChatMessageResource;
use Modules\Chat\Http\Resources\ChatRoomResourcePusher;
use Modules\Chat\Http\Services\ChatService;
use Modules\CP\Entities\CpRelation;
use Modules\CP\Repositories\CpRepository as RepositoriesCpRepository;
use Modules\CP\Transformers\CpListResource;
use Modules\CP\Transformers\RankingResource;
use Modules\CP\Transformers\RequestCpResource;

class CpserviceCo
{
    protected $cpRepository;

    public function __construct(RepositoriesCpRepository $cpRepository, public ChatService $chatService)
    {
        $this->cpRepository = $cpRepository;
    }

    public function makeRequestCp($request, $user)
    {
        $cpRelation = $this->cpRepository->getCpRelationById($request->cp_relation_id);

        if (!$cpRelation) {
            return Common::apiResponse(0, 'لا يوجد cp relations');
        }

        $cpCount = $this->cpRepository->getCpCount($user->id);

        if ($cpCount >= 15) {
            return Common::apiResponse(0, 'لقد تعديت العدد المسموح به!');
        }
        if ($cpRelation->cp_one == 1) {
            $existingCpOne = $this->cpRepository->checkExistingCpOne($user->id,$cpRelation->id);
            $existingCptwo = $this->cpRepository->checkExistingCpOne($request->user_id,$cpRelation->id);

            if ($existingCpOne) {
                return Common::apiResponse(0, 'انت تتمتع ب cp مع شخص اخر!');
            }

            if ( $existingCptwo) {
                return Common::apiResponse(0, 'هذا المستخدم يتمتع ب cp مع شخص اخر!');
            }
        }

        $existingCp = $this->cpRepository->checkExistingCp($user->id, $request->user_id);

        if ($existingCp) {
            return Common::apiResponse(0, 'لا يمكنك تقديم cp مع هذا المستخدم حاليا!');
        }

        $userRelation = $this->cpRepository->getUserRelationAvailable($user->id, $request->cp_relation_id);

        if ($userRelation) {
            $this->cpRepository->decrementUserRelationCount($userRelation);
        } else {
            if ($user->di < $cpRelation->price) {
                return Common::apiResponse(0, 'لا يوجد رصيد كافي من الكوينات برجاء الشحن!');
            }
        }

        $this->cpRepository->createCp([
            "cp_relation_id" => $request->cp_relation_id,
            "user_one_id" => $user->id,
            "user_two_id" => $request->user_id,
            "price" => $cpRelation->price,
        ]);

        $user->di -= $cpRelation->price;
        $user->save();

        $chatRoom = ChatRoom::BetweenUsers($user->id, $request->user_id)->first();


        if (!$chatRoom) {
            return response()->json([
                'status' => 404,
                'status' => 'Chat not Found',
            ], 404);
        }

        $user->di -= $cpRelation->price;
        $user->save();


        $user2 = User::find($request->user_id);

        $data = [
            'id' => $cpRelation->id,
            'title' => $cpRelation->description,
            'price' => $cpRelation->price,
            'image' => $cpRelation->image,
            'status' => 0
        ];

        $key = env('MESSAGE_KEY');

        $message = $this->encryptArray($data,$key);


        $chatMessageData = [
            'chat_room_id' => $chatRoom->id,
            'user_id' => $user->id,
            'message' => $message,
            'type' => 'CP'
        ];

        if($user2->online == 1 && $user2->current_room_chat == $chatRoom->id )
        {

            $chatMessageData['status'] = 'seen';
        }
        else if($user2->online == 1)
        {
            $chatMessageData['status'] = 'received';
        }
        $chatMessage = ChatMessage::create($chatMessageData);

        if($user2->is_logout != 1) {
            $tokens_notfacion[] = \DB::table('users')->where('id', $user2->id)->value('notification_id');
            $title=$user->name;
            $body= $message ;
            $type = $message->type ?? 'text';
            Common::send_firebase_notification($tokens_notfacion,$title,$body,messageType:$type );

        }

        $message_resource = new ChatMessageResource($chatMessage);
        $room_resource =  new ChatRoomResourcePusher($chatRoom) ;

        event(new Conversation($message_resource->toResponse(request())->getData()->data, $user2, $room_resource));

        event(new Chat($room_resource->toResponse(request())->getData()->data, $user2));

        return Common::apiResponse(1, 'تم الاضافه بنجاح');
    }


    function encryptArray(array $data, string $key): string
    {
        $ivLength = openssl_cipher_iv_length('AES-256-CBC');
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encrypted = openssl_encrypt(
            json_encode($data), // Convert the array to JSON
            'AES-256-CBC',      // Encryption algorithm
            $key,               // Your custom key
            0,                  // Options (0 for default)
            $iv                 // Initialization vector
        );

        // Combine IV and encrypted data, then encode it to base64
        return base64_encode($iv . $encrypted);
    }

    function decryptArray(string $encryptedData, string $key): array
    {
        $ivLength = openssl_cipher_iv_length('AES-256-CBC');
        $data = base64_decode(substr($encryptedData, 3));

        // Extract the IV and the encrypted string
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);

        $decrypted = openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            $key,
            0,
            $iv
        );

        // Decode JSON back to an array
        return json_decode($decrypted, true);
    }

    public function getRequestCp($user)
    {
        $data = $this->cpRepository->getRequestsForUser($user->id);
        return Common::apiResponse(1, '', RequestCpResource::collection($data));
    }

    public function respondToRequest(Request $request)
    {
        $cp = $this->cpRepository->findCpById($request->cp_id);
        $user = $request->user();

        if (!$cp || $cp->status != 0) {
            return Common::apiResponse(0, 'لا يوجد cp');
        }

        if ($cp->user_two_id != $user->id) {
            return Common::apiResponse(0, 'هناك شئ ما خطا');
        }

        if ($request->status == 1) {
            $this->cpRepository->updateCpStatus($cp, 1);
        } elseif ($request->status == 0) {
            $this->cpRepository->updateCpStatus($cp, 2);
            $this->cpRepository->updateOrCreateUserRelation($cp->user_one_id, $cp->cp_relation_id);
        }


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
