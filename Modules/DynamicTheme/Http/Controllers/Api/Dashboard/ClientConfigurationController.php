<?php

namespace Modules\DynamicTheme\Http\Controllers\Api\Dashboard;

use App\Helpers\Common;
use Modules\DynamicTheme\Http\Controllers\Controller;
use Modules\DynamicTheme\Entities\ClientConfiguration;
use Modules\DynamicTheme\Entities\ConfigChildAssetOverride;
use Modules\DynamicTheme\Entities\ConfigScreenOverride;
use Modules\DynamicTheme\Entities\ConfigThemeChildOverride;
use Modules\DynamicTheme\Entities\ConfigWidgetOverride;
use Modules\DynamicTheme\Entities\ConfigAssetOverride;
use Modules\DynamicTheme\Entities\Screen;
use Modules\DynamicTheme\Entities\ScreenWidget;
use Modules\DynamicTheme\Entities\ThemeAsset;
use Modules\DynamicTheme\Entities\ThemeChild;
use Modules\DynamicTheme\Entities\WidgetTheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClientConfigurationController extends Controller
{
    /**
     * List all configurations for a client
     */
    public function index(Request $request)
    {
        $clientId = $request->header('X-Client-ID', 'default');

        $configurations = ClientConfiguration::forClient($clientId)
            ->withCount(['screenOverrides', 'widgetOverrides', 'assetOverrides'])
            ->orderBy('is_active', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'data' => $configurations
        ]);
    }

    /**
     * Create a new configuration
     */
    public function store(Request $request)
    {
        $clientId = $request->header('X-Client-ID', 'default');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Check for duplicate name
        $exists = ClientConfiguration::forClient($clientId)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Configuration name already exists'
            ], 422);
        }

        $configuration = ClientConfiguration::create([
            'client_id' => $clientId,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => false,
        ]);

        // Initialize with default screen overrides
        $this->initializeScreenOverrides($configuration);
        // Initialize with default widget overrides (one per screen widget)
        $this->initializeWidgetOverrides($configuration);

        return response()->json([
            'message' => 'Configuration created successfully',
            'data' => $configuration->fresh()->load(['screenOverrides.screen', 'widgetOverrides', 'assetOverrides'])
        ], 201);
    }
    
    /**
     * Initialize widget overrides for a configuration (one override per screen widget)
     */
    private function initializeWidgetOverrides(ClientConfiguration $configuration): void
    {
        $screenWidgets = ScreenWidget::with('widget')->get();

        foreach ($screenWidgets as $sw) {
            ConfigWidgetOverride::updateOrCreate(
                [
                    'configuration_id' => $configuration->id,
                    'screen_widget_id' => $sw->id,
                ],
                [
                    'screen_id' => $sw->screen_id,
                    'is_visible' => true,
                    'display_order' => $sw->order,
                    'selected_theme_id' => $sw->widget_theme_id ?? null,
                    'primary_settings' => $sw->primary_settings ?? [],
                    'secondary_settings' => $sw->secondary_settings ?? [],
                    'action' => $sw->action ?? [],
                ]
            );
        }
    }

    /**
     * Get a single configuration with all overrides
     */
    public function show(ClientConfiguration $configuration)
    {
        $configuration->load([
            'screenOverrides.screen',
            'widgetOverrides.screenWidget.widget',
            'widgetOverrides.selectedTheme',
            // Include child overrides so the dashboard can restore per-child visibility state per configuration
            'widgetOverrides.themeChildOverrides.assetOverrides.asset',
            'assetOverrides.themeAsset',
        ]);

        return response()->json([
            'data' => $configuration
        ]);
    }

    /**
     * Update configuration details
     */
    public function update(Request $request, ClientConfiguration $configuration)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Check for duplicate name if name is being changed
        if (isset($validated['name']) && $validated['name'] !== $configuration->name) {
            $exists = ClientConfiguration::forClient($configuration->client_id)
                ->where('name', $validated['name'])
                ->where('id', '!=', $configuration->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Configuration name already exists'
                ], 422);
            }
        }

        $configuration->update($validated);

        return response()->json([
            'message' => 'Configuration updated successfully',
            'data' => $configuration
        ]);
    }

    /**
     * Delete a configuration
     */
    public function destroy(ClientConfiguration $configuration)
    {
        if ($configuration->is_active) {
            return response()->json([
                'message' => 'Cannot delete active configuration. Please activate another configuration first.'
            ], 422);
        }

        $configuration->delete();

        return response()->json([
            'message' => 'Configuration deleted successfully'
        ]);
    }

    /**
     * Clone a configuration
     */
    public function clone(Request $request, ClientConfiguration $configuration)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Check for duplicate name
        $exists = ClientConfiguration::forClient($configuration->client_id)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Configuration name already exists'
            ], 422);
        }

        $newConfiguration = $configuration->duplicate($validated['name']);

        return response()->json([
            'message' => 'Configuration cloned successfully',
            'data' => $newConfiguration
        ], 201);
    }

    /**
     * Activate a configuration
     */
    public function activate(ClientConfiguration $configuration)
    {
        $configuration->activate();

        return response()->json([
            'message' => 'Configuration activated successfully',
            'data' => $configuration
        ]);
    }

    /**
     * Update screen overrides for a configuration
     */
    public function updateScreenOverrides(Request $request, ClientConfiguration $configuration)
    {
        // Normalize incoming payloads:
        $input = $request->all();
        if (!isset($input['overrides'])) {
            if (isset($input['payload']['overrides']) && is_array($input['payload']['overrides'])) {
                $request->merge(['overrides' => $input['payload']['overrides']]);
            } else {
                $keys = array_keys($input);
                if ($keys === range(0, count($keys) - 1)) {
                    $request->merge(['overrides' => $input]);
                }
            }
        } else {
            $ov = $request->input('overrides');
            // If overrides was sent as { overrides: { screen_overrides: [...] } }, use the inner array
            if (is_array($ov) && isset($ov['screen_overrides']) && is_array($ov['screen_overrides'])) {
                $request->merge(['overrides' => $ov['screen_overrides']]);
            } elseif (is_array($ov) && array_key_exists('overrides', $ov) && is_array($ov['overrides'])) {
                $request->merge(['overrides' => $ov['overrides']]);
            }
        }

        $validated = $request->validate([
            'overrides' => 'required|array',
            'overrides.*.screen_id' => 'required|exists:screens,id',
            'overrides.*.is_visible' => 'boolean',
            'overrides.*.display_order' => 'integer',
        ]);

        DB::transaction(function () use ($configuration, $validated) {
            foreach ($validated['overrides'] as $screenData) {
                ConfigScreenOverride::updateOrCreate(
                    [
                        'configuration_id' => $configuration->id,
                        'screen_id' => $screenData['screen_id'],
                    ],
                    [
                        'is_visible' => $screenData['is_visible'] ?? true,
                        'display_order' => $screenData['display_order'] ?? 0,
                    ]
                );
            }
        });

        return response()->json([
            'message' => 'Screen overrides updated successfully',
            'data' => $configuration->fresh()->load('screenOverrides.screen')
        ]);
    }

    /**
     * Update widget overrides for a configuration
     */
