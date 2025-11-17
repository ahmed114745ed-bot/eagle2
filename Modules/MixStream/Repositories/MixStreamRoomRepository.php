<?php

namespace Modules\MixStream\Repositories;

use App\Tik\Repositories\AbstractRepository;
use Modules\MixStream\Entities\MixStreamRoom;

class MixStreamRoomRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new MixStreamRoom());
    }

    public function checkExistenceMix($mixStreamId, $liveRoomId)
    {
        return $this->model->where('mix_stream_id', '<>', $mixStreamId)->where('room_id', $liveRoomId)->exists();
    }
}
