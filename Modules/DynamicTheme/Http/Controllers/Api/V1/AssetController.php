<?php

namespace Modules\DynamicTheme\Http\Controllers\Api\V1;

use App\Helpers\Common;
use Modules\DynamicTheme\Http\Controllers\Controller;
use Modules\DynamicTheme\Entities\Asset;
use Modules\DynamicTheme\Entities\ThemeAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    /**
     * Get all assets
     */
    public function index(Request $request): JsonResponse
    {
        $query = Asset::query();

        // Filter by asset type
        if ($request->has('type')) {
            $query->where('asset_type', $request->type);
        }

        $assets = $query->orderBy('created_at', 'desc')->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $assets->items(),
            'meta' => [
                'current_page' => $assets->currentPage(),
                'last_page' => $assets->lastPage(),
                'per_page' => $assets->perPage(),
                'total' => $assets->total(),
            ],
        ]);
    }


     
    /**
     * Upload a new asset
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'asset_type' => 'required|in:image,svga,vap,alpha',
            'name' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $assetType = $request->asset_type;
        $disk = config('filesystems.default');
        $path = Common::upload("assets/{$assetType}", $file, $disk);
        $fileUrl = Storage::disk($disk)->url($path);

        // Create asset record
        $asset = Asset::create([
            'name' => $request->name ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_url' => $fileUrl,
            'asset_type' => $assetType,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'metadata' => $this->extractMetadata($file, $assetType),
        ]);

        return response()->json([
            'success' => true,
            'data' => $asset,
            'message' => 'Asset uploaded successfully',
        ], 201);
    }

        public function assetsDestroy(int $id): JsonResponse
    {
        $asset = ThemeAsset::find($id);

        if (!$asset) {
            return response()->json([
                'success' => false,
                'message' => 'Asset not found',
            ], 404);
        }

        $asset->delete();

        return response()->json([
            'success' => true,
            'message' => 'Asset deleted successfully',
        ]);
    }

    /**
     * Get asset by ID
     */
    public function show(int $id): JsonResponse
    {
        $asset = Asset::find($id);

        if (!$asset) {
            return response()->json([
                'success' => false,
                'message' => 'Asset not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $asset,
        ]);
    }

    /**
     * Delete an asset
     */
    public function destroy(int $id): JsonResponse
    {
        $asset = Asset::find($id);

        if (!$asset) {
            return response()->json([
                'success' => false,
                'message' => 'Asset not found',
            ], 404);
        }

        // Delete file from storage
        $disk = config('filesystems.default');

        if (Storage::disk($disk)->exists($asset->file_path)) {
            Storage::disk($disk)->delete($asset->file_path);
        }

        $asset->delete();

        return response()->json([
            'success' => true,
            'message' => 'Asset deleted successfully',
        ]);
    }

    /**
     * Extract metadata from uploaded file
     */
    private function extractMetadata($file, string $assetType): array
    {
        $metadata = [];

        if ($assetType === 'image') {
            $imageInfo = @getimagesize($file->getPathname());
            if ($imageInfo) {
                $metadata['width'] = $imageInfo[0];
                $metadata['height'] = $imageInfo[1];
            }
        }

        return $metadata;
    }
}
