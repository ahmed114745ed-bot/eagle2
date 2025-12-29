<?php

namespace Modules\DynamicTheme\Http\Controllers\Api\Admin;

use Modules\DynamicTheme\Http\Controllers\Controller;
use Modules\DynamicTheme\Imports\WidgetsImport;
use Modules\DynamicTheme\Imports\ThemesImport;
use Modules\DynamicTheme\Exports\WidgetsExport;
use Modules\DynamicTheme\Exports\ThemesExport;
use Modules\DynamicTheme\Exports\WidgetsTemplateExport;
use Modules\DynamicTheme\Exports\ThemesTemplateExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AdminImportExportController extends Controller
{
    /**
     * Import data from Excel file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
            'type' => 'required|in:widgets,themes',
            'replace_existing' => 'boolean',
        ]);

        $type = $request->input('type');
        $replaceExisting = $request->boolean('replace_existing');

        try {
            if ($type === 'widgets') {
                $import = new WidgetsImport($replaceExisting);
                Excel::import($import, $request->file('file'));

                return response()->json([
                    'message' => 'Widgets imported successfully',
                    'created' => $import->getCreatedCount(),
                    'updated' => $import->getUpdatedCount(),
                    'skipped' => $import->getSkippedCount(),
                ]);
            } elseif ($type === 'themes') {
                $import = new ThemesImport($replaceExisting);
                Excel::import($import, $request->file('file'));

                return response()->json([
                    'message' => 'Themes imported successfully',
                    'created' => $import->getCreatedCount(),
                    'updated' => $import->getUpdatedCount(),
                    'skipped' => $import->getSkippedCount(),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Import failed',
                'error' => $e->getMessage(),
            ], 422);
        }

        return response()->json(['message' => 'Invalid import type'], 400);
    }

    /**
     * Export widgets to Excel.
     */
    public function exportWidgets()
    {
        return Excel::download(new WidgetsExport, 'widgets_export_' . date('Y-m-d_His') . '.xlsx');
    }

    /**
     * Export themes to Excel.
     */
    public function exportThemes()
    {
        return Excel::download(new ThemesExport, 'themes_export_' . date('Y-m-d_His') . '.xlsx');
    }

    /**
     * Download import template.
     */
    public function downloadTemplate(string $type)
    {
        if ($type === 'widgets') {
            return Excel::download(new WidgetsTemplateExport, 'widgets_template.xlsx');
        } elseif ($type === 'themes') {
            return Excel::download(new ThemesTemplateExport, 'themes_template.xlsx');
        }

        return response()->json(['message' => 'Invalid template type'], 400);
    }
}
