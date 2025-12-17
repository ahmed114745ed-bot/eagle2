<?php

namespace Modules\Reals\Http\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\JpegEncoder;
use Illuminate\Support\Str;


class InterventionImage
{

    // public function combineImages(array $paths)
    // {
    //     $manager = new ImageManager(new Driver());

    //     $images = [];

    //     foreach ($paths as $path) {
    //         $img = $this->readImage($path);
    //         if ($img) {
    //             $images[] = $img->resize(300, 300);
    //         }
    //     }

    //     if (empty($images)) {
    //         return null;
    //     }

    //     $count   = count($images);
    //     $columns = ceil(sqrt($count));
    //     $rows    = ceil($count / $columns);

    //     $canvas = $manager->create($columns * 300, $rows * 300);

    //     foreach ($images as $i => $img) {
    //         $canvas->place(
    //             $img,
    //             'top-left',
    //             ($i % $columns) * 300,
    //             floor($i / $columns) * 300
    //         );
    //     }

    //     // Generate random filename
    //     $fileName = 'merged_' . Str::random(16) . '.png';

    //     // Get image content in memory
    //     $imageContent = (string) $canvas->toPng();


    //     // Upload to GCS (or other disk)
    //     Storage::disk('gcs')->put('merged/' . $fileName, $imageContent, 'public');


    //     return 'merged/' . $fileName;
    // }

public function combineImages(array $paths, $maxTileSize = 100)
{
    $manager = new ImageManager(new Driver());
    $images = [];

    // 1️⃣ Read and resize images
    foreach ($paths as $path) {
        $img = $this->readImage($path);
        if ($img) {
            // Resize to maxTileSize keeping aspect ratio
            $img->resize($maxTileSize, $maxTileSize, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $images[] = $img;
        }
    }

    if (empty($images)) {
        return null;
    }

    $count = count($images);
    $columns = ceil(sqrt($count));
    $rows = ceil($count / $columns);

    // 2️⃣ Calculate canvas size based on resized images
    $tileWidth = max(array_map(fn($img) => $img->width(), $images));
    $tileHeight = max(array_map(fn($img) => $img->height(), $images));

    $canvasWidth = $columns * $tileWidth;
    $canvasHeight = $rows * $tileHeight;

    $canvas = $manager->create($canvasWidth, $canvasHeight);


    // 3️⃣ Place images
    foreach ($images as $i => $img) {
        $x = ($i % $columns) * $tileWidth;
        $y = floor($i / $columns) * $tileHeight;
        $canvas->place($img, 'top-left', $x, $y);
    }

    // 4️⃣ Generate filename and save
    $fileName = 'merged_' . Str::random(16) . '.jpg';
    $imageContent = (string) $canvas->encode(new JpegEncoder(quality: 70));
 // smaller file

    Storage::disk('gcs')->put('merged/' . $fileName, $imageContent, ['visibility' => 'public']);

    return 'merged/' . $fileName;
}






    public function readImage(string $pathOrUrl)
    {
        $manager = new ImageManager(new Driver());

        // 1️⃣ Local file
        if (file_exists($pathOrUrl)) {
            return $manager->read($pathOrUrl);
        }

        // 2️⃣ asset() URL
        if (str_starts_with($pathOrUrl, asset(''))) {
            $local = public_path(parse_url($pathOrUrl, PHP_URL_PATH));
            return file_exists($local) ? $manager->read($local) : null;
        }

        // 3️⃣ Google Cloud Storage URL
        if (str_contains($pathOrUrl, 'storage.googleapis.com')) {

            // Extract object path
            $objectPath = ltrim(parse_url($pathOrUrl, PHP_URL_PATH), '/');
            $objectPath = preg_replace('#^[^/]+/#', '', $objectPath);

            if (!Storage::disk('gcs')->exists($objectPath)) {
                return null;
            }

            // Read binary directly from GCS
            $binary = Storage::disk('gcs')->get($objectPath);

            return $manager->read($binary);
        }

        // // 4️⃣ Any remote URL (CDN, S3, etc.)
        // if (filter_var($pathOrUrl, FILTER_VALIDATE_URL)) {

        //     $response = Http::timeout(10)->get($pathOrUrl);
        //     if (!$response->successful()) {
        //         return null;
        //     }

        //     return $manager->read($response->body());
        // }

        return null;
    }
}
