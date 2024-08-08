<?php

namespace App\Http\Controllers\Api\V2;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AllGamesResource;
use App\Models\AllGame;
use App\Models\CoinGameUser;
use App\Models\User;
use App\Services\AppFeatureService;
use Database\Seeders\config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use function Laravel\Prompts\confirm;

class GameController extends Controller
{
    
    
    // Function to decrypt the data
    function decryptData($data, $key, $secret) {
        [$encryptedData, $iv] = explode('::', base64_decode($data), 2);
        return openssl_decrypt($encryptedData, 'aes-256-cbc', $key, 0, $iv);
    }


    function get_user(Request $request)  {
        $encryptedData = $request->input('token');
        $app_key = \config('games.app_key');
        $app_screet = \config('games.app_secret');

       // Log::info($app_screet . $app_key);
        //check if encryption if valid or not
        $decryptedData = $this->decryptData($encryptedData, $app_key, $app_screet);
        if (!json_decode($decryptedData, true)) {
            abort(403, 'Invalid  request');
        }

        //check if encryption has our keys or not
        $data = json_decode($decryptedData, true);
        if ($app_key !== $data['app_key'] ||  $app_screet !== $data['app_screet'] ) {
            abort(403, 'Invalid  request');
        }

        //check if user token is valid  or not
        $user1 = Auth::user();
        $user  = User::find($user1->id);
        if (!$user ) {
            abort(403, 'Invalid  request');
        }

        $user = [
            'id'    => $user1->id,
            'img'   => getImagePath($request->user()->profile?->avatar ?? ''),
            'coins' => $user1->di,
            'name'  => $user1->name,
        ];
       // Log::info(implode(',', array_values($user)));

        return response()->json( $user);

    }

    function edit_user(Request $request) {
        $encryptedData = $request->input('token');
        $app_key = \config('games.app_key');
        $app_screet = \config('games.app_secret');

        //check if encryption if valid or not
        $decryptedData = $this->decryptData($encryptedData, $app_key, $app_screet);
        if (!json_decode($decryptedData, true)) {
            abort(403, 'Invalid  request');
        }

        //check if encryption has our keys or not
        $data = json_decode($decryptedData, true);
        if ($app_key !== $data['app_key'] ||  $app_screet !== $data['app_screet'] ) {
            abort(403, 'Invalid  request');
        }

        //check if user token is valid  or not
        $user = User::find($request->user()->id);
        if (!$user || $user->di < $data['cost'] ) {
            abort(403, 'Invalid  request');
        }

        $user->di += $data['coins'];
        $user->update();
        CoinGameUser::create(['user_id' =>  $user->id, 'coins' => $data['coins'], 'type' => ($data['coins'] < 0 ? 0 : 1)]);

        $user = [
            'id'    => $user->id,
            'img'   => getImagePath($request->user()->profile?->avatar ?? ''),
            'coins' =>$user->di,
            'name'  =>$user->name,
        ];
        return response()->json( $user);
    }
    public function gel_all_games()
    {
        $data=AllGame::query()->select("id",'name','name_en')->get();
        return Common::apiResponse(1, '', AllGamesResource::collection($data));
    }
}
