<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-end mb-4">
                    <a
                        href="/admin"
                        class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300"
                    >
                        ⬅️ العودة إلى لوحة التحكم
                    </a>
                </div>

                <!-- Configuration Selector Row -->
                <div class="flex justify-between items-center mb-4 pb-4 border-b border-gray-200">
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600 font-medium">📁 Configuration:</span>
                        <select
                            v-model="selectedConfigId"
                            @change="onConfigurationChange"
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option v-for="config in configurations" :key="config.id" :value="config.id">
                                {{ config.name }} {{ config.is_active ? '⭐' : '' }}
                            </option>
                        </select>
                        <button
                            @click="showNewConfigModal = true"
                            class="px-3 py-1.5 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700"
                        >
                            ➕ New
                        </button>
                        <button
                            @click="editConfiguration"
                            :disabled="!selectedConfigId"
                            class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 disabled:opacity-50"
                        >
                            ✏️ Edit
                        </button>
                        <button
                            @click="cloneConfiguration"
                            :disabled="!selectedConfigId"
                            class="px-3 py-1.5 bg-yellow-600 text-white text-sm rounded-lg hover:bg-yellow-700 disabled:opacity-50"
                        >
                            📋 Clone
                        </button>
                        <button
                            v-if="selectedConfig && !selectedConfig.is_active"
                            @click="activateConfiguration"
                            class="px-3 py-1.5 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700"
                        >
                            ⭐ Activate
                        </button>
                        <span v-if="selectedConfig?.is_active" class="px-3 py-1.5 bg-green-100 text-green-700 text-sm rounded-lg">
                            ✅ Active Configuration
                        </span>
                    </div>
                </div>

                <!-- Main Header Row -->
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-900">
                        🎨 Dynamic Theme Dashboard
                    </h1>
                </div>

                <div class="mt-4 flex justify-end space-x-4">
                  

                    <button
                        @click="saveConfiguration"
                        :disabled="!hasChanges || !selectedConfigId"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        💾 Save Configuration
                    </button>
                    
                    <button
                        @click="previewScreen"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                    >
                        👁️ Preview
                    </button>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex gap-6">
                <!-- Left Sidebar - Screens & Widgets Library -->
                <div class="w-80 flex-shrink-0 space-y-6">
                    <!-- Screens List -->
                    <ScreensList
                        :screens="perConfigScreens"
                        :selectedScreen="selectedScreen"
                        :screenOverrides="currentScreenOverrides"
                        @select="selectScreen"
                        @toggle-visibility="toggleScreenVisibility"
                        @reorder="reorderScreens"
                    />

                    <!-- Widgets Library -->
                    <WidgetsLibrary
                        :widgets="filteredAvailableWidgets"
                        @drag-start="onWidgetDragStart"
                    />
                  
                </div>

                <!-- Main Content - Screen Builder -->
                <div class="flex-1">
                    <ScreenBuilder
                        v-if="selectedScreen"
                        :screen="selectedScreen"
                        :configurationId="selectedConfigId"
                        :screenWidgets="currentWidgets"
                        :widgetOverrides="currentWidgetOverrides"
                        @update-order="updateWidgetOrder"
                        @remove-widget="removeWidget"
                        @edit-widget="editWidget"
                        @add-widget="addWidget"
                        @toggle-widget-visibility="toggleWidgetVisibility"
                    />
                    
                    <!-- Widget Visual Designer Button -->
                    <div v-if="editingWidget && editingWidget.settings" class="mt-8">
                        <div class="bg-gradient-to-r from-indigo-50 via-purple-50 to-pink-50 rounded-xl p-6 border border-indigo-200 shadow-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                                        <span class="text-3xl">🎨</span>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-800">المصمم المرئي</h3>
                                        <p class="text-gray-600 text-sm">صمم واجهة الـ Widget بشكل مرئي تفاعلي</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full">{{ (editingWidget.settings.children || []).length }} أطفال</span>
                                            <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs rounded-full">{{ editingWidgetThemes.length }} ثيمات</span>
                                        </div>
                                    </div>
                                </div>
                                <button 
                                    @click="openVisualDesigner"
                                    class="px-8 py-4 rounded-xl font-bold text-lg transition-all bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 text-white hover:from-purple-700 hover:via-indigo-700 hover:to-blue-700 shadow-xl hover:shadow-2xl hover:scale-105 flex items-center gap-3"
                                >
                                    <span class="text-2xl">✨</span>
                                    فتح المصمم المرئي
                                    <span class="text-2xl">→</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="!selectedScreen" class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                        <p class="text-lg">Select a screen to start editing</p>
                    </div>
                </div>

                <!-- Right Sidebar - Widget Settings -->
                <div class="w-96 flex-shrink-0">
                    <WidgetSettings
                        v-if="editingWidget"
                        :configurationId="selectedConfigId"
                        :screenWidgets="currentWidgets"
                        :widgetOverrides="currentWidgetOverrides"
                        :widget="editingWidget"
                        :themes="editingWidgetThemes"
                        @update="updateWidgetSettings"
                        @update-theme="updateWidgetTheme"
                        @update-child-theme="setWidgetChildTheme"
                        @upload-asset="uploadAssetOverride"
                        @remove-asset="removeAssetOverride"
                        @close="closeWidgetEditor"
                    />
                    <div v-else class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                        <p>Click on a widget to edit its settings</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- New Configuration Modal -->
        <div v-if="showNewConfigModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-96">
                <h3 class="text-lg font-semibold mb-4">{{ cloneMode ? '📋 Clone Configuration' : '➕ New Configuration' }}</h3>
                <input
                    v-model="newConfigName"
                    type="text"
                    placeholder="Configuration name (e.g., Christmas 2025)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-4"
                    @keyup.enter="createConfiguration"
                />
                <textarea
                    v-model="newConfigDescription"
                    placeholder="Description (optional)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-4"
                    rows="2"
                ></textarea>
                <div class="flex justify-end space-x-3">
                    <button
                        @click="closeConfigModal"
                        class="px-4 py-2 text-gray-600 hover:text-gray-800"
                    >
                        Cancel
                    </button>
                    <button
                        @click="createConfiguration"
                        :disabled="!newConfigName"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ cloneMode ? 'Clone' : 'Create' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit Configuration Modal -->
        <div v-if="showEditConfigModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-96">
                <h3 class="text-lg font-semibold mb-4">✏️ Edit Configuration</h3>
                <input
                    v-model="editConfigName"
                    type="text"
                    placeholder="Configuration name"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-4"
                    @keyup.enter="updateConfiguration"
                />
                <textarea
                    v-model="editConfigDescription"
                    placeholder="Description (optional)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-4"
                    rows="2"
                ></textarea>
                <div class="flex justify-between">
                    <button
                        @click="deleteConfiguration"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                    >
                        🗑️ Delete
                    </button>
                    <div class="flex space-x-3">
                        <button
                            @click="showEditConfigModal = false"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800"
                        >
                            Cancel
                        </button>
                        <button
                            @click="updateConfiguration"
                            :disabled="!editConfigName"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                        >
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Modal -->
        <PreviewModal
            :show="showPreview"
            :screenData="previewData"
            @close="showPreview = false"
        />

        <!-- Notification -->
        <NotificationToast
            :show="!!notification"
            :title="notification?.message || ''"
            :type="notification?.type || 'info'"
            @close="notification = null"
        />
        
        <!-- Visual Designer Modal -->
        <VisualDesigner
            v-if="showVisualDesigner"
            ref="visualDesignerRef"
            :widget="editingWidget"
            :widgets="currentWidgets"
            :themes="editingWidgetThemes"
            :configurationId="selectedConfigId"
            @close="closeVisualDesigner"
            @save="onVisualDesignerSave"
        />
    </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import ScreensList from './ScreensList.vue';
import WidgetsLibrary from './WidgetsLibrary.vue';
import ScreenBuilder from './ScreenBuilder.vue';
import WidgetSettings from './WidgetSettings.vue';
import PreviewModal from './PreviewModal.vue';
import NotificationToast from './NotificationToast.vue';
import VisualDesigner from './VisualDesigner.vue';
import { screensApi, widgetsApi, screenWidgetsApi, configurationsApi } from '../services/api';

