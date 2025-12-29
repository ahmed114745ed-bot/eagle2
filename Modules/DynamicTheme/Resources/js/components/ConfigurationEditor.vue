<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">
                        ✏️ Editing: {{ configuration?.name }}
                    </h2>
                    <p v-if="configuration?.description" class="text-gray-500 mt-1">
                        {{ configuration.description }}
                    </p>
                </div>
                <div class="flex space-x-3">
                    <button
                        @click="saveAllChanges"
                        :disabled="saving"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ saving ? 'Saving...' : '💾 Save Changes' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow">
            <div class="border-b">
                <nav class="flex -mb-px">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        @click="activeTab = tab.key"
                        :class="[
                            'px-6 py-3 text-sm font-medium border-b-2 transition-colors',
                            activeTab === tab.key
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]"
                    >
                        {{ tab.icon }} {{ tab.label }}
                    </button>
                </nav>
            </div>

            <div class="p-6">
                <!-- Screens Tab -->
                <div v-if="activeTab === 'screens'" class="space-y-4">
                    <p class="text-gray-500 text-sm mb-4">
                        Configure which screens are visible and their display order for this configuration.
                    </p>

                    <div v-if="!screens.length" class="text-center py-8 text-gray-400">
                        No screens available
                    </div>

                    <draggable
                        v-else
                        v-model="screens"
                        item-key="id"
                        handle=".drag-handle"
                        class="space-y-2"
                        @change="onScreenOrderChange"
                    >
                        <template #item="{ element: screen }">
                            <div
                                :class="[
                                    'border rounded-lg p-4 flex items-center justify-between',
                                    getScreenOverride(screen.id)?.is_visible !== false
                                        ? 'bg-white border-gray-200'
                                        : 'bg-gray-50 border-gray-100 opacity-60'
                                ]"
                            >
                                <div class="flex items-center space-x-4">
                                    <span class="drag-handle cursor-move text-gray-400 hover:text-gray-600">⋮⋮</span>
                                    <div>
                                        <h4 class="font-medium text-gray-900">{{ screen.screen_name }}</h4>
                                        <span class="text-xs text-gray-400">{{ screen.screen_key }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            :checked="getScreenOverride(screen.id)?.is_visible !== false"
                                            @change="toggleScreenVisibility(screen)"
                                            class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                                        />
                                        <span class="text-sm text-gray-600">Visible</span>
                                    </label>
                                </div>
                            </div>
                        </template>
                    </draggable>
                </div>

                <!-- Widgets Tab -->
                <div v-if="activeTab === 'widgets'" class="space-y-6">
                    <p class="text-gray-500 text-sm mb-4">
                        Configure widget visibility, order, and themes for each screen.
                    </p>

                    <div v-if="!screens.length" class="text-center py-8 text-gray-400">
                        No screens available
                    </div>

                    <div v-else class="space-y-6">
                        <div
                            v-for="screen in screensWithWidgets"
                            :key="screen.id"
                            class="border rounded-lg"
                        >
                            <div class="bg-gray-50 px-4 py-3 border-b">
                                <h4 class="font-medium text-gray-900">📱 {{ screen.screen_name }}</h4>
                            </div>

                            <div class="p-4">
                                <div v-if="!screen.widgets?.length" class="text-center py-4 text-gray-400 text-sm">
                                    No widgets on this screen
                                </div>

                                <draggable
                                    v-else
                                    :list="screen.widgets"
                                    item-key="id"
                                    handle=".drag-handle"
                                    group="widgets"
                                    class="space-y-2"
                                    @change="onWidgetOrderChange(screen)"
                                >
                                    <template #item="{ element: widget }">
                                        <div
                                            :class="[
                                                'border rounded-lg p-3 flex items-center justify-between',
                                                getWidgetOverride(widget.id)?.is_visible !== false
                                                    ? 'bg-white border-gray-200'
                                                    : 'bg-gray-50 border-gray-100 opacity-60'
                                            ]"
                                        >
                                            <div class="flex items-center space-x-3">
                                                <span class="drag-handle cursor-move text-gray-400 hover:text-gray-600">⋮⋮</span>
                                                <div>
                                                    <h5 class="font-medium text-gray-800 text-sm">
                                                        {{ widget.widget?.widget_name || widget.widget_key }}
                                                    </h5>
                                                    <span class="text-xs text-gray-400">{{ widget.widget_key }}</span>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-4">
                                                <!-- Theme Selector -->
                                                <select
                                                    v-if="widget.widget?.themes?.length"
                                                    :value="getWidgetOverride(widget.id)?.selected_theme_id || widget.selected_theme_id"
                                                    @change="onWidgetThemeChange(widget, $event.target.value)"
                                                    class="text-sm border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                                                >
                                                    <option
                                                        v-for="theme in widget.widget.themes"
                                                        :key="theme.id"
                                                        :value="theme.id"
                                                    >
                                                        🎨 {{ theme.theme_name }}
                                                    </option>
                                                </select>

                                                <label class="flex items-center space-x-2 cursor-pointer">
                                                    <input
                                                        type="checkbox"
                                                        :checked="getWidgetOverride(widget.id)?.is_visible !== false"
                                                        @change="toggleWidgetVisibility(screen.id, widget)"
                                                        class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                                                    />
                                                    <span class="text-sm text-gray-600">Visible</span>
                                                </label>
                                            </div>
                                        </div>
                                    </template>
                                </draggable>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assets Tab -->
                <div v-if="activeTab === 'assets'" class="space-y-4">
                    <p class="text-gray-500 text-sm mb-4">
                        Override asset files for this configuration (e.g., Christmas icons, Ramadan graphics).
                    </p>

                    <div v-if="!themeAssets.length" class="text-center py-8 text-gray-400">
                        No assets available to override
                    </div>

                    <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <div
                            v-for="asset in themeAssets"
                            :key="asset.id"
                            class="border rounded-lg p-4 hover:border-blue-300 transition-colors"
                        >
                            <div class="aspect-square bg-gray-100 rounded-lg mb-3 overflow-hidden relative">
                                <img
                                    v-if="getAssetPreviewUrl(asset)"
                                    :src="getAssetPreviewUrl(asset)"
                                    :alt="asset.asset_key"
                                    class="w-full h-full object-contain"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-4xl">
                                    🖼️
                                </div>

                                <!-- Override badge -->
                                <span
                                    v-if="getAssetOverride(asset.id)"
                                    class="absolute top-2 right-2 px-2 py-1 bg-blue-500 text-white text-xs rounded"
                                >
                                    Overridden
                                </span>
                            </div>

                            <h5 class="font-medium text-gray-800 text-sm truncate">{{ asset.asset_key }}</h5>
                            <p class="text-xs text-gray-400 truncate">{{ asset.original_filename }}</p>

                            <div class="mt-3 flex space-x-2">
                                <label class="flex-1 cursor-pointer">
                                    <span class="block w-full text-center px-3 py-1.5 bg-blue-50 text-blue-600 text-sm rounded hover:bg-blue-100">
                                        📤 Upload
                                    </span>
                                    <input
                                        type="file"
                                        accept="image/*"
                                        @change="uploadAssetOverride(asset, $event)"
                                        class="hidden"
                                    />
                                </label>
                                <button
                                    v-if="getAssetOverride(asset.id)"
                                    @click="removeAssetOverride(asset)"
                                    class="px-3 py-1.5 text-red-600 text-sm rounded hover:bg-red-50"
                                    title="Remove override"
                                >
                                    🗑️
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import draggable from 'vuedraggable';
import { configurationsApi, screensApi, widgetsApi } from '../services/api';

