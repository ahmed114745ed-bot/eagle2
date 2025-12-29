<?php

namespace Modules\DynamicTheme\Http\Controllers\Api\Admin;

use Modules\DynamicTheme\Http\Controllers\Controller;
use Modules\DynamicTheme\Entities\LibraryAsset;
use Modules\DynamicTheme\Entities\ThemeAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LibraryAssetController extends Controller
{
 public function index(Request $request)
    {
        $query = ThemeAsset::query();

        if ($request->filled('search')) {
            $query->where('asset_label', 'like', '%' . $request->search . '%')
                  ->orWhere('asset_key', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('type')) {
            $query->where('asset_type', $request->type);
        }

        $assets = $query->paginate(12);

        return response()->json($assets);
    }

    public function show($id)
    {
        $asset = LibraryAsset::findOrFail($id);
        return response()->json(['data' => $asset]);
    }


}
