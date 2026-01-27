<?php

namespace Utd\Room\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Utd\Room\Services\RoomCategoryService;
use Utd\Room\Transformers\RoomCategoryResource;

class RoomCategoryController extends Controller
{
    public function __construct(
        protected RoomCategoryService $categoryService
    ) {
    }

    /**
     * Get all categories
     */
    public function index(): JsonResponse
    {
        $categories = $this->categoryService->index();

        return response()->json([
            'status' => true,
            'message' => 'Success',
            'data' => RoomCategoryResource::collection($categories),
        ]);
    }

    /**
     * Get categories by type
     */
    public function byType(): JsonResponse
    {
        $categories = $this->categoryService->getByType();

        return response()->json([
            'status' => true,
            'message' => 'Success',
            'data' => RoomCategoryResource::collection($categories),
        ]);
    }

    /**
     * Get category details
     */
    public function show($id): JsonResponse
    {
        $category = $this->categoryService->findById($id);

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Success',
            'data' => new RoomCategoryResource($category),
        ]);
    }
}
