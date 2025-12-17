<?php

namespace Modules\Reals\Http\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
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

    public function combineImages(array $paths)
    {
        $manager = new ImageManager(new Driver());
        $images = [];

        // 1️⃣ Read images and store their dimensions
        foreach ($paths as $path) {
            $img = $this->readImage($path);
            if ($img) {
                $images[] = $img;
            }
        }

        if (empty($images)) {
            return null;
        }

        $count = count($images);
        $columns = ceil(sqrt($count));
        $rows = ceil($count / $columns);

        // 2️⃣ Determine minimal width and height per tile
        $tileWidths = array_map(fn($img) => $img->width(), $images);
        $tileHeights = array_map(fn($img) => $img->height(), $images);

        // Use the **max width/height per row/column**
        $tileWidth = max($tileWidths);
        $tileHeight = max($tileHeights);

        // 3️⃣ Calculate minimal canvas size
        $canvasWidth = ($columns > 1 ? ($columns - 1) * $tileWidth : $tileWidth) + $tileWidth;
        $canvasHeight = ($rows > 1 ? ($rows - 1) * $tileHeight : $tileHeight) + $tileHeight;

        $canvas = $manager->canvas($canvasWidth, $canvasHeight);

        // 4️⃣ Place images
        foreach ($images as $i => $img) {
            $x = ($i % $columns) * $tileWidth;
            $y = floor($i / $columns) * $tileHeight;
            $canvas->insert($img, 'top-left', $x, $y);
        }

        // 5️⃣ Generate filename and save
        $fileName = 'merged_' . Str::random(16) . '.png';
        $imageContent = (string) $canvas->encode('png', 80); // you can reduce quality for smaller file

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
