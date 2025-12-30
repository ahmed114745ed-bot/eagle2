<?php

namespace Modules\DynamicTheme\Http\Controllers\Api\Admin;

use App\Helpers\Common;
use Modules\DynamicTheme\Http\Controllers\Controller;
use Modules\DynamicTheme\Entities\ThemeAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssetUploadController extends Controller
{
    /**
     * Upload a default asset file for a theme asset.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'asset_type' => 'required|in:image,svga,vap,alpha',
        ]);

        $file = $request->file('file');
        $assetType = $request->input('asset_type');
        $disk = config('filesystems.default');

        // Determine folder based on asset type
        $folder = 'assets/' . $assetType . 's';
        $path = Common::upload($folder, $file, $disk);
        $filename = basename($path);
        $url = Storage::disk($disk)->url($path);

        return response()->json([
            'message' => 'File uploaded successfully',
            'path' => $path,
            'url' => $url,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);
    }

    /**
     * Upload and update a theme asset's default URL.
     */
    public function uploadForAsset(Request $request, ThemeAsset $asset)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $disk = config('filesystems.default');

        // Determine folder based on asset type
        $folder = 'assets/' . $asset->asset_type . 's';

        // Delete old file if exists
        if ($asset->file_path && Storage::disk($disk)->exists($asset->file_path)) {
            Storage::disk($disk)->delete($asset->file_path);
        }

        $path = Common::upload($folder, $file, $disk);
        $url = Storage::disk($disk)->url($path);

        // Update asset with new URL
        $asset->update([
            'default_url' => $url,
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
        ]);

        return response()->json([
            'message' => 'Asset file uploaded successfully',
            'data' => $asset->fresh(),
        ]);
    }

    /**
     * Delete an uploaded asset file.
     */
    public function delete(ThemeAsset $asset)
    {
        $disk = config('filesystems.default');

        if ($asset->file_path && Storage::disk($disk)->exists($asset->file_path)) {
            Storage::disk($disk)->delete($asset->file_path);
        }

        $asset->update([
            'default_url' => null,
            'file_path' => null,
            'original_filename' => null,
        ]);

        return response()->json([
            'message' => 'Asset file deleted successfully',
            'data' => $asset->fresh(),
        ]);
    }
}