export default {
    name: 'DashboardApp',
    components: {
        ScreensList,
        WidgetsLibrary,
        ScreenBuilder,
        WidgetSettings,
        PreviewModal,
        NotificationToast,
        VisualDesigner,
    },
    setup() {
        // Visual Designer State
        const showVisualDesigner = ref(false);
        const visualDesignerRef = ref(null);
        
        // Configuration State
        const configurations = ref([]);
        const selectedConfigId = ref(null);
        const selectedConfig = computed(() =>
            configurations.value.find(c => c.id === selectedConfigId.value)
        );
        const showNewConfigModal = ref(false);
        const newConfigName = ref('');
        const newConfigDescription = ref('');
        const cloneMode = ref(false);

        // Edit Configuration State
        const showEditConfigModal = ref(false);
        const editConfigName = ref('');
        const editConfigDescription = ref('');

        // Configuration Overrides (per-configuration storage)
        // map configId -> { screen_overrides: [], widget_overrides: [], asset_overrides: [] }
        const overridesMap = ref({});

        const ensureConfigOverrides = (configId) => {
            if (!configId) return;
            if (!overridesMap.value[configId]) {
                overridesMap.value[configId] = {
                    screen_overrides: [],
                    widget_overrides: [],
                    asset_overrides: [],
                };
            }
        };

        const screenOverrides = computed({
            get() {
                return overridesMap.value[selectedConfigId.value]?.screen_overrides || [];
            },
            set(val) {
                if (!selectedConfigId.value) return;
                ensureConfigOverrides(selectedConfigId.value);
                overridesMap.value[selectedConfigId.value].screen_overrides = val;
            }
        });

        const widgetOverrides = computed({
            get() {
                return overridesMap.value[selectedConfigId.value]?.widget_overrides || [];
            },
            set(val) {
                if (!selectedConfigId.value) return;
                ensureConfigOverrides(selectedConfigId.value);
                overridesMap.value[selectedConfigId.value].widget_overrides = val;
            }
        });

        const assetOverrides = computed({
            get() {
                return overridesMap.value[selectedConfigId.value]?.asset_overrides || [];
            },
            set(val) {
                if (!selectedConfigId.value) return;
                ensureConfigOverrides(selectedConfigId.value);
                overridesMap.value[selectedConfigId.value].asset_overrides = val;
            }
        });

        // Track unsaved changes per configuration
        const hasChangesMap = ref({});
        const hasChanges = computed({
            get() {
                return hasChangesMap.value[selectedConfigId.value] || false;
            },
            set(val) {
                if (!selectedConfigId.value) return;
                hasChangesMap.value[selectedConfigId.value] = val;
            }
        });

        // Computed overrides for current config
        const currentScreenOverrides = computed(() => screenOverrides.value);
        const currentWidgetOverrides = computed(() => widgetOverrides.value);
        const currentAssetOverrides = computed(() => assetOverrides.value);

        // Screen/Widget State
        const screens = ref([]);
        const selectedScreen = ref(null);
        const screenWidgets = ref([]);
        const availableWidgets = ref([]);
        // Per-config widgets & themes
        const perConfigAvailableWidgets = ref({}); // configId -> widgets[]
        const perConfigWidgetThemes = ref({}); // configId -> { [widgetId]: themes[] }
        const widgetThemes = ref({});
        const editingWidget = ref(null);
        const showPreview = ref(false);
        const notification = ref(null);

        const ensureConfigWidgets = async (configId) => {
            if (!configId) return;
            if (!perConfigAvailableWidgets.value[configId]) {
                // copy the global widgets list as starting point
                perConfigAvailableWidgets.value[configId] = (availableWidgets.value || []).map(w => ({ ...w }));
            }
            if (!perConfigWidgetThemes.value[configId]) {
                perConfigWidgetThemes.value[configId] = {};
            }
        };

        const loadWidgetsForConfig = async (configId) => {
            if (!configId) return;
            await ensureConfigWidgets(configId);
            // fetch themes for each widget for this config if not already present
            const widgets = perConfigAvailableWidgets.value[configId] || [];
            for (const w of widgets) {
                if (!perConfigWidgetThemes.value[configId][w.id]) {
                    try {
                        const resp = await widgetsApi.getThemes(w.id);
                        perConfigWidgetThemes.value[configId][w.id] = resp.data.data || resp.data || [];
                    } catch (e) {
                        perConfigWidgetThemes.value[configId][w.id] = [];
                    }
                }
            }
        };

        const cleanupConfigWidgets = (configId) => {
            if (!configId) return;
            if (perConfigAvailableWidgets.value[configId]) delete perConfigAvailableWidgets.value[configId];
            if (perConfigWidgetThemes.value[configId]) delete perConfigWidgetThemes.value[configId];
        };

        const currentAvailableWidgets = computed(() => {
            return perConfigAvailableWidgets.value[selectedConfigId.value] || availableWidgets.value || [];
        });

        // Filter the library by the currently selected screen's allowed widgets (if present)
        const filteredAvailableWidgets = computed(() => {
            // No screen selected -> show base library for the current config
            if (!selectedScreen.value) return currentAvailableWidgets.value;

            // allowed_widgets_v2 contains widget definitions (ids)
            const allowed = (selectedScreen.value.allowed_widgets_v2 || []).map(w => parseInt(w.id));

            // If allowed is not present (null/undefined) -> show library
            if (!Array.isArray(selectedScreen.value.allowed_widgets_v2)) return currentAvailableWidgets.value;

            // If allowed array exists but is empty -> show empty library (strict behavior)
            if (allowed.length === 0) return [];

            const allowedSet = new Set(allowed);
            return currentAvailableWidgets.value.filter(w => allowedSet.has(parseInt(w.id)));
        });

        // Resolve widget meta (type/key/display name) from definitions when API items are minimal
        const resolveWidgetMeta = (widget) => {
            const byId = currentAvailableWidgets.value.find(w => parseInt(w.id) === parseInt(widget.widget_id));
            const byKey = widget.widget_key
                ? currentAvailableWidgets.value.find(w => w.widget_key === widget.widget_key)
                : null;

            const widgetType = widget.widget_type
                ?? byId?.widget_type
                ?? byKey?.widget_type
                ?? 'widget';
            const widgetKey = widget.widget_key
                ?? byId?.widget_key
                ?? byKey?.widget_key
                ?? '';
            const displayName = widget.display_name
                ?? byId?.display_name
                ?? byKey?.display_name
                ?? widgetType
                ?? widgetKey;

            const themeKey = widget.theme_key
                ?? byId?.theme_key
                ?? byKey?.theme_key
                ?? widgetKey
                ?? widgetType;

            return { widget_type: widgetType, widget_key: widgetKey, display_name: displayName, theme_key: themeKey };
        };

        // Computed: current widgets with overrides applied
            const currentWidgets = computed(() => {
            if (!selectedScreen.value) return [];

            const screenId = parseInt(selectedScreen.value.id);
            const baseWidgets = screenWidgets.value || [];
            const baseMap = new Map(
                baseWidgets.map(widget => [parseInt(widget.id), widget])
            );

            const overridesForScreen = widgetOverrides.value.filter(override => {
                const overrideScreenId = override.screen_id != null
                    ? parseInt(override.screen_id)
                    : screenId;
                return overrideScreenId === screenId;
            });

                const merged = overridesForScreen.map(override => {
                const base = baseMap.get(parseInt(override.screen_widget_id));
                if (!base) return null;

                const meta = resolveWidgetMeta(base);
                const selectedThemeId = override.selected_theme_id ?? override.settings?.theme_id ?? base.widget_theme_id ?? null;

                return {
                    ...meta,
                    ...base,
                    is_visible: override.is_visible != null ? override.is_visible : true,
                    display_order: override.display_order != null ? override.display_order : (base.order ?? 0),
                    // mirror display_order into order so UI updates instantly without reload
                    order: override.display_order != null ? override.display_order : (base.order ?? 0),
                    selected_theme_id: selectedThemeId,
                    theme_id: selectedThemeId,
                    selected_child_theme_id: override.selected_child_theme_id ?? base.selected_child_theme_id ?? null,
                    settings: override.settings ?? base.settings ?? null,
                };
            }).filter(Boolean);

            return merged.sort((a, b) => (a.display_order ?? 0) - (b.display_order ?? 0));
        });

        // Computed preview data
        const previewData = computed(() => {
            if (!selectedScreen.value) return null;
            return {
                screen_key: selectedScreen.value.screen_key,
                screen_name: selectedScreen.value.screen_name,
                widgets: currentWidgets.value.filter(w => w.is_visible),
            };
        });

        // Per-config computed screens view (applies screenOverrides to global screens list)
        const perConfigScreens = computed(() => {
            const base = screens.value || [];
            return base.map(screen => {
                const so = screenOverrides.value.find(s => s.screen_id === screen.id);
                return {
                    ...screen,
                    is_visible: so?.is_visible ?? true,
                    display_order: so?.display_order ?? (screen.display_order ?? 0),
                };
            }).sort((a, b) => (a.display_order || 0) - (b.display_order || 0));
        });

        // Load initial data
        onMounted(async () => {
            await loadConfigurations();
            await loadScreens();
            await loadWidgets();
        });

        // Per-config UI state: remember selected screen id and editing widget id per configuration
        const perConfigSelectedScreen = ref({}); // configId -> screen.id
        const perConfigEditingWidget = ref({});  // configId -> widget.id

        const ensureConfigUiState = (configId) => {
            if (!configId) return;
            if (!perConfigSelectedScreen.value[configId]) perConfigSelectedScreen.value[configId] = null;
            if (!perConfigEditingWidget.value[configId]) perConfigEditingWidget.value[configId] = null;
        };

        const restoreSelectedForConfig = async (configId) => {
            if (!configId) return;
            ensureConfigUiState(configId);

            // restore selected screen (prefer saved id, otherwise first screen)
            let screenToSelect = null;
            const savedScreenId = perConfigSelectedScreen.value[configId];
            if (savedScreenId) {
                screenToSelect = screens.value.find(s => s.id === savedScreenId) || null;
            }
            if (!screenToSelect && screens.value.length > 0) {
                screenToSelect = screens.value[0];
                perConfigSelectedScreen.value[configId] = screenToSelect.id;
            }
            if (screenToSelect) {
                await selectScreen(screenToSelect);
                // restore editing widget if present
                const savedWidgetId = perConfigEditingWidget.value[configId];
                if (savedWidgetId) {
                    const widgetObj = currentWidgets.value.find(w => w.id === savedWidgetId);
                    if (widgetObj) {
                        // reuse existing editWidget logic to load themes etc.
                        await editWidget(widgetObj);
                    } else {
                        editingWidget.value = null;
                    }
                } else {
                    editingWidget.value = null;
                }
            }
        };

        // Watch for configuration change
        watch(selectedConfigId, async (newId, oldId) => {
            if (!newId) return;

            // If previous config had unsaved changes, confirm discard and reload previous configuration (to discard local edits)
            if (oldId && hasChangesMap.value[oldId]) {
                const keep = confirm('You have unsaved changes on the previous configuration. Discard them and switch?');
                if (!keep) {
                    // abort switch
                    selectedConfigId.value = oldId;
                    return;
                } else {
                    // reload previous config from server to discard local edits
                    try {
                        const prevResp = await configurationsApi.get(oldId);
                        const prevData = prevResp.data?.data || prevResp.data;
                        ensureConfigOverrides(oldId);
                        overridesMap.value[oldId].screen_overrides = prevData.screen_overrides || prevData.screenOverrides || [];
                        overridesMap.value[oldId].widget_overrides = prevData.widget_overrides || prevData.widgetOverrides || [];
                        overridesMap.value[oldId].asset_overrides = prevData.asset_overrides || prevData.assetOverrides || [];
                        hasChangesMap.value[oldId] = false;
                    } catch (e) {
                        // ignore reload failure, proceed to load new config
                    }
                }
            }

            await loadConfigurationDetails(newId);
            // ensure UI state exists and restore screen + editing widget
            ensureConfigUiState(newId);
            // initialize per-config widgets/themes if needed
            await loadWidgetsForConfig(newId);
            await restoreSelectedForConfig(newId);
        });

        // ==================== Configuration API ====================

        const loadConfigurations = async () => {
            try {
                const response = await configurationsApi.list();
                configurations.value = response.data.data || [];

                // Select active configuration or first one
                const activeConfig = configurations.value.find(c => c.is_active);
                if (activeConfig) {
                    selectedConfigId.value = activeConfig.id;
                } else if (configurations.value.length > 0) {
                    selectedConfigId.value = configurations.value[0].id;
                }
            } catch (error) {
                showNotification('Failed to load configurations', 'error');
            }
        };

        const loadConfigurationDetails = async (configId) => {
            if (!configId) return;

            try {
                const response = await configurationsApi.get(configId);
                const data = response.data?.data || response.data || {};

                ensureConfigOverrides(configId);
                overridesMap.value[configId].screen_overrides = data.screen_overrides || data.screenOverrides || [];
                overridesMap.value[configId].widget_overrides = data.widget_overrides || data.widgetOverrides || [];
                overridesMap.value[configId].asset_overrides = data.asset_overrides || data.assetOverrides || [];

                normalizeOverrides(configId);
                await ensureConfigWidgets(configId);
                await loadWidgetsForConfig(configId);
                hasChangesMap.value[configId] = false;
            } catch (error) {
                console.error('[DashboardApp] Load config error:', error);
                showNotification('Failed to load configuration details', 'error');
            }
        };
        
        // Normalize override entries for a configuration
        const normalizeOverrides = (configId) => {
	if (!configId) return;
	ensureConfigOverrides(configId);
	const so = overridesMap.value[configId].screen_overrides || [];
	overridesMap.value[configId].screen_overrides = so.map(s => ({
		...s,
		screen_id: s.screen_id != null ? parseInt(s.screen_id) : null,
		is_visible: !!s.is_visible,
		display_order: s.display_order != null ? parseInt(s.display_order) : 0,
	}));

	const wo = overridesMap.value[configId].widget_overrides || [];
        overridesMap.value[configId].widget_overrides = wo.map(w => {
            const normalized = {
                ...w,
                screen_id: w.screen_id != null ? parseInt(w.screen_id) : null,
                screen_widget_id: w.screen_widget_id != null ? parseInt(w.screen_widget_id) : null,
                is_visible: !!w.is_visible,
                display_order: w.display_order != null ? parseInt(w.display_order) : 0,
                selected_theme_id: w.selected_theme_id != null ? (isNaN(parseInt(w.selected_theme_id)) ? null : parseInt(w.selected_theme_id)) : null,
                selected_child_theme_id: w.selected_child_theme_id != null ? (isNaN(parseInt(w.selected_child_theme_id)) ? null : parseInt(w.selected_child_theme_id)) : null,
            };

            const settings = w.settings ? { ...w.settings } : {};
            if (normalized.selected_theme_id != null && settings.theme_id == null) {
                settings.theme_id = parseInt(normalized.selected_theme_id);
            }

            // If API returned child overrides, surface them into settings.children
            // This handles both cases: when settings.children is missing OR when it needs enrichment
            if (Array.isArray(w.theme_child_overrides) && w.theme_child_overrides.length > 0) {
                // Create a map of existing children by theme_child_id for merging
                const existingChildrenMap = new Map();
                (settings.children || []).forEach(c => {
                    existingChildrenMap.set(c.theme_child_id, c);
                });
                
                settings.children = w.theme_child_overrides.map((c) => {
                    const existing = existingChildrenMap.get(c.theme_child_id ?? c.id) || {};
                    return {
                        ...existing,
                        theme_child_id: c.theme_child_id ?? c.id,
                        name: c.name || existing.name,
                        child_key: c.child_key || existing.child_key,
                        is_visible: c.is_visible ?? existing.is_visible ?? true,
                        width: c.width ?? existing.width,
                        height: c.height ?? existing.height,
                        x: c.x ?? existing.x,
                        y: c.y ?? existing.y,
                        rotation: c.rotation ?? existing.rotation,
                        scale: c.scale ?? existing.scale,
                        opacity: c.opacity ?? existing.opacity,
                        z_index: c.z_index ?? existing.z_index,
                        background_color: c.background_color ?? existing.background_color,
                        border_width: c.border_width ?? existing.border_width,
                        border_style: c.border_style ?? existing.border_style,
                        border_color: c.border_color ?? existing.border_color,
                        border_radius_tl: c.border_radius_tl ?? existing.border_radius_tl,
                        border_radius_tr: c.border_radius_tr ?? existing.border_radius_tr,
                        border_radius_bl: c.border_radius_bl ?? existing.border_radius_bl,
                        border_radius_br: c.border_radius_br ?? existing.border_radius_br,
                        // Preserve assets from theme_child_overrides if present
                        assets: Array.isArray(c.assets) && c.assets.length > 0 
                            ? c.assets.map(asset => ({
                                id: asset.id,
                                asset_key: asset.asset_key || asset.name,
                                name: asset.name || asset.asset_key,
                                file_url: asset.file_url || asset.url,
                                url: asset.file_url || asset.url,
                                width: asset.width,
                                height: asset.height,
                                x: asset.x,
                                y: asset.y,
                                opacity: asset.opacity,
                                z_index: asset.z_index,
                                is_visible: asset.is_visible ?? true,
                                is_background: asset.is_background ?? false,
                                object_fit: asset.object_fit ?? 'contain',
                                scale: asset.scale,
                                rotation: asset.rotation,
                            })) 
                            : (existing.assets || []),
                    };
                });
            }

            normalized.settings = settings;
            return normalized;
        });
};

// Fill missing selected_theme_id for widget overrides from the actual widget default (if available)
const fillMissingSelectedThemeIds = () => {
	// Map current screen widgets by id for quick lookup
	const mapById = {};
	(screenWidgets.value || []).forEach(w => {
		mapById[parseInt(w.id)] = w;
	});

	widgetOverrides.value.forEach(wo => {
		const wid = parseInt(wo.screen_widget_id);
        const baseWidget = mapById[wid];
        if (!wo.screen_id && baseWidget?.screen_id) {
            wo.screen_id = parseInt(baseWidget.screen_id);
        }
        if ((wo.selected_theme_id == null || wo.selected_theme_id === '') && baseWidget) {
            const def = baseWidget.widget_theme_id;
            if (def != null) {
                wo.selected_theme_id = parseInt(def);
            }
        }
        if (wo.selected_theme_id != null) {
            wo.settings = wo.settings || {};
            wo.settings.theme_id = parseInt(wo.selected_theme_id);
        }
	});
};

        const onConfigurationChange = async () => {
            if (hasChanges.value) {
                if (!confirm('You have unsaved changes. Discard them?')) {
                    return;
                }
            }
            await loadConfigurationDetails(selectedConfigId.value);
            hasChanges.value = false;
        };

        const createConfiguration = async () => {
            if (!newConfigName.value) return;

            try {
                let response;
                if (cloneMode.value && selectedConfigId.value) {
                    response = await configurationsApi.clone(selectedConfigId.value, newConfigName.value);
                } else {
                    response = await configurationsApi.create({
                        name: newConfigName.value,
                        description: newConfigDescription.value,
                    });
                }

                const newConfig = response.data.data || response.data;
                configurations.value.push(newConfig);
                // select and load new configuration details immediately
                const previousConfig = selectedConfigId.value;
                selectedConfigId.value = newConfig.id;
                // ensure overrides map exists for the new config to avoid "overrides required" later
                ensureConfigOverrides(newConfig.id);
               // copy UI state from previous config if available (keeps user's current screen/widget)
                ensureConfigUiState(newConfig.id);
                if (previousConfig) {
                    perConfigSelectedScreen.value[newConfig.id] = perConfigSelectedScreen.value[previousConfig] || null;
                    perConfigEditingWidget.value[newConfig.id] = perConfigEditingWidget.value[previousConfig] || null;
                    // copy per-config widgets/themes from previous config if present
                    if (perConfigAvailableWidgets.value[previousConfig]) {
                        perConfigAvailableWidgets.value[newConfig.id] = perConfigAvailableWidgets.value[previousConfig].map(w => ({ ...w }));
                    } else {
                        perConfigAvailableWidgets.value[newConfig.id] = (availableWidgets.value || []).map(w => ({ ...w }));
                    }
                    if (perConfigWidgetThemes.value[previousConfig]) {
                        perConfigWidgetThemes.value[newConfig.id] = { ...perConfigWidgetThemes.value[previousConfig] };
                    } else {
                        perConfigWidgetThemes.value[newConfig.id] = {};
                    }
                } else {
                    // initialize empty per-config sets
                    await ensureConfigWidgets(newConfig.id);
                 }
                await loadConfigurationDetails(newConfig.id);

                closeConfigModal();
                showNotification(`Configuration "${newConfigName.value}" created`, 'success');
            } catch (error) {
                const message = error.response?.data?.message || 'Failed to create configuration';
                showNotification(message, 'error');
            }
        };

        const cloneConfiguration = () => {
            cloneMode.value = true;
            newConfigName.value = (selectedConfig.value?.name || '') + ' (Copy)';
            newConfigDescription.value = '';
            showNewConfigModal.value = true;
        };

        const editConfiguration = () => {
            if (!selectedConfig.value) return;
            editConfigName.value = selectedConfig.value.name;
            editConfigDescription.value = selectedConfig.value.description || '';
            showEditConfigModal.value = true;
        };

        const updateConfiguration = async () => {
            if (!editConfigName.value || !selectedConfigId.value) return;

            try {
                const response = await configurationsApi.update(selectedConfigId.value, {
                    name: editConfigName.value,
                    description: editConfigDescription.value,
                });

                // Update local state
                const index = configurations.value.findIndex(c => c.id === selectedConfigId.value);
                if (index !== -1) {
                    configurations.value[index] = { ...configurations.value[index], ...response.data.data || response.data };
                }

                showEditConfigModal.value = false;
                showNotification('Configuration updated successfully', 'success');
            } catch (error) {
                const message = error.response?.data?.message || 'Failed to update configuration';
                showNotification(message, 'error');
            }
        };

        const deleteConfiguration = async () => {
            if (!selectedConfigId.value) return;

            if (!confirm(`Are you sure you want to delete "${selectedConfig.value?.name}"? This action cannot be undone.`)) {
                return;
            }

            try {
                await configurationsApi.delete(selectedConfigId.value);

                // Remove from local state
                const deletedId = selectedConfigId.value;
                configurations.value = configurations.value.filter(c => c.id !== selectedConfigId.value);

                // Clean per-config UI state and overrides
                if (overridesMap.value[deletedId]) delete overridesMap.value[deletedId];
                if (perConfigSelectedScreen.value[deletedId]) delete perConfigSelectedScreen.value[deletedId];
                if (perConfigEditingWidget.value[deletedId]) delete perConfigEditingWidget.value[deletedId];
                // Clean per-config widgets & themes
                cleanupConfigWidgets(deletedId);

                // Select first remaining config
                if (configurations.value.length > 0) {
                    selectedConfigId.value = configurations.value[0].id;
                    await loadConfigurationDetails(selectedConfigId.value);
                } else {
                    selectedConfigId.value = null;
                }

                showEditConfigModal.value = false;
                showNotification('Configuration deleted', 'success');
            } catch (error) {
                const message = error.response?.data?.message || 'Failed to delete configuration';
                showNotification(message, 'error');
            }
        };

        const closeConfigModal = () => {
            showNewConfigModal.value = false;
            newConfigName.value = '';
            newConfigDescription.value = '';
            cloneMode.value = false;
        };

        const activateConfiguration = async () => {
            if (!selectedConfigId.value) return;

            try {
                await configurationsApi.activate(selectedConfigId.value);

                // Update local state
                configurations.value.forEach(c => {
                    c.is_active = c.id === selectedConfigId.value;
                });

                showNotification('Configuration activated!', 'success');
            } catch (error) {
                showNotification('Failed to activate configuration', 'error');
            }
        };

        // Synchronize current UI state (screens/widgets) into overrides before saving
        const syncOverridesFromUI = () => {
            // sync screens
            (perConfigScreens.value || []).forEach(s => {
                const existing = screenOverrides.value.find(so => so.screen_id === s.id);
                if (existing) {
                    existing.is_visible = !!s.is_visible;
                    existing.display_order = s.display_order ?? 0;
                } else {
                    screenOverrides.value.push({
                        screen_id: s.id,
                        is_visible: !!s.is_visible,
                        display_order: s.display_order ?? 0,
                    });
                }
            });

            // sync widgets from the currently loaded screen (currentWidgets)
            (currentWidgets.value || []).forEach(w => {
                const existing = widgetOverrides.value.find(wo => parseInt(wo.screen_widget_id) === parseInt(w.id));
                // --- تخزين خصائص الأطفال مع جميع الخصائص المتقدمة ---
                let children = [];
                if (w.settings && Array.isArray(w.settings.children)) {
                    children = w.settings.children.map(child => ({
                        theme_child_id: child.theme_child_id,
                        is_visible: child.is_visible,
                        // الحجم
                        width: child.width,
                        height: child.height,
                        // الموقع
                        x: child.x,
                        y: child.y,
                        // الهوامش
                        margin_top: child.margin_top,
                        margin_bottom: child.margin_bottom,
                        margin_left: child.margin_left,
                        margin_right: child.margin_right,
                        padding: child.padding,
                        // التحويلات
                        rotation: child.rotation,
                        scale: child.scale,
                        opacity: child.opacity,
                        z_index: child.z_index,
                        // الأصول (assets)
                        assets: Array.isArray(child.assets) ? child.assets.map(asset => ({
                            id: asset.id,
                            asset_key: asset.asset_key,
                            name: asset.name,
                            is_visible: asset.is_visible,
                            width: asset.width,
                            height: asset.height,
                            x: asset.x,
                            y: asset.y,
                            opacity: asset.opacity,
                            scale: asset.scale,
                        })) : []
                    }));
                }
                if (existing) {
                    existing.is_visible = !!w.is_visible;
                    existing.display_order = w.display_order ?? 0;
                    existing.selected_theme_id = (w.selected_theme_id != null) ? parseInt(w.selected_theme_id) : existing.selected_theme_id ?? w.widget_theme_id ?? null;
                    existing.selected_child_theme_id = (w.selected_child_theme_id != null) ? parseInt(w.selected_child_theme_id) : existing.selected_child_theme_id ?? null;
                    existing.screen_id = existing.screen_id ?? (w.screen_id != null ? parseInt(w.screen_id) : (selectedScreen.value ? parseInt(selectedScreen.value.id) : null));
                    existing.settings = { ...w.settings };
                    if (existing.selected_theme_id != null) {
                        existing.settings.theme_id = parseInt(existing.selected_theme_id);
                    }
                    if (children.length) {
                        existing.settings.children = children;
                    }
                } else {
                    const resolvedScreenId = w.screen_id != null ? parseInt(w.screen_id) : (selectedScreen.value ? parseInt(selectedScreen.value.id) : null);
                    const payloadSettings = w.settings ? { ...w.settings } : {};
                    if (w.selected_theme_id ?? w.widget_theme_id ?? null) {
                        payloadSettings.theme_id = parseInt(w.selected_theme_id ?? w.widget_theme_id);
                    }
                    if (children.length) {
                        payloadSettings.children = children;
                    }
                    widgetOverrides.value.push({
                        screen_widget_id: parseInt(w.id),
                        screen_id: resolvedScreenId,
                        is_visible: !!w.is_visible,
                        display_order: w.display_order ?? 0,
                        selected_theme_id: w.selected_theme_id ?? w.widget_theme_id ?? null,
                        selected_child_theme_id: w.selected_child_theme_id ?? null,
                        settings: payloadSettings,
                    });
                }
            });
        };

        // Ensure selected_theme_id is set to a sensible default per widget if missing
        const ensureSelectedThemeDefaults = () => {
            const mapById = {};
            (screenWidgets.value || []).forEach(w => {
                mapById[parseInt(w.id)] = w;
            });

            widgetOverrides.value.forEach(wo => {
                const wid = parseInt(wo.screen_widget_id);
                if ((wo.selected_theme_id == null || wo.selected_theme_id === '') && mapById[wid]) {
                    const def = mapById[wid].widget_theme_id;
                    if (def != null) {
                        wo.selected_theme_id = parseInt(def);
                    }
                }
                if (wo.selected_theme_id != null) {
                    wo.settings = wo.settings || {};
                    wo.settings.theme_id = parseInt(wo.selected_theme_id);
                }
            });
        };

        const saveConfiguration = async () => {
	if (!selectedConfigId.value) return;

	// Synchronize UI -> overrides before building payload
	syncOverridesFromUI();
	// ensure defaults exist (selected_theme_id etc.)
	ensureSelectedThemeDefaults();

	// Always include overrides field (even if arrays are empty) to satisfy validation
	const payload = {
		overrides: {
			screen_overrides: screenOverrides.value || [],
			widget_overrides: widgetOverrides.value || [],
			asset_overrides: assetOverrides.value || [],
		}
	};

	try {
		console.log('[DashboardApp] Saving configuration...');

		// Try existing endpoints first (backwards-compatible)
		try {
			const screenResponse = await configurationsApi.updateScreenOverrides(selectedConfigId.value, { overrides: payload.overrides.screen_overrides });
			console.log('[DashboardApp] Screen overrides saved:', screenResponse.data);

			const widgetResponse = await configurationsApi.updateWidgetOverrides(selectedConfigId.value, { overrides: payload.overrides.widget_overrides });
			console.log('[DashboardApp] Widget overrides saved:', widgetResponse.data);

			// Note: asset overrides are handled via upload/remove endpoints elsewhere
			hasChanges.value = false;
			showNotification('Configuration saved successfully', 'success');
			return;
		} catch (e) {
			// If server complains about missing 'overrides', fall back to single update payload
			const isMissingOverridesError = !!(e.response?.data?.errors?.overrides) || (typeof e.response?.data?.message === 'string' && e.response.data.message.toLowerCase().includes('overrides'));
			if (!isMissingOverridesError) throw e;

			console.warn('[DashboardApp] update* endpoints rejected; trying single update with overrides payload...', e);
		}

		// Fallback: send full update with overrides field
		const resp = await configurationsApi.update(selectedConfigId.value, payload);
		const data = resp.data.data || resp.data;
		ensureConfigOverrides(selectedConfigId.value);
		overridesMap.value[selectedConfigId.value].screen_overrides = data.screen_overrides || data.overrides?.screen_overrides || payload.overrides.screen_overrides;
		overridesMap.value[selectedConfigId.value].widget_overrides = data.widget_overrides || data.overrides?.widget_overrides || payload.overrides.widget_overrides;
		overridesMap.value[selectedConfigId.value].asset_overrides = data.asset_overrides || data.overrides?.asset_overrides || payload.overrides.asset_overrides;

		hasChanges.value = false;
		showNotification('Configuration saved successfully', 'success');
	} catch (error) {
		console.error('[DashboardApp] Save error:', error);
		// If initial error indicates missing overrides, try one more time as fallback
		const isMissingOverridesError = !!(error.response?.data?.errors?.overrides) || (typeof error.response?.data?.message === 'string' && error.response.data.message.toLowerCase().includes('overrides'));
		if (isMissingOverridesError) {
			try {
				const resp = await configurationsApi.update(selectedConfigId.value, payload);
				const data = resp.data.data || resp.data;
				ensureConfigOverrides(selectedConfigId.value);
				overridesMap.value[selectedConfigId.value].screen_overrides = data.screen_overrides || data.overrides?.screen_overrides || payload.overrides.screen_overrides;
				overridesMap.value[selectedConfigId.value].widget_overrides = data.widget_overrides || data.overrides?.widget_overrides || payload.overrides.widget_overrides;
				overridesMap.value[selectedConfigId.value].asset_overrides = data.asset_overrides || data.overrides?.asset_overrides || payload.overrides.asset_overrides;

				hasChanges.value = false;
				showNotification('Configuration saved successfully', 'success');
				return;
			} catch (e2) {
				console.error('[DashboardApp] Save fallback failed:', e2);
			}
		}

		showNotification('Failed to save configuration', 'error');
	}
};

        // ==================== Screen/Widget Overrides ====================

        const toggleScreenVisibility = (screen) => {
            const existing = screenOverrides.value.find(so => so.screen_id === screen.id);
            if (existing) {
                existing.is_visible = !existing.is_visible;
            } else {
                screenOverrides.value.push({
                    screen_id: screen.id,
                    is_visible: false,
                    display_order: screen.display_order || 0,
                });
            }
            hasChanges.value = true;
        };

        const reorderScreens = (newOrder) => {
            newOrder.forEach((screen, index) => {
                const existing = screenOverrides.value.find(so => so.screen_id === screen.id);
                if (existing) {
                    existing.display_order = index;
                } else {
                    screenOverrides.value.push({
                        screen_id: screen.id,
                        is_visible: true,
                        display_order: index,
                    });
                }
            });
            hasChanges.value = true;
        };

        const toggleWidgetVisibility = (widget) => {
            const existing = widgetOverrides.value.find(wo => wo.screen_widget_id === widget.id);
            if (existing) {
                existing.is_visible = !existing.is_visible;
            } else {
                const screenId = widget.screen_id ?? selectedScreen.value?.id ?? null;
                widgetOverrides.value.push({
                    screen_widget_id: parseInt(widget.id),
                    screen_id: screenId != null ? parseInt(screenId) : null,
                    is_visible: false,
                    display_order: widget.order || 0,
                    selected_theme_id: widget.widget_theme_id,
                    settings: (() => {
                        const themeId = widget.widget_theme_id ?? null;
                        return themeId != null ? { theme_id: parseInt(themeId) } : {};
                    })(),
                });
            }
            hasChanges.value = true;
        };

        const updateWidgetTheme = (widget, themeId) => {
            const existing = widgetOverrides.value.find(wo => wo.screen_widget_id === widget.id);
            if (existing) {
                existing.selected_theme_id = themeId;
                existing.settings = existing.settings || {};
                existing.settings.theme_id = themeId;
            } else {
                const screenId = widget.screen_id ?? selectedScreen.value?.id ?? null;
                widgetOverrides.value.push({
                    screen_widget_id: parseInt(widget.id),
                    screen_id: screenId != null ? parseInt(screenId) : null,
                    is_visible: true,
                    display_order: widget.order || 0,
                    selected_theme_id: themeId,
                    settings: { theme_id: themeId },
                });
            }
            hasChanges.value = true;
        };



        const uploadAssetOverride = async (themeAssetId, file) => {
            if (!selectedConfigId.value) return;

            try {
                const response = await configurationsApi.uploadAssetOverride(
                    selectedConfigId.value,
                    themeAssetId,
                    file
                );

                const newOverride = response.data.data || response.data;
                // ensure override list exists for this config then push
                ensureConfigOverrides(selectedConfigId.value);
                overridesMap.value[selectedConfigId.value].asset_overrides.push(newOverride);

                showNotification('Asset uploaded successfully', 'success');
                hasChanges.value = true;
            } catch (error) {
                showNotification('Failed to upload asset', 'error');
            }
        };

        const removeAssetOverride = async (themeAssetId) => {
            if (!selectedConfigId.value) return;

            try {
                await configurationsApi.removeAssetOverride(selectedConfigId.value, themeAssetId);
                ensureConfigOverrides(selectedConfigId.value);
                overridesMap.value[selectedConfigId.value].asset_overrides =
                    overridesMap.value[selectedConfigId.value].asset_overrides.filter(
                        ao => ao.theme_asset_id !== themeAssetId
                    );
                showNotification('Asset override removed', 'success');
                hasChanges.value = true;
            } catch (error) {
                showNotification('Failed to remove asset override', 'error');
            }
        };

        // ==================== Original Screen/Widget Functions ====================

        const loadScreens = async () => {
            try {
                const response = await screensApi.getAll();
                screens.value = response.data.data || response.data;
                if (screens.value.length > 0) {
                    selectScreen(screens.value[0]);
                }
            } catch (error) {
                showNotification('Failed to load screens', 'error');
            }
        };

        const loadWidgets = async () => {
            try {
                const response = await widgetsApi.getAll();
                availableWidgets.value = response.data.data || response.data;

                // initialize per-config copies for current config if selected
                if (selectedConfigId.value) {
                    await ensureConfigWidgets(selectedConfigId.value);
                    // copy base if not already created
                    if (!perConfigAvailableWidgets.value[selectedConfigId.value] || perConfigAvailableWidgets.value[selectedConfigId.value].length === 0) {
                        perConfigAvailableWidgets.value[selectedConfigId.value] = (availableWidgets.value || []).map(w => ({ ...w }));
                    }
                }

                // Load themes for each widget
                for (const widget of availableWidgets.value) {
                    try {
                        const themesResponse = await widgetsApi.getThemes(widget.id);
                        widgetThemes.value[widget.id] = themesResponse.data.data || themesResponse.data;
                        // also seed per-config themes for selected config
                        if (selectedConfigId.value) {
                            await ensureConfigWidgets(selectedConfigId.value);
                            perConfigWidgetThemes.value[selectedConfigId.value][widget.id] = themesResponse.data.data || themesResponse.data || [];
                        }
                    } catch (e) {
                        widgetThemes.value[widget.id] = [];
                        if (selectedConfigId.value) {
                            await ensureConfigWidgets(selectedConfigId.value);
                            perConfigWidgetThemes.value[selectedConfigId.value][widget.id] = [];
                        }
                    }
                }
            } catch (error) {
                showNotification('Failed to load widgets', 'error');
            }
        };

        const selectScreen = async (screen) => {
            selectedScreen.value = screen;
            editingWidget.value = null;
            // save selected screen per current config
            if (selectedConfigId.value && screen?.id) {
                ensureConfigUiState(selectedConfigId.value);
                perConfigSelectedScreen.value[selectedConfigId.value] = screen.id;
                perConfigEditingWidget.value[selectedConfigId.value] = null;
            }

            try {
                const response = await screensApi.get(screen.screen_key, selectedConfigId.value, true);
                const fullScreen = response.data.data || response.data;
                screenWidgets.value = fullScreen.widgets || [];
                fillMissingSelectedThemeIds();
            } catch (error) {
                screenWidgets.value = [];
                showNotification('Failed to load screen widgets', 'error');
            }
        };

        const addWidget = async (widgetData) => {
            if (!selectedScreen.value) return;

            try {
                const response = await screenWidgetsApi.add(selectedScreen.value.id, widgetData);
                const newWidget = response.data.data || response.data;
                // ensure order/display_order are set locally so UI reflects immediately
                const nextOrder = currentWidgets.value.length;
                newWidget.order = nextOrder;
                newWidget.display_order = nextOrder;
                screenWidgets.value.push(newWidget);
                if (selectedConfigId.value) {
                    ensureConfigOverrides(selectedConfigId.value);
                    widgetOverrides.value.push({
                        screen_widget_id: parseInt(newWidget.id),
                        screen_id: parseInt(selectedScreen.value.id),
                        is_visible: true,
                        display_order: nextOrder,
                        selected_theme_id: newWidget.widget_theme_id ?? null,
                        selected_child_theme_id: null,
                        settings: (() => {
                            const themeId = newWidget.widget_theme_id ?? null;
                            return themeId != null ? { theme_id: parseInt(themeId) } : {};
                        })(),
                    });
                }
                hasChanges.value = true;
                showNotification('Widget added successfully', 'success');
            } catch (error) {
                showNotification('Failed to add widget', 'error');
            }
        };

        const updateWidgetOrder = (newOrder) => {
            console.log('[DashboardApp] updateWidgetOrder called with:', newOrder.map(w => ({ id: w.id, type: w.widget_type, idType: typeof w.id })));
            console.log('[DashboardApp] Current widgetOverrides before update:', widgetOverrides.value.map(wo => ({
                id: wo.id,
                screen_widget_id: wo.screen_widget_id,
                display_order: wo.display_order,
                idType: typeof wo.id,
                swIdType: typeof wo.screen_widget_id
            })));

            const fallbackScreenId = selectedScreen.value ? parseInt(selectedScreen.value.id) : null;

            newOrder.forEach((widget, index) => {
                // Find by screen_widget_id (from API)
                // Widget ID could be number or string, so convert both to numbers for comparison
                const widgetId = parseInt(widget.id);
                const existing = widgetOverrides.value.find(wo =>
                    parseInt(wo.screen_widget_id) === widgetId
                );

                console.log(`[DashboardApp] Widget ${widgetId}: existing override =`, existing);

                if (existing) {
                    console.log(`[DashboardApp] Updating existing override for widget ${widgetId} from order ${existing.display_order} to ${index}`);
                    existing.display_order = index;
                } else {
                    console.log(`[DashboardApp] Creating new override for widget ${widgetId} with order ${index}`);
                    const resolvedScreenId = widget.screen_id != null ? parseInt(widget.screen_id) : fallbackScreenId;
                    widgetOverrides.value.push({
                        screen_widget_id: widgetId,
                        screen_id: resolvedScreenId ?? null,
                        is_visible: true,
                        display_order: index,
                        selected_theme_id: widget.widget_theme_id,
                        settings: { theme_id: widget.widget_theme_id ?? null },
                    });
                }

                // Also update the base widget array so the UI reflects order without reload
                const baseIdx = screenWidgets.value.findIndex(w => parseInt(w.id) === widgetId);
                if (baseIdx !== -1) {
                    screenWidgets.value[baseIdx] = {
                        ...screenWidgets.value[baseIdx],
                        order: index,
                        display_order: index,
                    };
                }
            });

            console.log('[DashboardApp] widgetOverrides after update:', JSON.parse(JSON.stringify(widgetOverrides.value)));
            hasChanges.value = true;
        };

        const removeWidget = async (widget) => {
            if (!selectedScreen.value) return;

            try {
                await screenWidgetsApi.remove(selectedScreen.value.id, widget.id);
                screenWidgets.value = screenWidgets.value.filter(w => w.id !== widget.id);
                widgetOverrides.value = widgetOverrides.value.filter(wo => wo.screen_widget_id !== widget.id);
                if (editingWidget.value?.id === widget.id) {
                    editingWidget.value = null;
                }
                showNotification('Widget removed', 'success');
                hasChanges.value = true;
            } catch (error) {
                showNotification('Failed to remove widget', 'error');
            }
        };

        const editingWidgetThemes = ref([]);

        const editWidget = async (widget) => {
            // remember editing widget per-config
            if (selectedConfigId.value && widget?.id) {
                ensureConfigUiState(selectedConfigId.value);
                perConfigEditingWidget.value[selectedConfigId.value] = widget.id;
            }

            // Ensure widget has settings object
            widget.settings = widget.settings || {};
            widget.settings.children = widget.settings.children || [];

            try {
                // fetch themes using widget_key (safer for screen widget ids)
                const themes = await fetchThemesForWidget(widget);
                editingWidgetThemes.value = themes || [];
                console.log('themes for widget:', editingWidgetThemes.value);
                
                // Merge theme children with their assets into settings.children
                const selectedThemeId = widget.selected_theme_id ?? widget.settings?.theme_id ?? widget.widget_theme_id;
                console.log('🎯 [editWidget] selectedThemeId:', selectedThemeId);
                console.log('🎯 [editWidget] widget.settings before merge:', JSON.stringify(widget.settings));
                console.log('🎯 [editWidget] editingWidgetThemes.value.length:', editingWidgetThemes.value.length);
                console.log('🎯 [editWidget] editingWidgetThemes.value:', editingWidgetThemes.value);
                if (selectedThemeId && editingWidgetThemes.value.length > 0) {
                    console.log('🎯 [editWidget] Searching for theme with id:', selectedThemeId, 'type:', typeof selectedThemeId);
                    editingWidgetThemes.value.forEach((t, i) => {
                        console.log(`🎯 [editWidget] Theme ${i}: id=${t.id} (type: ${typeof t.id}), has children: ${!!t.children}, children count: ${t.children?.length || 0}`);
                    });
                    const selectedTheme = editingWidgetThemes.value.find(t => parseInt(t.id) === parseInt(selectedThemeId));
                    console.log('🎯 [editWidget] selectedTheme:', selectedTheme);
                    console.log('🎯 [editWidget] selectedTheme.children:', selectedTheme?.children);
                    if (selectedTheme && Array.isArray(selectedTheme.children)) {
                        // Ensure widget has settings.children
                        widget.settings = widget.settings || {};
                        widget.settings.children = widget.settings.children || [];
                        
                        // Merge theme children with assets
                        const existingChildrenMap = {};
                        widget.settings.children.forEach(c => {
                            existingChildrenMap[c.theme_child_id] = c;
                        });
                        
                        // Create merged children list from theme children
                        const mergedChildren = selectedTheme.children.map(themeChild => {
                            const existingOverride = existingChildrenMap[themeChild.id];
                            // Get assets from theme child or from child's theme
                            let assets = [];
                            console.log('🔍 [editWidget] themeChild:', themeChild.id, themeChild);
                            console.log('🔍 [editWidget] themeChild.theme:', themeChild.theme);
                            console.log('🔍 [editWidget] themeChild.theme?.assets:', themeChild.theme?.assets);
                            console.log('🔍 [editWidget] themeChild.assets:', themeChild.assets);
                            if (themeChild.theme && Array.isArray(themeChild.theme.assets)) {
                                assets = themeChild.theme.assets;
                                console.log('✅ [editWidget] Using themeChild.theme.assets:', assets.length);
                            } else if (Array.isArray(themeChild.assets)) {
                                assets = themeChild.assets;
                                console.log('✅ [editWidget] Using themeChild.assets:', assets.length);
                            } else {
                                console.log('❌ [editWidget] No assets found for child:', themeChild.id);
                            }
                            
                            return {
                                theme_child_id: themeChild.id,
                                name: themeChild.name || themeChild.child_key || `Child ${themeChild.id}`,
                                child_key: themeChild.child_key,
                                is_visible: existingOverride?.is_visible ?? true,
                                width: existingOverride?.width ?? themeChild.width ?? 300,
                                height: existingOverride?.height ?? themeChild.height ?? 200,
                                x: existingOverride?.x ?? themeChild.x ?? 0,
                                y: existingOverride?.y ?? themeChild.y ?? 0,
                                rotation: existingOverride?.rotation ?? themeChild.rotation ?? 0,
                                scale: existingOverride?.scale ?? themeChild.scale ?? 1,
                                opacity: existingOverride?.opacity ?? themeChild.opacity ?? 1,
                                padding: existingOverride?.padding ?? themeChild.padding ?? '0',
                                margin: existingOverride?.margin ?? themeChild.margin ?? '0',
                                gap: existingOverride?.gap ?? themeChild.gap ?? 0,
                                // Merge assets - keep override values if they exist
                                assets: assets.map(asset => {
                                    const existingAsset = (existingOverride?.assets || []).find(a => 
                                        a.id === asset.id || a.asset_key === asset.asset_key
                                    );
                                    return {
                                        id: asset.id,
                                        asset_key: asset.asset_key || asset.name || asset.asset_label,
                                        name: asset.name || asset.asset_label || asset.asset_key,
                                        file_url: asset.file_url || asset.url || asset.default_url,
                                        url: asset.file_url || asset.url || asset.default_url,
                                        width: existingAsset?.width ?? asset.width ?? 80,
                                        height: existingAsset?.height ?? asset.height ?? 80,
                                        x: existingAsset?.x ?? asset.x ?? 0,
                                        y: existingAsset?.y ?? asset.y ?? 0,
                                        opacity: existingAsset?.opacity ?? asset.opacity ?? 1,
                                        z_index: existingAsset?.z_index ?? asset.z_index ?? 0,
                                        is_visible: existingAsset?.is_visible ?? asset.is_visible ?? true,
                                        scale: existingAsset?.scale ?? asset.scale ?? 1,
                                    };
                                })
                            };
                        });
                        
                        widget.settings.children = mergedChildren;
                        console.log('✅ [editWidget] Merged children with assets:', mergedChildren);
                        mergedChildren.forEach((c, i) => {
                            console.log(`✅ [editWidget] Child ${i} (${c.theme_child_id}): ${c.assets?.length || 0} assets`);
                        });
                    }
                }
                
                // Set editingWidget AFTER merging assets so Vue detects the complete data
                const finalWidget = { ...widget, settings: { ...widget.settings } };
                console.log('🚀 [editWidget] Final editingWidget:', finalWidget);
                console.log('🚀 [editWidget] Final children:', finalWidget.settings?.children);
                editingWidget.value = finalWidget;
            } catch (error) {
                editingWidgetThemes.value = [];
                editingWidget.value = widget;
            }
        };

        // Allow selecting a child-theme variant per widget in the current configuration
        const setWidgetChildTheme = (widget, childThemeId) => {
            const existing = widgetOverrides.value.find(wo => parseInt(wo.screen_widget_id) === parseInt(widget.id));
            if (existing) {
                existing.selected_child_theme_id = childThemeId;
            } else {
                const screenId = widget.screen_id ?? selectedScreen.value?.id ?? null;
                widgetOverrides.value.push({
                    screen_widget_id: parseInt(widget.id),
                    screen_id: screenId != null ? parseInt(screenId) : null,
                    is_visible: true,
                    display_order: widget.order || 0,
                    selected_theme_id: widget.widget_theme_id,
                    selected_child_theme_id: childThemeId,
                    settings: { theme_id: widget.widget_theme_id ?? null },
                });
            }
            hasChanges.value = true;
        };

        const closeWidgetEditor = () => {
            if (selectedConfigId.value) {
                ensureConfigUiState(selectedConfigId.value);
                perConfigEditingWidget.value[selectedConfigId.value] = null;
            }
            editingWidget.value = null;
        };

        const findBaseWidget = (widgetId) => {
            return (screenWidgets.value || []).find(w => parseInt(w.id) === parseInt(widgetId)) ||
                currentWidgets.value.find(w => parseInt(w.id) === parseInt(widgetId)) ||
                null;
        };

        const buildWidgetOverridePayload = (widgetId, settings = {}) => {
            const existing = widgetOverrides.value.find(wo => parseInt(wo.screen_widget_id) === parseInt(widgetId));
            const baseWidget = findBaseWidget(widgetId);
            const screenId = existing?.screen_id
                ?? (baseWidget?.screen_id != null ? parseInt(baseWidget.screen_id) : null)
                ?? (selectedScreen.value ? parseInt(selectedScreen.value.id) : null);
            const normalizedSettings = settings ? { ...settings } : {};
            const selectedThemeId = normalizedSettings.theme_id
                ?? existing?.selected_theme_id
                ?? baseWidget?.selected_theme_id
                ?? baseWidget?.widget_theme_id
                ?? null;

            if (selectedThemeId != null) {
                normalizedSettings.theme_id = parseInt(selectedThemeId);
            }

            return {
                screen_widget_id: parseInt(widgetId),
                screen_id: screenId,
                is_visible: existing?.is_visible ?? true,
                display_order: existing?.display_order ?? baseWidget?.display_order ?? baseWidget?.order ?? 0,
                selected_theme_id: selectedThemeId != null ? parseInt(selectedThemeId) : null,
                selected_child_theme_id: existing?.selected_child_theme_id ?? null,
                settings: normalizedSettings,
            };
        };

        const upsertWidgetOverride = (payload) => {
            const existing = widgetOverrides.value.find(wo => parseInt(wo.screen_widget_id) === parseInt(payload.screen_widget_id));
            if (existing) {
                existing.screen_id = payload.screen_id;
                existing.is_visible = payload.is_visible;
                existing.display_order = payload.display_order;
                existing.selected_theme_id = payload.selected_theme_id;
                existing.selected_child_theme_id = payload.selected_child_theme_id;
                existing.settings = { ...payload.settings };
            } else {
                widgetOverrides.value.push({
                    ...payload,
                    settings: { ...payload.settings },
                });
            }
        };

        const updateWidgetSettings = async (widgetId, settings) => {
            // Persist per-config widget settings when a config is active
            if (selectedConfigId.value) {
                try {

                    const payload = buildWidgetOverridePayload(widgetId, settings);
                    await configurationsApi.updateWidgetOverrides(selectedConfigId.value, { overrides: [payload] });
                    upsertWidgetOverride(payload);
                    const refreshedWidget = currentWidgets.value.find(w => parseInt(w.id) === parseInt(widgetId));
                    if (refreshedWidget) {
                        editingWidget.value = { ...refreshedWidget };
                    }
                    hasChanges.value = false;
                    showNotification('Widget settings saved for this configuration', 'success');
                    return;
                } catch (error) {
                    showNotification('Failed to save widget settings for configuration', 'error');
                    return;
                }
            }

            // Fallback: update global screen widget (no config)
            if (!selectedScreen.value) return;
            try {
                const response = await screenWidgetsApi.update(selectedScreen.value.id, widgetId, settings);
                const updatedWidget = response.data.data || response.data;
                const index = screenWidgets.value.findIndex(w => w.id === widgetId);
                if (index !== -1) {
                    screenWidgets.value[index] = { ...screenWidgets.value[index], ...updatedWidget };
                }
                hasChanges.value = true;
                showNotification('Widget updated', 'success');
            } catch (error) {
                showNotification('Failed to update widget', 'error');
            }
        };

        const previewScreen = () => {
            showPreview.value = true;
        };

        const getThemesForWidget = (widget) => {
            if (!widget) return [];
            const widgetDef = availableWidgets.value.find(w => w.widget_key === widget.widget_key);
            const defId = widgetDef?.id;
            // prefer per-config themes if present
            if (selectedConfigId.value && perConfigWidgetThemes.value[selectedConfigId.value]?.[defId]) {
                return perConfigWidgetThemes.value[selectedConfigId.value][defId] || [];
            }
            return widgetDef ? (widgetThemes.value[widgetDef.id] || []) : [];
        };

        const showNotification = (message, type = 'info') => {
            notification.value = { message, type };
            setTimeout(() => {
                notification.value = null;
            }, 3000);
        };

        const fetchThemesForWidget = async (widget) => {
                if (!widget) return [];

                console.log('editingWidget:', widget);

                // Try using widget.id (screen widget id) first - this is what has themes associated
                // Then fall back to widget definition id if needed
                const widgetDef = availableWidgets.value.find(w => w.widget_key === widget.widget_key);
                const widgetIdToUse = widget.id || widgetDef?.id;
                
                if (!widgetIdToUse) return [];

                console.log('widgetDef:', widgetDef);
                console.log('🎯 Using widget ID for themes:', widgetIdToUse, '(widget.id:', widget.id, ', widgetDef.id:', widgetDef?.id, ')');

                try {
                    // First try with widget.id (screen widget id)
                    let response = await widgetsApi.getThemes(widget.id);
                    let themes = response.data.data || [];
                    
                    console.log('themes from widget.id:', widget.id, '→', themes.length, 'themes');
                    
                    // If empty and we have a different widgetDef.id, try that
                    if (themes.length === 0 && widgetDef && widgetDef.id !== widget.id) {
                        console.log('🔄 No themes found, trying widgetDef.id:', widgetDef.id);
                        response = await widgetsApi.getThemes(widgetDef.id);
                        themes = response.data.data || [];
                        console.log('themes from widgetDef.id:', widgetDef.id, '→', themes.length, 'themes');
                    }

                    // store per-config
                    if (selectedConfigId.value && widgetDef) {
                        await ensureConfigWidgets(selectedConfigId.value);
                        perConfigWidgetThemes.value[selectedConfigId.value][widgetDef.id] = themes;
                    }

                    console.log('themes returned from API:', themes);
                    console.log('response returned from API:', response);

                    return themes;
                } catch (error) {
                    console.error('Failed to fetch themes for widget', error);
                    return [];
                }
            };


        const onWidgetDragStart = (widget) => {
            // Handle drag start from library
        };

        // expose per-config helpers if needed (mostly internal)
        return {
            // Configuration
            configurations,
            selectedConfigId,
            selectedConfig,
            showNewConfigModal,
            newConfigName,
            newConfigDescription,
            cloneMode,
            showEditConfigModal,
            editConfigName,
            editConfigDescription,
            currentScreenOverrides,
            currentWidgetOverrides,
            currentAssetOverrides,
            currentAvailableWidgets,
            perConfigScreens,
            onConfigurationChange,
            createConfiguration,
            cloneConfiguration,
            editConfiguration,
            updateConfiguration,
            deleteConfiguration,
            closeConfigModal,
            activateConfiguration,
            saveConfiguration,
            toggleScreenVisibility,
            reorderScreens,
            toggleWidgetVisibility,
            updateWidgetTheme,
            setWidgetChildTheme,
            uploadAssetOverride,
            removeAssetOverride,
            // per-config change state
            hasChanges,

            // Screens/Widgets
            screens,
            selectedScreen,
            currentWidgets,
            availableWidgets,
            editingWidget,
            editingWidgetThemes,
            showPreview,
            // hasChanges (kept above per-config)
             notification,
             previewData,
             selectScreen,
             addWidget,
             updateWidgetOrder,
             removeWidget,
             editWidget,
             updateWidgetSettings,
             closeWidgetEditor,
             previewScreen,
             getThemesForWidget,
             onWidgetDragStart,
             fetchThemesForWidget,
             // filtered library for selected screen
             filteredAvailableWidgets,
             // Children update handler
             onChildrenUpdated: (updatedChildren) => {
                 if (editingWidget.value && editingWidget.value.settings) {
                     editingWidget.value.settings.children = updatedChildren;
                     hasChanges.value = true;
                 }
             },
             
             // Visual Designer
             showVisualDesigner,
             visualDesignerRef,
             openVisualDesigner: () => {
                 showVisualDesigner.value = true;
                 setTimeout(() => {
                     if (visualDesignerRef.value) {
                         visualDesignerRef.value.open();
                     }
                 }, 100);
             },
             closeVisualDesigner: () => {
                 showVisualDesigner.value = false;
             },
             onVisualDesignerSave: async (designData) => {
                 if (editingWidget.value && editingWidget.value.settings) {
                     editingWidget.value.settings.theme_id = designData.theme_id;
                     editingWidget.value.settings.children = designData.children;
                     hasChanges.value = true;
                     
                     // Auto save to configuration
                     try {
                         if (selectedConfigId.value && editingWidget.value.id) {
                             await configurationsApi.update(selectedConfigId.value, {
                                 widget_overrides: [{
                                     screen_widget_id: editingWidget.value.id,
                                     settings: editingWidget.value.settings,
                                 }]
                             });
                             console.log('✅ Configuration auto-saved');
                         }
                     } catch (error) {
                         console.error('Failed to auto-save configuration:', error);
                     }
                 }
             },
        };
    },
};
</script>
