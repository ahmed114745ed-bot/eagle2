<?php

namespace Modules\DynamicTheme\Http\Controllers\Api\Admin;

use App\Helpers\Common;
use Modules\DynamicTheme\Http\Controllers\Controller;
use Modules\DynamicTheme\Entities\Asset;
use Modules\DynamicTheme\Entities\Screen;
use Modules\DynamicTheme\Entities\ScreenWidget;
use Modules\DynamicTheme\Entities\ThemeChild;
use Modules\DynamicTheme\Entities\WidgetTheme;
use Modules\DynamicTheme\Entities\ThemeAsset;
use Modules\DynamicTheme\Entities\LibraryAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminThemeController extends Controller
{
    /**
     * Display a listing of all themes.
     */
    public function index()
    {
        $themes = WidgetTheme::with(['widget', 'assets','children.assets'])
            ->orderBy('widget_id')
            ->orderBy('theme_name')
            ->get();

        return response()->json([
            'data' => $themes
        ]);
    }


       public function getByWidget($widgetId)
    {
        $configurationId = request()->query('configuration_id');
        
        $screenWidget =ScreenWidget::where('id', $widgetId)->first();
        if (!$screenWidget) {
            return response()->json([
                'data' => []
            ]);
        }
        $themes = WidgetTheme::where('widget_id', $screenWidget->widget_id)
                                ->with(['widget', 'children.assets'])
                                ->orderBy('widget_id')
                                ->orderBy('theme_name')
                                ->get();
        return response()->json([
            'data' => $themes
        ]);
    }
    /**
     * Store a newly created theme.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'widget_id' => 'required|exists:widgets,id',
            'theme_key' => 'required|string|max:100',
            'theme_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        // If this is set as default, unset other defaults for same widget
        if ($request->boolean('is_default')) {
            WidgetTheme::where('widget_id', $validated['widget_id'])
                ->update(['is_default' => false]);
        }

        $theme = WidgetTheme::create($validated);

        return response()->json([
            'message' => 'Theme created successfully',
            'data' => $theme->load(['widget', 'assets'])
        ], 201);
    }

    /**
     * Display the specified theme.
     */
    public function show(WidgetTheme $theme)
    {
        return response()->json([
            'data' => $theme->load(['widget', 'assets'])
        ]);
    }

    /**
     * Update the specified theme.
     */
    public function update(Request $request, WidgetTheme $theme)
    {
        $validated = $request->validate([
            'widget_id' => 'sometimes|exists:widgets,id',
            'theme_key' => 'sometimes|string|max:100',
            'theme_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        // If this is set as default, unset other defaults for same widget
        if ($request->boolean('is_default')) {
            WidgetTheme::where('widget_id', $theme->widget_id)
                ->where('id', '!=', $theme->id)
                ->update(['is_default' => false]);
        }

        $theme->update($validated);

        return response()->json([
            'message' => 'Theme updated successfully',
            'data' => $theme->fresh()->load(['widget', 'assets'])
        ]);
    }

    /**
     * Remove the specified theme.
     */
    public function destroy(WidgetTheme $theme)
    {
        $theme->delete();

        return response()->json([
            'message' => 'Theme deleted successfully'
        ]);
    }

    /**
     * Add an asset to a theme.
     */
    public function addAsset(Request $request, $childId)
    {
        // Find child to associate theme_id
        $child = ThemeChild::find($childId);

        $validated = $request->validate([
            'asset_key' => 'required|string|max:100',
            'input_type' => 'required|in:file,text',
            'asset_type' => 'required|in:image,svga,vap,alpha',
            'asset_label' => 'nullable|string|max:255',
            'default_url' => 'nullable|string',
            'description' => 'nullable|string',
            'child_id' => 'nullable',
            'type' => 'nullable|string|max:50',
            'text' => 'nullable|string',
            'max_size' => 'nullable|numeric',
            'file' => 'nullable|file',
        ]);

        // If a file is uploaded, validate and store it
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $maxMb = $request->input('max_size') ? (int)$request->input('max_size') : 5;
            $disk = config('filesystems.default');

            // additional validation based on asset_type
            if ($validated['asset_type'] === 'image') {
                $request->validate(['file' => 'image|max:' . ($maxMb * 1024)]);
            } else {
                $request->validate(['file' => 'file|max:' . ($maxMb * 1024)]);
            }

            $folder = "theme-assets/{$validated['asset_type']}";
            $path = Common::upload($folder, $file, $disk);

            $validated['file_path'] = $path;
            $validated['default_url'] = Storage::disk($disk)->url($path);
            $validated['original_filename'] = $file->getClientOriginalName();
        }
        
        $validated['type'] = $request->input('input_type');

        if ($request->input_type == "text") {
            $validated['text'] = $request->input('default_url');
        }


        // Associate child_id and theme_id when available
        $validated['child_id'] = $childId;
        if ($child) {
            $validated['theme_id'] = $child->theme_id;
        }

        $asset = ThemeAsset::create($validated);

        return response()->json([
            'message' => 'Asset added successfully',
            'data' => $asset
        ], 201);
    }

    // Upload/replace file for existing ThemeAsset
    public function uploadAssetFile(Request $request, $assetId)
    {
        $asset = ThemeAsset::findOrFail($assetId);

        $request->validate([
            'file' => 'required|file',
        ]);

        $file = $request->file('file');
        $disk = config('filesystems.default');

        // choose folder by asset_type
        $type = $asset->asset_type ?? 'file';
        $folder = "theme-assets/{$type}";
        $path = Common::upload($folder, $file, $disk);

        // delete previous file if exists
        if ($asset->file_path && Storage::disk($disk)->exists($asset->file_path)) {
            Storage::disk($disk)->delete($asset->file_path);
        }

        $asset->update([
            'file_path' => $path,
            'default_url' => Storage::disk($disk)->url($path),
            'original_filename' => $file->getClientOriginalName(),
        ]);

        return response()->json([
            'message' => 'File uploaded',
            'data' => $asset
        ]);
    }

    public function deleteAssetFile(Request $request, $assetId)
    {
        $asset = ThemeAsset::findOrFail($assetId);

        $disk = config('filesystems.default');

        if ($asset->file_path && Storage::disk($disk)->exists($asset->file_path)) {
            Storage::disk($disk)->delete($asset->file_path);
        }

        $asset->update([
            'file_path' => null,
            'default_url' => null,
            'original_filename' => null,
        ]);

        return response()->json(['message' => 'File removed', 'data' => $asset]);
    }

    /**
     * Update an asset.
     */
    public function updateAsset(Request $request, WidgetTheme $theme, ThemeAsset $asset)
    {
    
        $validated = $request->validate([
            'asset_key' => 
                    'required
                    |string
                    |max:100
                    |'.Rule::unique('theme_assets')->ignore($asset->id),
            'asset_type' => 'sometimes|in:image,svga,vap,alpha',
            'asset_label' => 'nullable|string|max:255',
            'default_url' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        
        $asset->update($validated);

        return response()->json([
            'message' => 'Asset updated successfully',
            'data' => $asset
        ]);
    }
public function updateAssetDashboard(Request $request, WidgetTheme $theme, ThemeAsset $asset)
    {
        $validated = $request->validate([
            'asset_key'   => ['required','string','max:100', Rule::unique('theme_assets')->ignore($asset->id)],
            'input_type'  => 'required|in:file,text',
            'asset_type'  => 'required_if:input_type,file|in:image,svga,vap,alpha',
            'asset_label' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'text'        => 'nullable|string',
            'file_path'   => 'nullable|string',
            'file'        => 'nullable|file',
            'max_size'   => 'nullable|numeric',
        ]);

      $maxMb = $asset->max_size ?? 5;

        if ($validated['input_type'] === 'text') {

            $validated['text'] = $request->input('text');
            $validated['file_path'] = null;

        }

        if ($validated['input_type'] === 'file') {

            $validated['text'] = null;

         
            if ($request->hasFile('file')) {

                $file = $request->file('file');

                if ($validated['asset_type'] === 'image') {
                    $request->validate([
                        'file' => 'image|max:' . ($maxMb * 1024),
                    ]);
                } else {
                    $request->validate([
                        'file' => 'file|max:' . ($maxMb * 1024),
                    ]);
                }

                $disk = config('filesystems.default');
                $path = Common::upload("theme-assets/{$validated['asset_type']}", $file, $disk);

                $validated['file_path'] = $path;
                $validated['default_url'] = Storage::disk($disk)->url($path);
                $validated['original_filename'] = $file->getClientOriginalName();
            }

    
            elseif ($request->filled('file_path')) {

                $validated['file_path'] = $request->input('file_path');
            }
        }


        $validated['child_id'] = $asset->child_id;
        $validated['theme_id'] = $asset->theme_id;
        $validated['type'] = $validated['input_type'];

        $asset->update($validated);

        return response()->json([
            'message' => 'Asset updated successfully from Dashboard',
            'data' => $asset
        ]);
    }


    /**
     * Delete an asset.
     */
    public function deleteAsset(WidgetTheme $theme, ThemeAsset $asset)
    {
        $asset->delete();

        return response()->json([
            'message' => 'Asset deleted successfully'
        ]);
    }


     public function theme_children(Request $request)
    {
        $data = $request->validate([
            'theme_id'   => 'required|exists:widget_themes,id',
            'child_key'  => 'required|string|max:255',
            'label'      => 'required|string|max:255',
            'child_type' => 'required|in:tab,category,special',
            'action'     => 'nullable|string|max:255',
            'is_visible' => 'boolean',
            'is_active'  => 'boolean',
            'position'   => 'nullable|in:left,right',
            
        ]);

        $child = ThemeChild::create($data);

        return response()->json($child);
    }

    public function UpdateThemeChild(Request $request, $id): JsonResponse
    {
        $themeChild = ThemeChild::findOrFail($id);

        $data = $request->validate([
            'theme_id' => 'sometimes|exists:widget_themes,id',
            'child_key' => 'sometimes|string|max:255',
            'label' => 'sometimes|string|max:255',
            'child_type' => 'sometimes|in:tab,category,special',
            'action' => 'nullable|string|max:255',
            'is_visible' => 'boolean',
            'is_active' => 'boolean',
            'position' => 'nullable|in:left,right',
        ]);

        $themeChild->update($data);

        return response()->json($themeChild);
    }
   public function assetByChiId( $id): JsonResponse
    {
        $assets = ThemeAsset::where('child_id', $id)->orderBy('created_at', 'desc')->paginate(50);
        return response()->json($assets);

    }

    /**
     * Create a ThemeAsset by copying a LibraryAsset into a child
     */
    public function addAssetFromLibrary(Request $request, $childId)
    {
        $request->validate([
            'library_asset_id' => 'required|exists:library_assets,id',
            'asset_key' => 'nullable|string|max:100',
            'asset_label' => 'nullable|string|max:255',
        ]);

        $library = LibraryAsset::findOrFail($request->input('library_asset_id'));

        // find child & theme
        $child = ThemeChild::find($childId);

        $data = [
            'asset_key' => $request->input('asset_key') ?? $library->asset_key,
            'asset_label' => $request->input('asset_label') ?? $library->asset_label,
            'asset_type' => $library->asset_type,
            'description' => $library->description ?? null,
            'file_path' => null,
            'default_url' => $library->default_url,
            'original_filename' => $library->original_filename,
            'max_size' => null,
            'type' => 'file',
            'child_id' => $childId,
        ];

        if ($child) {
            $data['theme_id'] = $child->theme_id;
        }

        // Optionally copy file to new location so ThemeAsset owns its own copy
        if ($library->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($library->file_path)) {
            $ext = pathinfo($library->file_path, PATHINFO_EXTENSION) ?: 'bin';
            $filename = \Illuminate\Support\Str::uuid() . '.' . $ext;
            $newPath = "theme-assets/{$library->asset_type}/{$filename}";
            \Illuminate\Support\Facades\Storage::disk('public')->copy($library->file_path, $newPath);
            $data['file_path'] = $newPath;
            $data['default_url'] = asset('storage/' . $newPath);
        }

        $asset = ThemeAsset::create($data);

        return response()->json(['message' => 'Asset created from library', 'data' => $asset], 201);
    }

    public function deleteThemeChild($id): JsonResponse
    {
        $themeChild = ThemeChild::findOrFail($id);
        $themeChild->delete();

        return response()->json(['message' => 'Theme child deleted successfully.']);
    }

      public function HideThemeChild($id): JsonResponse
    {
      $themeChild = ThemeChild::findOrFail($id);

        $themeChild->hide = $themeChild->hide ? 0 : 1;
        $themeChild->save();

        $status = $themeChild->hide ? 'hidden' : 'visible';

        return response()->json([
            'message' => "Theme child is now $status.",
            'hide' => $themeChild->hide
        ]);
    }
    
    
}
