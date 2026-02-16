<?php

namespace Utd\Chat\Traits;

use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

trait FfmpegTrait
{
    public function extract_frame($videoPath, $thumbnailPath)
    {
        FFMpeg::fromDisk(config('filesystems.default'))
            ->open($videoPath)
            ->getFrameFromSeconds(1)
            ->export()
            ->toDisk(config('filesystems.default'))
            ->save($thumbnailPath);
    }
}
