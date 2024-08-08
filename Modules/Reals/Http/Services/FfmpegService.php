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
}
