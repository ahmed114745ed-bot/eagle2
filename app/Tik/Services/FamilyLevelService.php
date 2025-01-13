<?php

namespace App\Tik\Services;

use App\Helpers\Common;
use Illuminate\Support\Facades\DB;
use App\Tik\Repositories\FamilyLevelRepository;


class FamilyLevelService
{

    public function __construct(
        private readonly FamilyLevelRepository $FamilyLevelRepository,

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

    public function create( $request)
    {
        
        $img = null;
        if ($request->hasFile('img'))  $img = Common::upload(' FamilyLevels', $request->file('img'));

        $FamilyLevelData = [
            'name' => $request->name,
            'img' => $img,
            'exp' => $request->exp,
            'type' => $request->type,
            'members' => $request->members,
            'admins' => $request->admins,
        ];
        $FamilyLevel =  $this->FamilyLevelRepository->store($FamilyLevelData);

        return $FamilyLevel;
       
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

   

    public function delete( $FamilyLevelId)
    {
        $FamilyLevel = $this->FamilyLevelRepository->find($FamilyLevelId);
        if (!$FamilyLevel) throw new \Exception('not found');
        $FamilyLevel->delete();
        return true;
    }

   

   
   
}
