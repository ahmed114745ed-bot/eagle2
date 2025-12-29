<?php

namespace Modules\DynamicTheme\Http\Controllers\Api\Dashboard;

use Modules\DynamicTheme\Http\Controllers\Controller;
use Modules\DynamicTheme\Imports\WidgetsImport;
use Modules\DynamicTheme\Imports\ThemesImport;
use Modules\DynamicTheme\Exports\WidgetsExport;
use Modules\DynamicTheme\Exports\ThemesExport;
use Modules\DynamicTheme\Exports\WidgetsTemplateExport;
use Modules\DynamicTheme\Exports\ThemesTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Exception;

class ImportExportController extends Controller
{
    /**
     * Download widgets template Excel file
     */
    public function downloadWidgetsTemplate()
    {
        return Excel::download(new WidgetsTemplateExport(), 'widgets_template.xlsx');
    }

    /**
     * Download themes template Excel file
     */
    public function downloadThemesTemplate()
    {
        return Excel::download(new ThemesTemplateExport(), 'themes_template.xlsx');
    }

    /**
     * Export all widgets to Excel
     */
    public function exportWidgets()
    {
        return Excel::download(new WidgetsExport(), 'widgets_export_' . date('Y-m-d_His') . '.xlsx');
    }

    /**
     * Export all themes to Excel
     */
    public function exportThemes()
    {
        return Excel::download(new ThemesExport(), 'themes_export_' . date('Y-m-d_His') . '.xlsx');
    }

    /**
     * Import widgets from Excel file
     */
    public function importWidgets(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            Excel::import(new WidgetsImport(), $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Widgets imported successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import themes from Excel file
     */
    public function importThemes(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            Excel::import(new ThemesImport(), $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Themes imported successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate widgets Excel file without importing
     */
    public function validateWidgetsFile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = Excel::toArray(new WidgetsImport(), $request->file('file'));

            $summary = [
                'widgets_count' => isset($data['widgets']) ? count($data['widgets']) : 0,
                'settings_count' => isset($data['settings']) ? count($data['settings']) : 0,
                'actions_count' => isset($data['actions']) ? count($data['actions']) : 0,
            ];

            $errors = $this->validateWidgetsData($data);

            return response()->json([
                'success' => empty($errors),
                'message' => empty($errors) ? 'File is valid' : 'File has validation errors',
                'summary' => $summary,
                'errors' => $errors,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate themes Excel file without importing
     */
    public function validateThemesFile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = Excel::toArray(new ThemesImport(), $request->file('file'));

            $summary = [
                'themes_count' => isset($data['themes']) ? count($data['themes']) : 0,
                'assets_count' => isset($data['assets']) ? count($data['assets']) : 0,
            ];

            $errors = $this->validateThemesData($data);

            return response()->json([
                'success' => empty($errors),
                'message' => empty($errors) ? 'File is valid' : 'File has validation errors',
                'summary' => $summary,
                'errors' => $errors,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate widgets data
     */
    private function validateWidgetsData(array $data): array
    {
        $errors = [];

        // Validate widgets sheet
        if (isset($data['widgets'])) {
            foreach ($data['widgets'] as $index => $row) {
                $rowNum = $index + 2; // +2 for header and 0-index

                if (empty($row['widget_key'])) {
                    $errors[] = "Row {$rowNum} in widgets sheet: widget_key is required";
                }
                if (empty($row['widget_type'])) {
                    $errors[] = "Row {$rowNum} in widgets sheet: widget_type is required";
                }
                if (empty($row['display_name'])) {
                    $errors[] = "Row {$rowNum} in widgets sheet: display_name is required";
                }
            }
        }

        // Validate settings sheet
        if (isset($data['settings'])) {
            $widgetKeys = array_column($data['widgets'] ?? [], 'widget_key');

            foreach ($data['settings'] as $index => $row) {
                $rowNum = $index + 2;

                if (empty($row['widget_key'])) {
                    $errors[] = "Row {$rowNum} in settings sheet: widget_key is required";
                } elseif (!in_array($row['widget_key'], $widgetKeys)) {
                    $errors[] = "Row {$rowNum} in settings sheet: widget_key '{$row['widget_key']}' not found in widgets sheet";
                }

                if (empty($row['setting_key'])) {
                    $errors[] = "Row {$rowNum} in settings sheet: setting_key is required";
                }
            }
        }

        return $errors;
    }

    /**
     * Validate themes data
     */
    private function validateThemesData(array $data): array
    {
        $errors = [];

        // Validate themes sheet
        if (isset($data['themes'])) {
            foreach ($data['themes'] as $index => $row) {
                $rowNum = $index + 2;

                if (empty($row['theme_key'])) {
                    $errors[] = "Row {$rowNum} in themes sheet: theme_key is required";
                }
                if (empty($row['widget_key'])) {
                    $errors[] = "Row {$rowNum} in themes sheet: widget_key is required";
                }
                if (empty($row['theme_name'])) {
                    $errors[] = "Row {$rowNum} in themes sheet: theme_name is required";
                }
            }
        }

        // Validate assets sheet
        if (isset($data['assets'])) {
            $themeKeys = array_column($data['themes'] ?? [], 'theme_key');

            foreach ($data['assets'] as $index => $row) {
                $rowNum = $index + 2;

                if (empty($row['theme_key'])) {
                    $errors[] = "Row {$rowNum} in assets sheet: theme_key is required";
                } elseif (!in_array($row['theme_key'], $themeKeys)) {
                    $errors[] = "Row {$rowNum} in assets sheet: theme_key '{$row['theme_key']}' not found in themes sheet";
                }

                if (empty($row['asset_key'])) {
                    $errors[] = "Row {$rowNum} in assets sheet: asset_key is required";
                }
            }
        }

        return $errors;
    }
}
