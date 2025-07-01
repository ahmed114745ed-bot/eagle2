<?php

namespace Modules\Reals\Http\Services;

use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class FfmpegService
{

    public function extract($videoPath, $id)
    {
        $imagePath = (config('app.env') != 'production' ? '' : 'test-') . "frames/" . $id . '.jpg';
        FFMpeg::openUrl($videoPath)
            ->getFrameFromSeconds(1)
            ->export()
            ->toDisk('gcs')
            ->save($imagePath);
    }

    public function extractByDuration($videoPath, $id): void
    {
        $imagePath = (config('app.env') != 'production' ? '' : 'test-') . "frames/" . $id . '.jpg';
        $media    = FFMpeg::openUrl($videoPath);
        $duration = $media->getDurationInSeconds();
        $timestamp = max(0, (int) floor($duration / 2));

        $media->getFrameFromSeconds($timestamp)
            ->export()
            ->toDisk('gcs')
            ->save($imagePath);
    }
}
