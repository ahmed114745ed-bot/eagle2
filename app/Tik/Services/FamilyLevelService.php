<?php

namespace App\Tik\Services;

use App\Facades\CustomNotification;
use App\Models\User;
use App\Helpers\Common;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use App\Tik\Repositories\RoomRepository;
use App\Tik\Repositories\UserRepository;
use App\Tik\Repositories\FamilyLevelRepository;
use App\Http\Resources\Api\V2\FamilyLevelResource;
use App\Tik\Repositories\FamilyLevelRankRepository;
use App\Tik\Repositories\FamilyLevelUserRepository;

class FamilyLevelService
{

    public function __construct(
        private readonly FamilyLevelRepository $FamilyLevelRepository,
        private readonly UserRepository $userRepository,

    ) {}

    public function index()
    {
        return $this->FamilyLevelRepository->all();
    }

    public function getWithSearch($search = null): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $data = $this->FamilyLevelRepository->getWithSearch($search);
        // return FamilyLevelResource::collection($data);
        return $data;
    }

    public function show($id)
    {

        return $this->FamilyLevelRepository->find($id);
    }

    public function create(User $user, $request, $price)
    {
        $FamilyLevelLevel = $this->FamilyLevelLevelRepository->findByUserId($user->id);
        if ($FamilyLevelLevel) throw new \Exception('already have FamilyLevelLevel');

        $img = null;
        if ($request->hasFile('image'))  $img = Common::upload('families', $request->file('image'));


        // DB::beginTransaction();
        $FamilyLevelLevelData = [
            'name' => $request->name,
            'introduce' => $request->introduce,
            'notice' =>  $request->notice,
            'user_id' => $user->id,
            'num' => 20,
            'image' =>  $img ?: '',
            'is_success' => 1,
        ];
        $FamilyLevelLevel =  $this->FamilyLevelLevelRepository->create($FamilyLevelLevelData);

        $FamilyLevelLevelUserData = [
            'user_id' => $user->id,
            'FamilyLevelLevel_id' => $FamilyLevelLevel->id,
            'user_type' => 2,
            'status' => 1,
        ];
        // $this->userRepository->decrementCoins($user->id, $price);
        // $this->userRepository->updateFamilyLevelId($user, $FamilyLevel->id);
        // return $FamilyLevel;
    }



    public function update($userId, $request, $FamilyLevelId)
    {
        $FamilyLevel = $this->FamilyLevelRepository->findById($FamilyLevelId);
        $is_admin = $this->FamilyLevelUserRepository->checkIsAdmin($FamilyLevelId, $userId);
        if (($userId != $FamilyLevel->user_id) && !$is_admin) throw new \Exception('not allowed');
        if (!$FamilyLevel) throw new \Exception('not found');
        if ($request->name) {
            $FamilyLevel->name = $request->name;
        }
        if ($request->introduce) {
            $FamilyLevel->introduce = $request->introduce;
        }
        if ($request->notice) {
            $FamilyLevel->notice = $request->notice;
        }
        if ($request->hasFile('image')) {
            $FamilyLevel->image = Common::upload('families', $request->file('image'));
        }
        $FamilyLevel->save();
        return $FamilyLevel;
    }

   

    public function delete($user, $FamilyLevelId)
    {
        $FamilyLevel = $this->FamilyLevelRepository->findById($FamilyLevelId);
        if (!$FamilyLevel) throw new \Exception('not found');
        if ($user->id != $FamilyLevel->user_id) return Common::apiResponse(0, 'not allowed', null, 403);
        $this->FamilyLevelUserRepository->delete($FamilyLevelId);
        $this->userRepository->updateFamilyLevelId($user, null);
        $this->userRepository->updateUsersFamilyLevel($FamilyLevelId, null);
        $FamilyLevel->delete();
        return true;
    }

   

   
   
}
