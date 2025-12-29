<?php

namespace Modules\DynamicTheme\Http\Controllers\Api\Admin;

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

        // Determine folder based on asset type
        $folder = 'assets/' . $assetType . 's';

        // Generate unique filename
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Store file in public disk
        $path = $file->storeAs($folder, $filename, 'public');

        // Generate public URL
        $url = asset('storage/' . $path);

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

        // Determine folder based on asset type
        $folder = 'assets/' . $asset->asset_type . 's';

        // Generate unique filename
        $filename = time() . '_' . $asset->asset_key . '.' . $file->getClientOriginalExtension();

        // Delete old file if exists
        if ($asset->default_url && Storage::disk('public')->exists($asset->default_url)) {
            Storage::disk('public')->delete($asset->default_url);
        }

        // Store file in public disk
        $path = $file->storeAs($folder, $filename, 'public');

        // Generate public URL
        $url = asset('storage/' . $path);

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
        if ($asset->file_path && Storage::disk('public')->exists($asset->file_path)) {
            Storage::disk('public')->delete($asset->file_path);
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
