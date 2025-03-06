<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StorageUploadController extends Controller
{
    public function chatVideo(Request $request){
         // Validate the request
         $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'folder' => 'nullable|string'
        ]);

        $filename = $request->input('name');
        $filetype = $request->input('type');
        $folder = $request->folder ?? 'pre-sign';

        // Generate a unique file name to avoid conflicts
        $uniqueFilename = $folder . '/' . Str::uuid() . '_' . $filename;

        // Initialize Google Cloud Storage client
        $storage = new StorageClient([
            'keyFilePath' => config('app.google_cloud_file'),
        ]);
        $bucket = $storage->bucket(config('app.google_cloud_storage_bucket'));

        // Generate a pre-signed URL with optional metadata
        $object = $bucket->object($uniqueFilename);

        $url = $object->signedUrl(
            now()->addMinutes(15),
            [
                'method' => 'PUT',
                'contentType' => $filetype
            ]
        );

        $deleteUrl = $object->signedUrl(now()->addMinutes(30), [
            'method' => 'DELETE',
        ]);

        return response()->json([
            'upload_url' => $url,
            'delete_url' => $deleteUrl,
            'name' => $uniqueFilename,
        ]);
    }
}
