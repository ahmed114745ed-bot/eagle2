<?php

namespace Modules\SwitchAccount\Http\Controllers;

use App\Models\User;
use Dotenv\Util\Str;
use App\Helpers\Common;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Chat\Entities\ChatRoom;
use Modules\Chat\Entities\ChatMessage;
use Illuminate\Contracts\Support\Renderable;
use Modules\SwitchAccount\Entities\UserAccount;
use Modules\SwitchAccount\Transformers\AccountResource;

class SwitchAccountController extends Controller
{
    public function add_account(Request $request)
    {
        $user = $request->user();
        if (!$request->token_new_account) return Common::apiResponse(0, 'missing params', null, 422);
        try {
            $otherUser = $this->getOtherUser($request);
            if ($otherUser->id == $user->id) return Common::apiResponse(0, 'can not add yourself', null, 422);
            $key = \Illuminate\Support\Str::uuid();
            $found = UserAccount::where(function ($q) use ($otherUser) {
                $q->where('child_user_id', $otherUser->id)->orWhere('parent_user_id', $otherUser->id);
            })->first();

            if ($found) {
                return Common::apiResponse(0, 'account is related to another account', null, 422);
            }
        } catch (\Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
        UserAccount::firstOrCreate([
            'parent_user_id' => $user->id,
            'child_user_id' => $otherUser->id,
            'device_token' => $user->device_token,
        ], [
            'key' => $key,
            'expire' => 30,
        ]);
        $accounts = $this->getAccounts($user->id, $otherUser->id, $user->device_token);
        $user_acount = UserAccount::query()->where(function ($q) use ($user) {
            $q->where("parent_user_id", $user->id)->orWhere("child_user_id", $user->id);
        })->first();
        $data = [
            'current'       => [
                'image'         =>  $user->profile->avatar,
                'name'          =>  $user->name,
                'key'           =>  $user_acount->key,
                'expire'        =>  $user_acount->expire,
                'can_switch'    =>  false
            ],
            'other'         => AccountResource::collection($accounts)
        ];
        return Common::apiResponse(1, 'success', $data, 200);
    }

    public function getAccounts($userId, $otherUserId, $deviceToken)
    {

        if (empty($deviceToken))  return  [];

        $users = UserAccount::
            //            where(function ($q) use ($userId,$otherUserId){
            //                $q->where("parent_user_id", $userId)
            //                    ->orWhere("child_user_id", $userId)
            //                    ->orWhere("child_user_id", $otherUserId)
            //                    ->orWhere("parent_user_id", $otherUserId);
            //            })
            where('device_token', $deviceToken)
            ->get();

        $parentUserIds = $users->pluck('parent_user_id');
        $childUserIds = $users->pluck('child_user_id');

        $allIds = $parentUserIds->merge($childUserIds)->unique()->values()->all();

        $filteredIds = array_filter($allIds, function ($id) use ($userId) {
            return $id != $userId;
        });
        $filteredIds = array_values($filteredIds);
        $accounts = User::query()->whereIn('id', $filteredIds)->get();

        return $accounts ?? [];
    }

    public function getOtherUser($request)
    {
        $bearerToken = $request->token_new_account;

               if (strpos($bearerToken, '|') !== false) {
                   [$id, $bearerToken] = explode('|', $bearerToken, 2);
               }
            //    $token = hash('sha256', $bearerToken);
        $token = DB::table('personal_access_tokens')->where('tokenable_type', "App\Models\User")->where('token', $bearerToken)->first();
        if (! $token) throw new \Exception( 'user token not found');
        $otherUser = User::find($token->tokenable_id);
        return $otherUser;
    }

    public function switch_account(Request $request)
    {
        $user = $request->user();
        if (!$request->key) return Common::apiResponse(0, 'missing params', null, 422);

        $user_account = UserAccount::query()->where("key", $request->key)->first();
        if (!$user_account) return Common::apiResponse(0, 'missing params', null, 422);

        //        $new_account_signin_id = $user_account->parent_user_id == $user->id ? $user_account->child_user_id : $user_account->parent_user_id;
        $new_account = User::find($request->id);
        $token = $new_account->createToken('api_token')->plainTextToken;
        $new_account->auth_token = $token;

        $data = [
            'id'            => $new_account->id,
            'is_first'      => @(bool)$new_account->is_points_first,
            'auth_token'    => $new_account->auth_token
        ];
        return Common::apiResponse(1, 'success', $data, 200);
    }

    public function myAccounts()
    {
        $user = \Auth::user();
        $currentUser = User::find($user->id);
        $chats_id = ChatRoom::where('user_id', $user->id)->orWhere('user_id2', $user->id)->pluck('id')->toArray();
        $total_unread_message =  ChatMessage::whereIn('chat_room_id', $chats_id)->where('user_id', 'not Like', $user->id)->where('status', 'not Like', 'seen')->count();

        $accounts = $this->getAccounts($user->id, 0, $user->device_token);
        $user_acount = UserAccount::query()->where(function ($q) use ($user) {
            $q->where("parent_user_id", $user->id)->orWhere("child_user_id", $user->id);
        })->first();
        $data = [
            'current'       => [
                'image'         =>  $currentUser->profile->avatar,
                'name'          =>  $currentUser->name,
                'uuid'          => $currentUser->uuid,
                'user_type'     => $currentUser->type_user,
                'sender_level'  => $currentUser->sender_level ?? 0,
                'received_level'  => $currentUser->received_level ?? 0,
                'unread_messages'  => $total_unread_message ?? 0,
                'key'           =>  $user_acount?->key,
                'expire'        =>  $user_acount?->expire,
                'can_switch'    =>  false
            ],
            'other'         => AccountResource::collection($accounts)
        ];
        return Common::apiResponse(1, 'success', $data, 200);
    }
}
