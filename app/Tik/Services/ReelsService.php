<?php

namespace App\Tik\Services;

use App\Helpers\Common;
use App\Tik\Repositories\ReelsRepository;
use Illuminate\Support\Facades\DB;
use App\Tik\Repositories\reelRepository;


class ReelsService
{

    public function __construct(
        private readonly ReelsRepository $reelRepository,

    ) {}

    public function index($perPage, $Page,)
    {
        return $this->reelRepository->all($perPage, $Page);
    }

   

    public function show($id)
    {

        return $this->reelRepository->find($id);
    }

    public function search($id)
    {

        return $this->reelRepository->search($id);
    }
    
   
    public function delete( $reelId)
    {
        $reel = $this->reelRepository->find($reelId);
        if (!$reel) throw new \Exception('not found');
        // if (!$reel)  return Common::apiResponse(false, 'not found', null);

        $reel->delete();
        return true;
    }

   

   
   
}
