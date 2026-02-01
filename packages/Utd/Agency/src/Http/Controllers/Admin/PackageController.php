<?php

namespace Utd\Agency\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\JsonResponse;

class PackageController extends Controller
{
    /**
     * Install the Agency package.
     *
     * @return JsonResponse
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Installation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Uninstall the Agency package.
     *
     * @return JsonResponse
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Uninstallation failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
