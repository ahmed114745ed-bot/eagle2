<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\Follow;
use App\Helpers\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Facades\CustomNotification;
use App\Http\Controllers\Controller;
use Modules\Public\Http\Services\UserCounterServices;

class FollowController extends Controller
{
    public function follow(Request $request)
    {

        if ($request->user()->id == $request->user_id) {
            return Common::apiResponse(false, 'cant follow your self', null, 403);
        }
        if (!User::query()->find($request->user_id)) {
            return Common::apiResponse(false, 'this user not found', null, 404);
        }
        $f = Follow::query()->where(
            [
                'user_id' => $request->user()->id,
                'followed_user_id' => $request->user_id
            ]
        )->first();
        if (!$f) {
            Follow::query()->create(

                [
                    'user_id' => $request->user()->id,
                    'followed_user_id' => $request->user_id,
                    'status' => 1
                ]
            );
            //follow back

            $receiver = User::find($request->user_id);
            $user = $request->user();
            if ($user->followBack($receiver)) {
                CustomNotification::followBack($receiver, $user);
                (new UserCounterServices)->eventUser($receiver,'friend',1);
            } else {
                CustomNotification::follow($receiver, $user);
                (new UserCounterServices)->eventUser($receiver,'follow',1);
                
            }
            (new UserCounterServices)->eventUser($receiver,'follower',1);


            //follow back




            //Common::handelFirebase ($request,'follow');
        } else {
            $f->status = 1;
            $f->save();
        }



        return Common::apiResponse(true, 'follow done', null, 201);
    }
    public function unFollow(Request $request)
    {
        Follow::query()->where('user_id', $request->user()->id)->where('followed_user_id', $request->user_id)->delete();
        return Common::apiResponse(true, 'unFollow done', null, 201);
    }
}
