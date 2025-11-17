<?php

namespace Modules\MixStream\Repositories;

use App\Tik\Repositories\AbstractRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\MixStream\Entities\MixStream;

class MixStreamRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new MixStream());
    }

    public function get(): LengthAwarePaginator
    {
        return $this->model->with(['rooms'])->paginate(request('per_page', 10));
    }

    public function createMix($liveRoomId)
    {
        $mixStream = $this->model->firstOrCreate(['room_id' => $liveRoomId]);

        $mixStream->rooms()->firstOrCreate([
            'room_id' => $mixStream->room_id,
        ]);

        return $mixStream;
    }

    public function createMixRoom($mixStream, $liveRoomId)
    {
        $mixStream->rooms()->firstOrCreate([
            'room_id' => $liveRoomId,
        ]);

        return $mixStream;
    }

    public function getExistenceMix($mixStream, $liveRoomId)
    {
        return $mixStream->rooms()->where('room_id', $liveRoomId)->first();
    }
}
