<?php

namespace App\Tik\Services;

use App\Helpers\Common;
use App\Tik\Repositories\UserRepository;
use App\Tik\Repositories\RequestBackgroundImageRepository;

class RequestBackgroundImagService
{
    public function __construct(
        private readonly RequestBackgroundImageRepository $requestBackgroundImageRepository,
        private readonly UserRepository $userRepository,

    ) {
    }

    public function create($request, $userId, $price)
    {
  
            if ($request->hasFile('image')) {
                $img                                   = $request->file('image');
                $image                                 = Common::upload('images', $img);

                $data = [
                    'owner_room_id' => $userId,
                    'img' => $image,
                    'price' => $price,
                    'status' => 1,
                ];
                $this->requestBackgroundImageRepository->create($data);
                $this->userRepository->decrementCoins($userId, $price);
                return true;
            }
        
    }

    public function findByUserId($id)
    {
        return $this->requestBackgroundImageRepository->findByUserId($id);
    }
}
