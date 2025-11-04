<?php

namespace App\Classes\Gifts;

use App\Interfaces\RoomJobInterface;
use App\Jobs\TestTestCharizma;
use App\Soalfna\DTO\RoomJob;

class RoomJobFactory
{
    public RoomJobInterface $roomJob;
    public string $type;

    public function setType($type): static
    {
        if ($type == 'charisma'){
            $this->type = 'charisma';
            $this->roomJob = new CharismaWork();
        }elseif ($type == 'pk'){
            $this->type = 'pk';
            $this->roomJob = new PKWork();
        }
        return $this;
    }
    public function work($roomJob): array
    {

        return $this->roomJob->work($roomJob);
    }

    public function sendToZego(array $data)
    {
        $data = $this->roomJob->prepareDataToZego($data);
        dispatch(new TestTestCharizma($data, $this->roomJob))->onQueue($this->type . '-job');
    }
}
