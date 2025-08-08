<?php

namespace App\Admin\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Modules\Reals\Http\Services\FfmpegService;

class FileService
{
    /**
     * @return void
     *
     * @throws ValidationException
     */
    public static function getExtension(UploadedFile $img2, mixed $wareId, bool $getFromService = false): ?string
    {

        $urlVideo = upload($img2);

        $allowedExtensions = ['svga', 'mp4', 'alpha', 'vap', 'png'];
        $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'svg', 'heic', 'heif'];

        $ext = mb_strtolower($img2->guessExtension());
        $originalExt = mb_strtolower($img2->getClientOriginalExtension());

        if ($ext === 'zz' && $originalExt === 'svga') {
            $ext = 'svga';
        }

        // Default to original extension if allowed
        if (in_array($originalExt, $allowedImageTypes)) {
            $ext = 'png'; // Convert gif/webp to png

        }

        if ($ext === 'mp4' && $getFromService) {

            $videoPath = getDriverUrl().'/'.$urlVideo;

            (new FfmpegService())->extractByFrame($videoPath, $wareId);

            $imagePath = (config('app.env') !== 'production' ? '' : 'test-').'frames/'.$wareId.'.jpg';

            $response = Http::attach(
                'image',
                Storage::disk('gcs')->get($imagePath),
                $wareId.'.jpg'
            )->post('https://utd-test.utdsoftware.com/api/analyze-media');

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['data']['video_type'])) {
                $ext = mb_strtolower(explode('-', $responseData['data']['video_type'])[0]);
            }

        }
        if (! in_array($ext, $allowedExtensions)) {
            throw ValidationException::withMessages([
                'img2' => ['Invalid file type. Allowed extensions are: '.implode(', ', $allowedExtensions)],
            ]);
        }

        return $ext;
    }
}
