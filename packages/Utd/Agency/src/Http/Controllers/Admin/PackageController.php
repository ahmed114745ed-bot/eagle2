<?php

namespace Utd\Agency\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class PackageController extends Controller
{
    /**
     * Install the Agency package.
     */
    public function install(): JsonResponse
    {
        try {
            Artisan::call('agency:install', ['--force' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Agency package installed successfully.',
                'output' => Artisan::output(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Installation failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Uninstall the Agency package.
     */
    public function uninstall(): JsonResponse
    {
        try {
            Artisan::call('agency:uninstall', ['--force' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Agency package uninstalled successfully.',
                'output' => Artisan::output(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Uninstallation failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Debug installation status.
     */
    public function debug(): JsonResponse
    {
        $class = \Utd\Agency\Entities\Agency::class;
        $classExists = class_exists($class);
        $modelClassExists = class_exists(\App\Models\Agency::class);

        $tableExists = false;
        $tableCount = -1;
        try {
            $tableExists = \Illuminate\Support\Facades\Schema::hasTable('agencies');
            if ($tableExists) {
                $tableCount = \Illuminate\Support\Facades\DB::table('agencies')->count();
            }
        } catch (Exception $e) {
            $tableError = $e->getMessage();
        }

        $helperStatus = \App\Helpers\AgencyPackageHelper::isAgencyInstalled();

        return response()->json([
            'class_exists' => $classExists,
            'class_name' => $class,
            'app_model_exists' => $modelClassExists,
            'table_exists' => $tableExists,
            'table_count' => $tableCount,
            'helper_status' => $helperStatus,
            'error' => $tableError ?? null,
        ]);
    }
}
