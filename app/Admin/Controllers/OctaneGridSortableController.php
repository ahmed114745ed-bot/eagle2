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
        
        Log::info('Octane Grid Sortable Request', [
            'sorts' => $sorts,
            'model' => $request->get('_model')
        ]);

        $sorts = collect($sorts)
            ->pluck('key')
            ->combine(
                collect($sorts)->pluck('sort')->sort()
            );

        $status     = true;
        $message    = trans('admin.save_succeeded');
        $modelClass = $request->get('_model');

        try {
            // Clear cache before update (Octane compatibility)
            Cache::flush();
            
            DB::beginTransaction();
            
            /** @var \Illuminate\Database\Eloquent\Collection $models */
            $models = $modelClass::find($sorts->keys());

            foreach ($models as $model) {
                $column = data_get($model->sortable, 'order_column_name', 'order');

                $model->{$column} = $sorts->get($model->getKey());
                $model->timestamps = false; // Disable timestamps for sort updates
                $model->save();
            }
            
            DB::commit();
            
            // Clear cache after update (Octane compatibility)
            Cache::flush();
            
            // Clear opcache if available
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            
            Log::info('Octane Grid Sortable Success', [
                'updated_count' => $models->count()
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
