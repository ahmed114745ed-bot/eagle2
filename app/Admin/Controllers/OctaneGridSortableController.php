<?php

namespace App\Admin\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OctaneGridSortableController extends Controller
{
    public function sort(Request $request)
    {
        $sorts = $request->get('_sort');
        
        Log::info('🔍 Octane Grid Sortable - RAW Request', [
            'sorts_raw' => $sorts,
            'model' => $request->get('_model')
        ]);

        // Get the column name from the first model
        $modelClass = $request->get('_model');
        $tempModel = new $modelClass;
        $column = data_get($tempModel->sortable, 'order_column_name', 'order');
        
        Log::info('📋 Sort column detected', [
            'column' => $column,
            'sortable_config' => $tempModel->sortable
        ]);

        // IMPORTANT: The array comes in the NEW order after drag & drop
        // Map each item to its new position (1-based index)
        $sortMapping = collect($sorts)->mapWithKeys(function ($item, $index) {
            return [$item['key'] => $index + 1];
        });

        Log::info('🗺️ Sort Mapping Created', [
            'mapping' => $sortMapping->toArray()
        ]);

        $status     = true;
        $message    = trans('admin.save_succeeded');

        try {
            // CRITICAL: Clear all caches BEFORE reading from database (Octane fix)
            Cache::flush();
            \Artisan::call('cache:clear');
            
            // Clear model cache if using any
            if (method_exists($modelClass, 'flushCache')) {
                $modelClass::flushCache();
            }
            
            DB::beginTransaction();
            
            // Force fresh query from database (bypass Octane cache)
            $models = $modelClass::whereIn(
                $tempModel->getKeyName(), 
                $sortMapping->keys()->toArray()
            )->get();
            
            Log::info('📦 Models fetched from DB', [
                'count' => $models->count(),
                'ids' => $models->pluck($tempModel->getKeyName())->toArray()
            ]);

            foreach ($models as $model) {
                $newSortValue = $sortMapping->get($model->getKey());
                
                Log::info('💾 Updating model', [
                    'id' => $model->getKey(),
                    'old_sort' => $model->{$column},
                    'new_sort' => $newSortValue,
                    'column' => $column
                ]);
                
                // Direct DB update to bypass Eloquent events and Octane cache
                DB::table($model->getTable())
                    ->where($model->getKeyName(), $model->getKey())
                    ->update([
                        $column => $newSortValue,
                        'updated_at' => now()
                    ]);
            }
            
            DB::commit();
            
            // CRITICAL: Clear all caches AFTER update (Octane fix)
            Cache::flush();
            \Artisan::call('cache:clear');
            
            // Clear opcache if available (for Octane)
            if (function_exists('opcache_reset')) {
                @opcache_reset();
            }
            
            // Clear PHP realpath cache (for Octane)
            if (function_exists('clearstatcache')) {
                clearstatcache(true);
            }
            
            Log::info('✅ Octane Grid Sortable Success', [
                'updated_count' => $models->count(),
                'final_mapping' => $sortMapping->toArray()
            ]);
            
        } catch (Exception $exception) {
            DB::rollBack();
            
            $status  = false;
            $message = $exception->getMessage();
            
            Log::error('❌ Octane Grid Sortable Failed', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
        }

        return response()->json(compact('status', 'message'));
    }
}