public function updateWidgetOverrides(Request $request, ClientConfiguration $configuration)
{
    // Normalize incoming payloads
    $input = $request->all();
    if (!isset($input['overrides'])) {
        if (isset($input['payload']['overrides']) && is_array($input['payload']['overrides'])) {
            $request->merge(['overrides' => $input['payload']['overrides']]);
        } else {
            $keys = array_keys($input);
            if ($keys === range(0, count($keys) - 1)) {
                $request->merge(['overrides' => $input]);
            }
        }
    } else {
        $ov = $request->input('overrides');
        if (is_array($ov) && isset($ov['widget_overrides']) && is_array($ov['widget_overrides'])) {
            $request->merge(['overrides' => $ov['widget_overrides']]);
        } elseif (is_array($ov) && array_key_exists('overrides', $ov) && is_array($ov['overrides'])) {
            $request->merge(['overrides' => $ov['overrides']]);
        }
    }

    $validated = $request->validate([
        'overrides' => 'required|array',
        'overrides.*.screen_widget_id' => 'required|exists:screen_widgets,id',
        'overrides.*.screen_id' => 'nullable|exists:screens,id',
        'overrides.*.is_visible' => 'boolean',
        'overrides.*.display_order' => 'integer',
        'overrides.*.selected_theme_id' => 'nullable|exists:widget_themes,id',
        'overrides.*.selected_child_theme_id' => 'nullable|integer',
        'overrides.*.settings' => 'nullable|array',
        'overrides.*.settings.primary_settings' => 'nullable|array',
        'overrides.*.settings.secondary_settings' => 'nullable|array',
        'overrides.*.settings.action' => 'nullable|array',
        'overrides.*.settings.theme_id' => 'nullable|integer',
    ]);

    DB::transaction(function () use ($configuration, $validated, $request) {
        foreach ($validated['overrides'] as $index => $widgetData) {
            $rawWidgetData = $request->input("overrides.$index");

            $screenWidget = ScreenWidget::find($widgetData['screen_widget_id']);
            // Use the explicit theme_id from payload first, otherwise reuse the existing selection or widget default
            $existingWidgetOverride = ConfigWidgetOverride::where('configuration_id', $configuration->id)
                ->where('screen_widget_id', $widgetData['screen_widget_id'])
                ->first();
            // Prefer theme_id from payload, otherwise fall back to existing override or widget default
            $themeId = data_get($rawWidgetData, 'settings.theme_id')
                ?? ($widgetData['selected_theme_id'] ?? null)
                ?? $existingWidgetOverride?->selected_theme_id
                ?? $screenWidget->widget_theme_id;

            // Extract layout settings from payload
            $settings = data_get($rawWidgetData, 'settings', []);

            // 1️⃣ تحديث أو إنشاء الـ Widget Override
            $configWidgetOverride = ConfigWidgetOverride::updateOrCreate(
                [
                    'configuration_id' => $configuration->id,
                    'screen_widget_id' => $widgetData['screen_widget_id'],
                ],
                [
                    'screen_id' => $screenWidget->screen_id,
                    'is_visible' => $widgetData['is_visible'] ?? true,
                    'display_order' => $widgetData['display_order'] ?? 0,
                    'widget_id' => $screenWidget->widget_id,
                    'selected_theme_id' => $themeId,
                    'selected_child_theme_id' => $rawWidgetData['selected_child_theme_id'] ?? null,
                    'primary_settings' => data_get($settings, 'primary_settings', []),
                    'secondary_settings' => data_get($settings, 'secondary_settings', []),
                    'action' => data_get($settings, 'action', []),
                    // Visual Designer layout fields
                    'x' => data_get($settings, 'x'),
                    'y' => data_get($settings, 'y'),
                    'width' => data_get($settings, 'width'),
                    'height' => data_get($settings, 'height'),
                    'z_index' => data_get($settings, 'z_index'),
                    'opacity' => data_get($settings, 'opacity'),
                    'layout_mode' => data_get($settings, 'layout_mode'),
                    'layout_gap' => data_get($settings, 'layout_gap'),
                    'layout_padding' => data_get($settings, 'layout_padding'),
                    'child_width' => data_get($settings, 'child_width'),
                    'child_height' => data_get($settings, 'child_height'),
                    'infinite_scroll' => data_get($settings, 'infinite_scroll'),
                    'scroll_speed' => data_get($settings, 'scroll_speed'),
                    'background_color' => data_get($settings, 'background_color'),
                    'border_radius' => data_get($settings, 'border_radius'),
                ]
            );

            // Collect per-child settings if provided
            $childSettingsMap = collect(data_get($rawWidgetData, 'settings.children', []))
                ->keyBy('theme_child_id');
            
            $childVisibilities = $childSettingsMap
                ->map(fn($c) => array_key_exists('is_visible', $c) ? (bool)$c['is_visible'] : null);

            if (!$themeId) continue;

            // حافظ على حالات الإظهار السابقة لكل Child قبل الحذف
            $previousChildVisibility = $configWidgetOverride->themeChildOverrides
                ->keyBy('theme_child_id')
                ->map->is_visible;

            // 2️⃣ حذف أي Child Overrides مرتبطة بالـ Widget الحالي (لإعادة البذل)
            $configWidgetOverride->themeChildOverrides()->each(function ($childOverride) {
                $childOverride->assetOverrides()->delete();
                $childOverride->delete();
            });

            // 3️⃣ استرجاع Children للـ Theme
            $themeChildren = ThemeChild::where('theme_id', $themeId)->get();

            foreach ($themeChildren as $child) {
                // احذف أو اعد استخدام أي Override سابق لنفس الـ Child داخل نفس الـ Configuration لتجنب تضارب الـ unique
                $existingChildOverride = ConfigThemeChildOverride::where('configuration_id', $configuration->id)
                    ->where('theme_child_id', $child->id)
                    ->first();

                if ($existingChildOverride && $existingChildOverride->id !== optional($configWidgetOverride->themeChildOverrides->first())->id) {
                    $existingChildOverride->assetOverrides()->delete();
                }

                // استرجع الحالة السابقة أو القادمة من الـ payload أو الافتراضية
                $isVisible = $childVisibilities->get($child->id, null);
                if ($isVisible === null) {
                    $isVisible = $previousChildVisibility->get($child->id, null);
                }
                if ($isVisible === null) {
                    $isVisible = $child->is_visible !== null
                        ? (bool) $child->is_visible
                        : ($child->hide !== null ? !$child->hide : true);
                }

                // Get saved child settings from payload if available
                $savedChildSettings = $childSettingsMap->get($child->id, []);

                // إنشاء أو تحديث Child Override مع الحفاظ على حالة الإظهار السابقة
                $configThemeChildOverride = ConfigThemeChildOverride::updateOrCreate(
                    [
                        'configuration_id' => $configuration->id,
                        'theme_child_id' => $child->id,
                    ],
                    [
                        'config_widget_override_id' => $configWidgetOverride->id,
                        'is_visible' => $isVisible,
                        'order' => $child->order,
                        'action' => $child->action,
                        'position' => $child->position,
                        // Visual Designer layout fields for children
                        'width' => data_get($savedChildSettings, 'width', $child->width),
                        'height' => data_get($savedChildSettings, 'height', $child->height),
                        'x' => data_get($savedChildSettings, 'x', $child->x ?? 0),
                        'y' => data_get($savedChildSettings, 'y', $child->y ?? 0),
                        'rotation' => data_get($savedChildSettings, 'rotation', 0),
                        'scale' => data_get($savedChildSettings, 'scale', 1),
                        'opacity' => data_get($savedChildSettings, 'opacity', 1),
                        'z_index' => data_get($savedChildSettings, 'z_index', 0),
                        'background_color' => data_get($savedChildSettings, 'background_color'),
                        'border_width' => data_get($savedChildSettings, 'border_width', 0),
                        'border_style' => data_get($savedChildSettings, 'border_style'),
                        'border_color' => data_get($savedChildSettings, 'border_color'),
                        'border_radius_tl' => data_get($savedChildSettings, 'border_radius_tl', 0),
                        'border_radius_tr' => data_get($savedChildSettings, 'border_radius_tr', 0),
                        'border_radius_bl' => data_get($savedChildSettings, 'border_radius_bl', 0),
                        'border_radius_br' => data_get($savedChildSettings, 'border_radius_br', 0),
                    ]
                );

                // 4️⃣ استرجاع Assets الخاصة بالـ Child وإنشاؤها
                $configThemeChildOverride->assetOverrides()->delete();
                $assets = ThemeAsset::where('child_id', $child->id)->get();
                
                // Get saved asset settings from payload
                $savedAssetSettings = collect(data_get($savedChildSettings, 'assets', []))
                    ->keyBy('id');

                foreach ($assets as $asset) {
                    $savedAsset = $savedAssetSettings->get($asset->id, []);
                    
                    ConfigChildAssetOverride::updateOrCreate(
                        [
                            'configuration_id' => $configuration->id,
                            'child_id' => $child->id,
                            'asset_id' => $asset->id,
                        ],
                        [
                            'config_theme_child_override_id' => $configThemeChildOverride->id,
                            'type' => $asset->type,
                            'text' => $asset->text,
                            'file_path' => $asset->file_path,
                            'is_visible' => data_get($savedAsset, 'is_visible', true),
                            'is_background' => data_get($savedAsset, 'is_background', false),
                            'object_fit' => data_get($savedAsset, 'object_fit', 'contain'),
                            // Visual Designer layout fields for assets
                            'width' => data_get($savedAsset, 'width', $asset->width),
                            'height' => data_get($savedAsset, 'height', $asset->height),
                            'x' => data_get($savedAsset, 'x', $asset->x ?? 0),
                            'y' => data_get($savedAsset, 'y', $asset->y ?? 0),
                            'rotation' => data_get($savedAsset, 'rotation', 0),
                            'scale' => data_get($savedAsset, 'scale', 1),
                            'opacity' => data_get($savedAsset, 'opacity', 1),
                            'z_index' => data_get($savedAsset, 'z_index', 0),
                            'border_width' => data_get($savedAsset, 'border_width', 0),
                            'border_style' => data_get($savedAsset, 'border_style'),
                            'border_color' => data_get($savedAsset, 'border_color'),
                            'border_radius_tl' => data_get($savedAsset, 'border_radius_tl', 0),
                            'border_radius_tr' => data_get($savedAsset, 'border_radius_tr', 0),
                            'border_radius_bl' => data_get($savedAsset, 'border_radius_bl', 0),
                            'border_radius_br' => data_get($savedAsset, 'border_radius_br', 0),
                        ]
                    );
                }
            }
        }
    });

    return response()->json([
        'message' => 'Widget overrides updated successfully',
        'data' => $configuration->fresh()->load([
            'widgetOverrides.screenWidget.widget',
            'widgetOverrides.themeChildOverrides',
        ])
    ]);
}



    /**
     * Upload asset override for a configuration
     */
    public function uploadAssetOverride(Request $request, ClientConfiguration $configuration)
    {
        $request->validate([
            'theme_asset_id' => 'required|exists:theme_assets,id',
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $asset = ThemeAsset::findOrFail($request->theme_asset_id);
        $assetType = $asset->asset_type;
        $disk = config('filesystems.default');

        // Store file
        $path = Common::upload("config_{$configuration->id}/{$assetType}s", $file, $disk);

        // Delete old file if exists
        $existingOverride = ConfigAssetOverride::where('configuration_id', $configuration->id)
            ->where('theme_asset_id', $asset->id)
            ->first();

        if ($existingOverride && $existingOverride->file_path) {
            Storage::disk($disk)->delete($existingOverride->file_path);
        }

        // Create or update override
        $override = ConfigAssetOverride::updateOrCreate(
            [
                'configuration_id' => $configuration->id,
                'theme_asset_id' => $asset->id,
            ],
            [
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
            ]
        );

        return response()->json([
            'message' => 'Asset override uploaded successfully',
            'data' => [
                'file_path' => $path,
                'file_url' => Storage::disk($disk)->url($path),
                'original_filename' => $file->getClientOriginalName(),
            ]
        ]);
    }

    /**
     * Remove asset override
     */
    public function removeAssetOverride(Request $request, ClientConfiguration $configuration, $themeAssetId)
    {
        $override = ConfigAssetOverride::where('configuration_id', $configuration->id)
            ->where('theme_asset_id', $themeAssetId)
            ->first();

        if (!$override) {
            return response()->json([
                'message' => 'Asset override not found'
            ], 404);
        }

        $override->delete();

        return response()->json([
            'message' => 'Asset override removed successfully'
        ]);
    }

    /**
     * Get child assets for a configuration scoped to a specific theme child
     */
    public function getChildAssets(ClientConfiguration $configuration, ThemeChild $child)
    {
        // Always start from the base theme assets for this child, then layer per-config overrides.
        $assets = ThemeAsset::where('child_id', $child->id)
            ->orderBy('order')
            ->get();

        // Ensure we have a ThemeChildOverride record (needed for FK on asset overrides)
        $childOverride = ConfigThemeChildOverride::where('configuration_id', $configuration->id)
            ->where('theme_child_id', $child->id)
            ->first();

        if (!$childOverride) {
            $widgetOverrideId = ConfigWidgetOverride::where('configuration_id', $configuration->id)
                ->where('selected_theme_id', $child->theme_id)
                ->value('id')
                ?? ConfigWidgetOverride::where('configuration_id', $configuration->id)->value('id');

            $childOverride = ConfigThemeChildOverride::create([
                'configuration_id' => $configuration->id,
                'config_widget_override_id' => $widgetOverrideId,
                'theme_child_id' => $child->id,
                'is_visible' => $child->is_visible !== null ? (bool) $child->is_visible : true,
                'order' => $child->order,
                'action' => $child->action,
                'position' => $child->position,
            ]);
        }

        $overrides = ConfigChildAssetOverride::where('configuration_id', $configuration->id)
            ->where('child_id', $child->id)
            ->get()
            ->keyBy('asset_id');

        $data = $assets->map(function (ThemeAsset $asset) use ($overrides, $configuration, $child, $childOverride) {
            // Ensure an override exists for every asset in this configuration so the dashboard and mobile stay in sync.
            $override = $overrides->get($asset->id);
            if (!$override) {
                $override = ConfigChildAssetOverride::create([
                    'configuration_id' => $configuration->id,
                    'child_id' => $child->id,
                    'asset_id' => $asset->id,
                    'config_theme_child_override_id' => $childOverride?->id,
                    'type' => $asset->type,
                    'text' => $asset->text,
                    'file_path' => $asset->file_path,
                    'is_visible' => true,
                ]);
            }

            return [
                'id' => $override->id,
                'asset_id' => $asset->id,
                'child_id' => $child->id,
                'type' => $override->type ?? $asset->type,
                'input_type' => $asset->type,
                'asset_key' => $asset->asset_key,
                'asset_label' => $asset->asset_label,
                'asset_type' => $asset->asset_type,
                'description' => $asset->description ?? null,
                'default_url' => $asset->default_url,
                'file_path' => $override->file_path ?? $asset->file_path,
                'original_filename' => $asset->original_filename,
                'is_visible' => $override->is_visible !== false,
            ];
        })->values();

        return response()->json(['data' => $data]);
    }

    /**
     * Toggle child asset visibility per configuration instead of deleting
     */
    public function toggleChildAssetVisibility(Request $request, ClientConfiguration $configuration, ConfigChildAssetOverride $override)
    {
        if ($override->configuration_id !== $configuration->id) {
            return response()->json(['message' => 'Override does not belong to configuration'], 404);
        }

        $override->is_visible = !$override->is_visible;
        $override->save();

        return response()->json([
            'message' => 'Child asset visibility updated',
            'data' => $override->fresh('asset'),
        ]);
    }

    /**
     * Get full configuration data for editing (screens with widgets)
     */
   
public function getFullConfiguration(ClientConfiguration $configuration)
{
    // تحميل العلاقات المطلوبة
    $configuration->load([
        'screenOverrides',
        'widgetOverrides.screenWidget.widget.themes.children.assets',
        'widgetOverrides.themeChildOverrides.assetOverrides',
    ]);

    // جلب الشاشات النشطة
    $screens = Screen::with(['screenWidgets.widget'])
        ->active()
        ->orderBy('display_order')
        ->get();

    $screensData = $screens->map(function ($screen) use ($configuration) {
        // البحث عن override للشاشة
        $screenOverride = $configuration->screenOverrides->firstWhere('screen_id', $screen->id);

        return [
            'id' => $screen->id,
            'screen_key' => $screen->screen_key,
            'screen_name' => $screen->screen_name,
            'display_order' => $screenOverride->display_order ?? $screen->display_order,
            'is_visible' => $screenOverride->is_visible ?? true,
            'widgets' => $screen->screenWidgets->map(function ($sw) use ($configuration) {
                // البحث عن override للـ widget
                $widgetOverride = $configuration->widgetOverrides
                    ->firstWhere('screen_widget_id', $sw->id);

                if (!$widgetOverride) {
                    return null; // تجاهل الـ widgets التي لا يوجد لها override
                }

                $selectedThemeId = $widgetOverride->selected_theme_id;

                return [
                    'id' => $sw->id,
                    'widget_id' => $sw->widget_id,
                    'widget_key' => $sw->widget?->widget_key,
                    'widget_name' => $sw->widget?->display_name,
                    'display_order' => $widgetOverride->display_order ?? 0,
                    'is_visible' => $widgetOverride->is_visible ?? true,
                    'selected_theme_id' => $selectedThemeId,
                    
                    // الـ Theme المحدد مع أطفاله وأصوله المحفوظة
                    'theme' => $selectedThemeId ? $this->getThemeWithOverrides(
                        $selectedThemeId, 
                        $widgetOverride
                    ) : null,
                ];
            })->filter()->values(), // إزالة القيم الـ null
        ];
    });

    return response()->json([
        'data' => [
            'id' => $configuration->id,
            'name' => $configuration->name,
            'description' => $configuration->description,
            'is_active' => $configuration->is_active,
            'screens' => $screensData,
        ],
    ]);
}

/**
 * جلب بيانات الـ Theme مع الـ Overrides المحفوظة
 */
private function getThemeWithOverrides($themeId, $widgetOverride)
{
    $theme = WidgetTheme::find($themeId);
    
    if (!$theme) {
        return null;
    }

    return [
        'id' => $theme->id,
        'theme_name' => $theme->theme_name,
        'theme_key' => $theme->theme_key,
        'is_default' => $theme->is_default,
        
        // الأطفال المحفوظة في ConfigThemeChildOverride
        'children' => $widgetOverride->themeChildOverrides->map(function ($childOverride) {
            return [
                'id' => $childOverride->id,
                'theme_child_id' => $childOverride->theme_child_id,
                'is_visible' => $childOverride->is_visible,
                'order' => $childOverride->order,
                'action' => $childOverride->action,
                'position' => $childOverride->position,
                
                // الأصول المحفوظة في ConfigChildAssetOverride
                'assets' => $childOverride->assetOverrides->map(function ($assetOverride) {
                    return [
                        'id' => $assetOverride->id,
                        'asset_id' => $assetOverride->asset_id,
                        'child_id' => $assetOverride->child_id,
                        'type' => $assetOverride->type,
                        'text' => $assetOverride->text,
                        'file_path' => $assetOverride->file_path,
                        'file_url' => $assetOverride->file_path ? \Illuminate\Support\Facades\Storage::url($assetOverride->file_path) : null,
                    ];
                })->values(),
            ];
        })->values(),
    ];
}

    /**
     * Initialize screen overrides with current defaults
     */
    private function initializeScreenOverrides(ClientConfiguration $configuration): void
    {
        $screens = Screen::active()->orderBy('display_order')->get();

        foreach ($screens as $screen) {
            ConfigScreenOverride::create([
                'configuration_id' => $configuration->id,
                'screen_id' => $screen->id,
                'is_visible' => true,
                'display_order' => $screen->display_order,
            ]);
        }
    }
}
