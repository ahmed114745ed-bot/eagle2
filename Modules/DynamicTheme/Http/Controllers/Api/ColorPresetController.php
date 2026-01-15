<?php

namespace Modules\DynamicTheme\Http\Controllers\Api;

use Modules\DynamicTheme\Http\Controllers\Controller;
use Modules\DynamicTheme\Entities\ColorPreset;
use Modules\DynamicTheme\Entities\ClientConfiguration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ColorPresetController extends Controller
{
    /**
     * Get all color presets for a configuration
     * 
     * @param int $configurationId
     * @return JsonResponse
     */
    public function indexByConfiguration(int $configurationId): JsonResponse
    {
        try {
            $presets = ColorPreset::where('configuration_id', $configurationId)
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'presets' => $presets,
                'total' => $presets->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load presets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific preset
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $preset = ColorPreset::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'preset' => $preset,
                'css_variables' => $preset->toCSSVariables()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Preset not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create new color preset
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'configuration_id' => 'required|integer|exists:client_configurations,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'colors' => 'required|array',
                'is_default' => 'nullable|boolean'
            ]);

            // If setting as default, unset other defaults
            if ($validated['is_default'] ?? false) {
                ColorPreset::where('configuration_id', $validated['configuration_id'])
                    ->update(['is_default' => false]);
            }

            $preset = ColorPreset::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Color preset created successfully',
                'preset' => $preset
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create preset',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update preset
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $preset = ColorPreset::findOrFail($id);

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'colors' => 'nullable|array',
                'is_default' => 'nullable|boolean'
            ]);

            if ($validated['is_default'] ?? false) {
                ColorPreset::where('configuration_id', $preset->configuration_id)
                    ->where('id', '!=', $id)
                    ->update(['is_default' => false]);
            }

            $preset->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Preset updated successfully',
                'preset' => $preset
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update preset',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete preset
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $preset = ColorPreset::findOrFail($id);
            $preset->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Preset deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete preset',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get default preset for configuration
     * 
     * @param int $configurationId
     * @return JsonResponse
     */
    public function getDefault(int $configurationId): JsonResponse
    {
        try {
            $preset = ColorPreset::where('configuration_id', $configurationId)
                ->where('is_default', true)
                ->first();

            if (!$preset) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No default preset found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'preset' => $preset
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get default preset',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export presets as JSON
     * 
     * @param int $configurationId
     * @return JsonResponse
     */
    public function export(int $configurationId): JsonResponse
    {
        try {
            $presets = ColorPreset::where('configuration_id', $configurationId)->get();
            $data = $presets->map(fn($p) => $p->export())->toArray();

            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to export presets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import presets from JSON
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function import(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'configuration_id' => 'required|integer|exists:client_configurations,id',
                'data' => 'required|array'
            ]);

            $imported = [];
            foreach ($validated['data'] as $presetData) {
                $preset = ColorPreset::create([
                    'configuration_id' => $validated['configuration_id'],
                    'name' => $presetData['name'] ?? 'Imported Preset',
                    'description' => $presetData['description'] ?? null,
                    'colors' => $presetData['colors'] ?? [],
                ]);
                $imported[] = $preset;
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Presets imported successfully',
                'presets' => $imported,
                'count' => count($imported)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to import presets',
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
