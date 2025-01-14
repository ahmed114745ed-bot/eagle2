<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UploadLinkController extends Controller
{
    public function uploadLink(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'size' => 'nullable|integer',
            'folder' => 'nullable|string'
        ]);

        $filename = $request->input('name');
        $filetype = $request->input('type');
        $size = $request->input('size'); // Optional
        $folder = 'pre-sign';

        if(!empty($request->folder)){
            $folder = $request->folder;
        }
        // Generate a unique file name to avoid conflicts
        $uniqueFilename = $folder . '/' . Str::uuid() . '_' . $filename;

        // Initialize Google Cloud Storage client
        $storage =  new StorageClient([
            'keyFilePath' => config('app.google_cloud_file'),
        ]);
        $bucket = $storage->bucket(config('app.google_cloud_storage_bucket'));

        // Generate a pre-signed URL
        $object = $bucket->object($uniqueFilename);

        $url = $object->signedUrl(
            now()->addMinutes(15), // URL expiration time (e.g., 15 minutes)
            [
                'method' => 'PUT', // HTTP method for upload
                'contentType' => $filetype, // Set the file type
            ]
        );

        return response()->json([
            'upload_url' => $url,
            'name' => $uniqueFilename,
        ]);
    }
}
