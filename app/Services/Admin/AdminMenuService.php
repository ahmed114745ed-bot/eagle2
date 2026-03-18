<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Log;

/**
 * AdminMenuService
 *
 * Validates admin menu trees for circular references and infinite recursion.
 * Octane-safe: no static variables are used — all state is per-invocation.
 */
class AdminMenuService
{
    /**
     * Validate and sanitize a menu tree, removing circular references.
     *
     * @param  array  $items  The menu items array
     * @return array  Sanitized menu items
     */
    public function validateAndSanitize(array $items): array
    {
        return $this->validateMenu($items);
    }

    /**
     * Recursively validate menu items for circular references.
     *
     * @param  array  $items       Current level menu items
     * @param  array  $parentPath  IDs of all ancestors (to detect cycles)
     * @param  int    $depth       Current recursion depth
     * @param  int    $maxDepth    Maximum allowed depth
     * @return array  Cleaned menu items
     */
    private function validateMenu(array $items, array $parentPath = [], int $depth = 0, int $maxDepth = 10): array
    {
        if ($depth >= $maxDepth) {
            Log::warning('AdminMenuService: max depth reached while validating menu', [
                'depth'      => $depth,
                'parentPath' => $parentPath,
            ]);
            return [];
        }

        $cleaned = [];

        foreach ($items as $index => $item) {
            $itemId = $item['id'] ?? "item-{$index}";
            $currentPath = array_merge($parentPath, [$itemId]);

            // Check if this item's ID already appears in its ancestor path (circular)
            if (in_array($itemId, $parentPath)) {
                Log::error('AdminMenuService: circular menu reference detected and removed', [
                    'item_id'    => $itemId,
                    'item_title' => $item['title'] ?? 'unknown',
                    'path'       => $currentPath,
                ]);
                // Skip this item entirely — it's a cycle
                continue;
            }

            // Validate children recursively
            if (isset($item['children']) && is_array($item['children']) && !empty($item['children'])) {
                $childIds = array_filter(array_column($item['children'], 'id'));
                $intersect = array_intersect($parentPath, $childIds);

                if (!empty($intersect)) {
                    Log::error('AdminMenuService: circular reference in children detected', [
                        'parent_item' => $itemId,
                        'path'        => $currentPath,
                        'circular_ids' => array_values($intersect),
                    ]);
                    // Remove problematic children
                    $item['children'] = [];
                } else {
                    $item['children'] = $this->validateMenu(
                        $item['children'],
                        $currentPath,
                        $depth + 1,
                        $maxDepth
                    );
                }
            }

            $cleaned[] = $item;
        }

        return $cleaned;
    }
}