export default {
    name: 'ConfigurationEditor',
    components: { draggable },
    props: {
        configuration: {
            type: Object,
            required: true,
        },
    },
    emits: ['save', 'notify', 'back'],

    setup(props, { emit }) {
        const activeTab = ref('screens');
        const saving = ref(false);

        const screens = ref([]);
        const themeAssets = ref([]);

        // Local overrides state
        const screenOverrides = ref({});
        const widgetOverrides = ref({});
        const assetOverrides = ref({});

        const tabs = [
            { key: 'screens', label: 'Screens', icon: '📱' },
            { key: 'widgets', label: 'Widgets', icon: '🧩' },
            { key: 'assets', label: 'Assets', icon: '🎨' },
        ];

        const screensWithWidgets = computed(() => {
            return screens.value.filter(s => s.widgets && s.widgets.length > 0);
        });

        const loadData = async () => {
            if (!props.configuration) return;

            // Initialize overrides from configuration
            screenOverrides.value = {};
            widgetOverrides.value = {};
            assetOverrides.value = {};

            // Parse existing screen overrides
            (props.configuration.screen_overrides || []).forEach(o => {
                screenOverrides.value[o.screen_id] = {
                    is_visible: o.is_visible,
                    display_order: o.display_order,
                };
            });

            // Parse existing widget overrides
            (props.configuration.widget_overrides || []).forEach(o => {
                widgetOverrides.value[o.screen_widget_id] = {
                    screen_id: o.screen_id,
                    is_visible: o.is_visible,
                    display_order: o.display_order,
                    selected_theme_id: o.selected_theme_id,
                };
            });

            // Parse existing asset overrides
            (props.configuration.asset_overrides || []).forEach(o => {
                assetOverrides.value[o.theme_asset_id] = {
                    file_path: o.file_path,
                    file_url: o.file_url,
                    original_filename: o.original_filename,
                };
            });

            // Load screens with widgets
            screens.value = props.configuration.screens || [];

            // Load theme assets
            await loadThemeAssets();
        };

        const loadThemeAssets = async () => {
            try {
                // Get all unique theme assets from widget themes
                const assets = [];
                const assetIds = new Set();

                screens.value.forEach(screen => {
                    (screen.widgets || []).forEach(widget => {
                        const selectedTheme = widget.widget?.themes?.find(t =>
                            t.id === (getWidgetOverride(widget.id)?.selected_theme_id || widget.selected_theme_id)
                        );
                        (selectedTheme?.assets || []).forEach(asset => {
                            if (!assetIds.has(asset.id)) {
                                assetIds.add(asset.id);
                                assets.push(asset);
                            }
                        });
                    });
                });

                themeAssets.value = assets;
            } catch (error) {
                console.error('Failed to load assets:', error);
            }
        };

        // Screen overrides
        const getScreenOverride = (screenId) => {
            return screenOverrides.value[screenId] || null;
        };

        const toggleScreenVisibility = (screen) => {
            const current = screenOverrides.value[screen.id];
            const isVisible = current?.is_visible !== false;

            screenOverrides.value[screen.id] = {
                ...current,
                is_visible: !isVisible,
                display_order: current?.display_order || screen.display_order,
            };
        };

        const onScreenOrderChange = () => {
            screens.value.forEach((screen, index) => {
                const current = screenOverrides.value[screen.id] || {};
                screenOverrides.value[screen.id] = {
                    ...current,
                    display_order: index + 1,
                    is_visible: current.is_visible !== false,
                };
            });
        };

        // Widget overrides
        const getWidgetOverride = (screenWidgetId) => {
            return widgetOverrides.value[screenWidgetId] || null;
        };

        const toggleWidgetVisibility = (screenId, widget) => {
            const current = widgetOverrides.value[widget.id];
            const isVisible = current?.is_visible !== false;

            widgetOverrides.value[widget.id] = {
                ...current,
                screen_id: screenId,
                is_visible: !isVisible,
                display_order: current?.display_order || widget.display_order,
                selected_theme_id: current?.selected_theme_id || widget.selected_theme_id,
            };
        };

        const onWidgetOrderChange = (screen) => {
            screen.widgets.forEach((widget, index) => {
                const current = widgetOverrides.value[widget.id] || {};
                widgetOverrides.value[widget.id] = {
                    ...current,
                    screen_id: screen.id,
                    display_order: index + 1,
                    is_visible: current.is_visible !== false,
                    selected_theme_id: current.selected_theme_id || widget.selected_theme_id,
                };
            });
        };

        const onWidgetThemeChange = (widget, themeId) => {
            const current = widgetOverrides.value[widget.id] || {};
            widgetOverrides.value[widget.id] = {
                ...current,
                selected_theme_id: parseInt(themeId),
                is_visible: current.is_visible !== false,
                display_order: current.display_order || widget.display_order,
            };
        };

        // Asset overrides
        const getAssetOverride = (assetId) => {
            return assetOverrides.value[assetId] || null;
        };

        const getAssetPreviewUrl = (asset) => {
            const override = getAssetOverride(asset.id);
            if (override?.file_url) {
                return override.file_url;
            }
            return asset.file_url || null;
        };

        const uploadAssetOverride = async (asset, event) => {
            const file = event.target.files[0];
            if (!file) return;

            try {
                const response = await configurationsApi.uploadAssetOverride(
                    props.configuration.id,
                    asset.id,
                    file
                );

                const data = response.data.data || response.data;
                assetOverrides.value[asset.id] = {
                    file_path: data.file_path,
                    file_url: data.file_url,
                    original_filename: data.original_filename,
                };

                emit('notify', { type: 'success', message: 'Asset uploaded successfully' });
            } catch (error) {
                emit('notify', { type: 'error', message: 'Failed to upload asset' });
            }

            // Reset input
            event.target.value = '';
        };

        const removeAssetOverride = async (asset) => {
            try {
                await configurationsApi.removeAssetOverride(props.configuration.id, asset.id);
                delete assetOverrides.value[asset.id];
                emit('notify', { type: 'success', message: 'Asset override removed' });
            } catch (error) {
                emit('notify', { type: 'error', message: 'Failed to remove asset override' });
            }
        };

        // Save all changes
        const saveAllChanges = async () => {
            try {
                saving.value = true;

                // Build screen overrides array
                const screenOverridesArray = Object.entries(screenOverrides.value).map(([screenId, override]) => ({
                    screen_id: parseInt(screenId),
                    is_visible: override.is_visible,
                    display_order: override.display_order,
                }));

                // Build widget overrides array
                const widgetOverridesArray = Object.entries(widgetOverrides.value).map(([widgetId, override]) => ({
                    screen_widget_id: parseInt(widgetId),
                    screen_id: override.screen_id,
                    is_visible: override.is_visible,
                    display_order: override.display_order,
                    selected_theme_id: override.selected_theme_id,
                }));

                // Save screen overrides
                if (screenOverridesArray.length > 0) {
                    await configurationsApi.updateScreenOverrides(props.configuration.id, screenOverridesArray);
                }

                // Save widget overrides
                if (widgetOverridesArray.length > 0) {
                    await configurationsApi.updateWidgetOverrides(props.configuration.id, widgetOverridesArray);
                }

                emit('save');
                emit('notify', { type: 'success', message: 'Configuration saved successfully' });
            } catch (error) {
                emit('notify', { type: 'error', message: 'Failed to save configuration' });
            } finally {
                saving.value = false;
            }
        };

        watch(() => props.configuration, loadData, { immediate: true });

        return {
            activeTab,
            tabs,
            saving,
            screens,
            screensWithWidgets,
            themeAssets,
            getScreenOverride,
            toggleScreenVisibility,
            onScreenOrderChange,
            getWidgetOverride,
            toggleWidgetVisibility,
            onWidgetOrderChange,
            onWidgetThemeChange,
            getAssetOverride,
            getAssetPreviewUrl,
            uploadAssetOverride,
            removeAssetOverride,
            saveAllChanges,
        };
    },
};
</script>
