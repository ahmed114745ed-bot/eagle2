<?php

namespace Modules\DynamicTheme\Http\Controllers\Api;

use Modules\DynamicTheme\Http\Controllers\Controller;
use Modules\DynamicTheme\Entities\WidgetDesignTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DesignTemplateController extends Controller
{
    /**
     * Get all design templates for a configuration
     * 
     * @param int $configurationId
     * @return JsonResponse
     */
    public function indexByConfiguration(int $configurationId): JsonResponse
    {
        try {
            $templates = WidgetDesignTemplate::where('configuration_id', $configurationId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'templates' => $templates,
                'total' => $templates->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load templates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get public templates (shared)
     * 
     * @return JsonResponse
     */
    public function getPublic(): JsonResponse
    {
        try {
            $templates = WidgetDesignTemplate::where('is_public', true)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'templates' => $templates,
                'total' => $templates->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load public templates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific template
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $template = WidgetDesignTemplate::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'template' => $template
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Template not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create new design template
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
                'design_config' => 'required|array',
                'preview_data' => 'nullable|array',
                'is_public' => 'nullable|boolean'
            ]);

            $template = WidgetDesignTemplate::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Design template created successfully',
                'template' => $template
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create template',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update template
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $template = WidgetDesignTemplate::findOrFail($id);

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'design_config' => 'nullable|array',
                'preview_data' => 'nullable|array',
                'is_public' => 'nullable|boolean'
            ]);

            $template->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Template updated successfully',
                'template' => $template
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update template',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete template
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $template = WidgetDesignTemplate::findOrFail($id);
            $template->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Template deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export template
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function export(int $id): JsonResponse
    {
        try {
            $template = WidgetDesignTemplate::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $template->export()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to export template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import template
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function import(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'configuration_id' => 'required|integer|exists:client_configurations,id',
                'template_data' => 'required|array'
            ]);

            $template = WidgetDesignTemplate::importTemplate(
                $validated['template_data'],
                $validated['configuration_id']
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Template imported successfully',
                'template' => $template
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to import template',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Duplicate template
     * 
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function duplicate(int $id, Request $request): JsonResponse
    {
        try {
            $template = WidgetDesignTemplate::findOrFail($id);
            
            $validated = $request->validate([
                'new_name' => 'nullable|string|max:255'
            ]);

            $duplicate = $template->replicate();
            $duplicate->name = $validated['new_name'] ?? $template->name . ' (Copy)';
            $duplicate->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Template duplicated successfully',
                'template' => $duplicate
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to duplicate template',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
