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
        
        Log::info('Octane Grid Sortable Request - RAW', [
            'sorts_raw' => $sorts,
            'model' => $request->get('_model')
        ]);

        // IMPORTANT: Don't sort the values! They come in the correct order from drag & drop
        // Just map key => sort value based on the array ORDER (index position)
        $sortMapping = collect($sorts)->mapWithKeys(function ($item, $index) {
            // $index is the NEW position after drag (0-based)
            // We need to convert it to 1-based sort value
            return [$item['key'] => $index + 1];
        });

        Log::info('Octane Grid Sortable - Mapping', [
            'mapping' => $sortMapping->toArray()
        ]);

        $status     = true;
        $message    = trans('admin.save_succeeded');
        $modelClass = $request->get('_model');

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
                (new $modelClass)->getKeyName(), 
                $sortMapping->keys()->toArray()
            )->get();

            foreach ($models as $model) {
                $column = data_get($model->sortable, 'order_column_name', 'order');

                // Direct DB update to bypass Eloquent events and Octane cache
                DB::table($model->getTable())
                    ->where($model->getKeyName(), $model->getKey())
                    ->update([
                        $column => $sortMapping->get($model->getKey()),
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
            
            Log::info('Octane Grid Sortable Success', [
                'updated_count' => $models->count(),
                'final_mapping' => $sortMapping->toArray()
            ]);
            
        } catch (Exception $exception) {
            DB::rollBack();
            
            $status  = false;
            $message = $exception->getMessage();
            
            Log::error('Octane Grid Sortable Failed', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
        }

        return response()->json(compact('status', 'message'));
    }
}
