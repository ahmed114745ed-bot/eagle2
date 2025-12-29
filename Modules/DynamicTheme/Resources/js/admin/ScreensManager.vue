<template>
    <div>
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-white">📱 Screens Manager</h2>
            <button
                @click="openCreateModal"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center"
            >
                <span class="mr-2">➕</span> Create Screen
            </button>
        </div>

        <!-- Screens List -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div
                v-for="screen in screens"
                :key="screen.id"
                class="bg-gray-800 rounded-lg overflow-hidden border border-gray-700"
            >
                <!-- Screen Header -->
                <div class="p-4 border-b border-gray-700">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-semibold text-white">{{ screen.screen_name }}</h3>
                            <p class="text-sm text-gray-400 font-mono">{{ screen.screen_key }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span
                                :class="[
                                    'px-2 py-1 text-xs rounded',
                                    screen.is_active ? 'bg-green-900 text-green-300' : 'bg-gray-600 text-gray-400'
                                ]"
                            >
                                {{ screen.is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <span class="px-2 py-1 text-xs bg-blue-900 text-blue-300 rounded">
                                Order: {{ screen.display_order }}
                            </span>
                        </div>
                    </div>
                    <p v-if="screen.description" class="text-sm text-gray-500 mt-2">{{ screen.description }}</p>
                </div>

                <!-- Widgets Preview -->
                <div class="p-4">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="text-sm font-medium text-gray-400">
                            Widgets ({{ screen.widgets?.length || 0 }})
                        </h4>
                        <button
                            @click="openScreenBuilder(screen)"
                            class="text-sm text-blue-400 hover:text-blue-300"
                        >
                            🛠️ Edit Layout
                        </button>

                            <button
                                @click="openAllowedWidgetsModal(screen)"
                                class="text-sm text-green-400 hover:text-green-300"
                            >
                                🛠️ Allowed Widgets
                            </button>
                    </div>
                    <div class="space-y-2">
                        <div
                            v-for="widget in (screen.widgets || []).slice(0, 3)"
                            :key="widget.id"
                            class="flex items-center justify-between bg-gray-700 rounded p-2"
                        >
                            <div class="flex items-center space-x-2">
                                <span class="text-lg">{{ getWidgetIcon(widget.widget?.widget_type) }}</span>
                                <span class="text-white text-sm">{{ widget.widget?.display_name }}</span>
                            </div>
                            <span class="text-xs text-gray-400">
                                {{ widget.theme?.theme_name || 'No Theme' }}
                            </span>
                        </div>
                        <div v-if="(screen.widgets?.length || 0) > 3" class="text-center">
                            <span class="text-xs text-gray-500">+{{ screen.widgets.length - 3 }} more widgets</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="p-4 border-t border-gray-700 flex justify-between">
                    <button
                        @click="previewScreen(screen)"
                        class="px-3 py-1 text-sm bg-purple-600 text-white rounded hover:bg-purple-700"
                    >
                        👁️ Preview
                    </button>
                    <div class="flex space-x-2">
                        <button
                            @click="editScreen(screen)"
                            class="px-3 py-1 text-sm text-blue-400 hover:text-blue-300"
                        >
                            ✏️ Edit
                        </button>
                        <button
                            @click="duplicateScreen(screen)"
                            class="px-3 py-1 text-sm text-yellow-400 hover:text-yellow-300"
                        >
                            📋 Clone
                        </button>
                        <button
                            @click="confirmDelete(screen)"
                            class="px-3 py-1 text-sm text-red-400 hover:text-red-300"
                        >
                            🗑️
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="screens.length === 0" class="text-center py-12">
            <p class="text-gray-500">No screens found. Create your first screen!</p>
        </div>

        <!-- Create/Edit Screen Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-[500px]">
                <h3 class="text-lg font-semibold text-white mb-4">
                    {{ editingScreen ? '✏️ Edit Screen' : '➕ Create Screen' }}
                </h3>
                <form @submit.prevent="saveScreen" class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Screen Key</label>
                        <input
                            v-model="formData.screen_key"
                            type="text"
                            class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white"
                            placeholder="e.g., home_hot"
                            required
                        />
                        <p class="text-xs text-gray-500 mt-1">Unique identifier for API calls</p>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Screen Name</label>
                        <input
                            v-model="formData.screen_name"
                            type="text"
                            class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white"
                            placeholder="e.g., Home - Hot Tab"
                            required
                        />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Description</label>
                        <textarea
                            v-model="formData.description"
                            class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white"
                            rows="2"
                            placeholder="Screen description..."
                        ></textarea>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Display Order</label>
                        <input
                            v-model.number="formData.display_order"
                            type="number"
                            class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white"
                        />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Max Widgets</label>
                        <input
                            v-model.number="formData.max_widgets"
                            type="number"
                            min="1"
                            class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white"
                            placeholder="e.g., 5"
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            Maximum number of widgets allowed on this screen
                        </p>
                    </div>

                    <div class="flex items-center">
                        <label class="flex items-center text-gray-300">
                            <input
                                v-model="formData.is_active"
                                type="checkbox"
                                class="mr-2 rounded bg-gray-600 border-gray-500"
                            />
                            Active
                        </label>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-500"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                        >
                            {{ editingScreen ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Screen Builder Modal -->
        <div v-if="showBuilder" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-[900px] max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-white">
                        🛠️ Screen Builder: "{{ selectedScreen?.screen_name }}"
                    </h3>
                    <button @click="closeBuilder" class="text-gray-400 hover:text-white">✕</button>
                </div>

                <div class="grid grid-cols-3 gap-6">
                    <!-- Available Widgets -->
                    <div class="col-span-1">
                        <h4 class="text-sm font-medium text-gray-400 mb-3">📦 Available Widgets</h4>
                        <div class="space-y-2 max-h-[400px] overflow-y-auto">
                            <div
                                v-for="widget in availableWidgets"
                                :key="widget.id"
                                class="bg-gray-700 rounded p-3 cursor-pointer hover:bg-gray-600"
                                @click="addWidgetToScreen(widget)"
                            >
                                <div class="flex items-center space-x-2">
                                    <span class="text-lg">{{ getWidgetIcon(widget.widget_type) }}</span>
                                    <span class="text-white text-sm">{{ widget.display_name }}</span>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">{{ widget.themes?.length || 0 }} themes</p>
                            </div>
                        </div>
                    </div>

                    <!-- Screen Layout -->
                    <div class="col-span-2">
                        <h4 class="text-sm font-medium text-gray-400 mb-3">📱 Screen Layout</h4>
                        <div class="bg-gray-700 rounded-lg p-4 min-h-[400px]">
                            <div
                                v-for="(item, index) in screenWidgets"
                                :key="item.id || index"
                                class="flex items-center justify-between bg-gray-600 rounded p-3 mb-2"
                            >
                                <div class="flex items-center space-x-4">
                                    <span class="text-gray-400">{{ index + 1 }}</span>
                                    <span class="text-lg">{{ getWidgetIcon(item.widget?.widget_type) }}</span>
                                    <span class="text-white">{{ item.widget?.display_name }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <select
                                        v-model="item.widget_theme_id"
                                        class="px-2 py-1 bg-gray-500 border border-gray-400 rounded text-white text-sm"
                                        @change="updateWidgetTheme(item)"
                                    >
                                        <option value="">No Theme</option>
                                        <option
                                            v-for="theme in getWidgetThemes(item.widget_id)"
                                            :key="theme.id"
                                            :value="theme.id"
                                        >
                                            {{ theme.theme_name }}
                                        </option>
                                    </select>
                                    <button
                                        @click="moveWidgetUp(index)"
                                        :disabled="index === 0"
                                        class="text-gray-400 hover:text-white disabled:opacity-30"
                                    >
                                        ⬆️
                                    </button>
                                    <button
                                        @click="moveWidgetDown(index)"
                                        :disabled="index === screenWidgets.length - 1"
                                        class="text-gray-400 hover:text-white disabled:opacity-30"
                                    >
                                        ⬇️
                                    </button>
                                    <button
                                        @click="removeWidgetFromScreen(index)"
                                        class="text-red-400 hover:text-red-300"
                                    >
                                        🗑️
                                    </button>
                                </div>
                            </div>
                            <div v-if="screenWidgets.length === 0" class="text-center py-12">
                                <p class="text-gray-500">Click widgets to add them to the screen</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button
                        @click="closeBuilder"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-500"
                    >
                        Cancel
                    </button>
                    <button
                        @click="saveScreenLayout"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                    >
                        💾 Save Layout
                    </button>
                </div>
            </div>
        </div>

        <!-- Preview Modal -->
        <div v-if="showPreview" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-[400px] max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-white">👁️ Preview: {{ previewData?.screen_name }}</h3>
                    <button @click="showPreview = false" class="text-gray-400 hover:text-white">✕</button>
                </div>
                <div class="bg-gray-900 rounded-lg p-4">
                    <pre class="text-xs text-green-400 overflow-x-auto">{{ JSON.stringify(previewData, null, 2) }}</pre>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-[400px]">
                <h3 class="text-lg font-semibold text-white mb-4">⚠️ Delete Screen</h3>
                <p class="text-gray-300 mb-6">
                    Are you sure you want to delete "{{ screenToDelete?.screen_name }}"?
                    This will remove all widget configurations for this screen.
                </p>
                <div class="flex justify-end space-x-3">
                    <button
                        @click="showDeleteModal = false"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-500"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deleteScreen"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Allowed Widgets Modal -->

<div
    v-if="showAllowedWidgetsModal"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
>
    <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-[900px] max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-white">
                🛠 Allowed Widgets – "{{ selectedScreen?.screen_name }}"
            </h3>
            <button @click="closeAllowedWidgetsModal" class="text-gray-400 hover:text-white">✕</button>
        </div>

        <!-- Content -->
        <div class="grid grid-cols-3 gap-6">
            <!-- Not Allowed -->
            <div>
                <h4 class="text-sm text-gray-400 mb-3">🚫 Not Allowed</h4>
                <div class="space-y-2 max-h-[400px] overflow-y-auto">
                    <div
                        v-for="widget in notAllowedWidgets"
                        :key="widget.id"
                        class="bg-gray-700 p-3 rounded flex justify-between items-center"
                    >
                        <div class="flex items-center gap-2">
                            <span>{{ getWidgetIcon(widget.widget_type) }}</span>
                            <span class="text-white text-sm">{{ widget.display_name }}</span>
                        </div>
                        <button
                            @click="allowWidget(widget.id)"
                            class="text-green-400 hover:text-green-300"
                        >
                            ➡️
                        </button>
                    </div>
                </div>
            </div>

            <!-- Center arrows -->
            <div class="flex items-center justify-center text-gray-500">
                ⬅️ ➡️
            </div>

            <!-- Allowed -->
            <div>
                <h4 class="text-sm text-gray-400 mb-3">✅ Allowed</h4>
                <div class="space-y-2 max-h-[400px] overflow-y-auto">
                    <div
                        v-for="widget in allowedWidgets"
                        :key="widget.id"
                        class="bg-gray-700 p-3 rounded flex justify-between items-center"
                    >
                        <div class="flex items-center gap-2">
                            <span>{{ getWidgetIcon(widget.widget_type) }}</span>
                            <span class="text-white text-sm">{{ widget.display_name }}</span>
                        </div>
                        <button
                            @click="disallowWidget(widget.id)"
                            class="text-red-400 hover:text-red-300"
                        >
                            ⬅️
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end gap-3 mt-6">
            <button
                @click="closeAllowedWidgetsModal"
                class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-500"
            >
                Cancel
            </button>
            <button
                @click="saveAllowedWidgets"
                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
            >
                💾 Save
            </button>
        </div>
    </div>
</div>


</template>


<script>
import { ref, onMounted, computed } from 'vue';
import { adminApi } from '../services/adminApi';

export default {
    name: 'ScreensManager',
    emits: ['notify'],
    setup(props, { emit }) {

        /* ================= State ================= */
        const screens = ref([]);
        const availableWidgets = ref([]);
        const themes = ref([]);

        const showModal = ref(false);
        const showBuilder = ref(false);
        const showPreview = ref(false);
        const showDeleteModal = ref(false);
        const showAllowedWidgetsModal = ref(false);

        const editingScreen = ref(null);
        const selectedScreen = ref(null);
        const screenToDelete = ref(null);

        const previewData = ref(null);
        const screenWidgets = ref([]);
        const allowedWidgetIds = ref([]);

        const formData = ref({
            screen_key: '',
            screen_name: '',
            description: '',
            display_order: 0,
            max_widgets: 5,
            is_active: true,
            
        });

        /* ================= Helpers ================= */
        const getWidgetIcon = (type) => {
            const icons = {
                banner: '🖼️',
                horizontal_list: '📋',
                vertical_list: '📃',
                grid: '⊞',
                tab_container: '📑',
                single_item: '🔲',
                custom: '🎨',
            };
            return icons[type] || '📦';
        };

        const getWidgetThemes = (widgetId) =>
            themes.value.filter(t => t.widget_id === widgetId);

        /* ================= Computed ================= */
        const allowedWidgets = computed(() =>
            availableWidgets.value.filter(w =>
                allowedWidgetIds.value.includes(w.id)
            )
        );

        const notAllowedWidgets = computed(() =>
            availableWidgets.value.filter(w =>
                !allowedWidgetIds.value.includes(w.id)
            )
        );

        /* ================= Lifecycle ================= */
        onMounted(async () => {
            await loadScreens();
            await loadWidgets();
            await loadThemes();
        });

        /* ================= Loaders ================= */
        const loadScreens = async () => {
            try {
                const res = await adminApi.screens.list();
                screens.value = res.data.data || res.data;
            } catch {
                emit('notify', { type: 'error', message: 'Failed to load screens' });
            }
        };

        const loadWidgets = async () => {
            try {
                const res = await adminApi.widgets.list();
                availableWidgets.value = res.data.data || res.data;
            } catch (e) {
                console.error('Failed to load widgets', e);
            }
        };

        const loadThemes = async () => {
            try {
                const res = await adminApi.themes.list();
                themes.value = res.data.data || res.data;
            } catch (e) {
                console.error('Failed to load themes', e);
            }
        };

        /* ================= Screen CRUD ================= */
        const openCreateModal = () => {
            editingScreen.value = null;
            formData.value = {
                screen_key: '',
                screen_name: '',
                description: '',
                display_order: screens.value.length,
                is_active: true,
            };
            showModal.value = true;
        };

        const editScreen = (screen) => {
            editingScreen.value = screen;
            formData.value = {
                screen_key: screen.screen_key,
                screen_name: screen.screen_name,
                description: screen.description || '',
                display_order: screen.display_order,
                max_widgets: screen.max_widgets ,
                is_active: screen.is_active,
            };
            showModal.value = true;
        };

        const closeModal = () => {
            showModal.value = false;
            editingScreen.value = null;
        };

        const saveScreen = async () => {
            try {
                if (editingScreen.value) {
                    await adminApi.screens.update(editingScreen.value.id, formData.value);
                    emit('notify', { type: 'success', message: 'Screen updated successfully' });
                } else {
                    await adminApi.screens.create(formData.value);
                    emit('notify', { type: 'success', message: 'Screen created successfully' });
                }
                await loadScreens();
                closeModal();
            } catch {
                emit('notify', { type: 'error', message: 'Failed to save screen' });
            }
        };

        /* ================= Screen Builder ================= */
        const openScreenBuilder = (screen) => {
            selectedScreen.value = screen;
            screenWidgets.value = (screen.widgets || []).map(w => ({
                ...w,
                widget: availableWidgets.value.find(aw => aw.id === w.widget_id),
            }));
            showBuilder.value = true;
        };

        const closeBuilder = () => {
            showBuilder.value = false;
            selectedScreen.value = null;
            screenWidgets.value = [];
        };

        const addWidgetToScreen = (widget) => {
            const defaultTheme = themes.value.find(
                t => t.widget_id === widget.id && t.is_default
            );

            screenWidgets.value.push({
                widget_id: widget.id,
                widget,
                widget_theme_id: defaultTheme?.id || null,
                display_order: screenWidgets.value.length,
                is_active: true,
            });
        };

        const removeWidgetFromScreen = (index) => {
            screenWidgets.value.splice(index, 1);
        };

        const moveWidgetUp = (index) => {
            if (index > 0) {
                [screenWidgets.value[index - 1], screenWidgets.value[index]] =
                    [screenWidgets.value[index], screenWidgets.value[index - 1]];
            }
        };

        const moveWidgetDown = (index) => {
            if (index < screenWidgets.value.length - 1) {
                [screenWidgets.value[index + 1], screenWidgets.value[index]] =
                    [screenWidgets.value[index], screenWidgets.value[index + 1]];
            }
        };

        const updateWidgetTheme = () => {};

        const saveScreenLayout = async () => {
            try {
                const widgets = screenWidgets.value.map((w, index) => ({
                    widget_id: w.widget_id,
                    widget_theme_id: w.widget_theme_id,
                    display_order: index,
                    is_active: true,
                }));

                await adminApi.screens.updateWidgets(selectedScreen.value.id, { widgets });
                emit('notify', { type: 'success', message: 'Screen layout saved successfully' });
                await loadScreens();
                closeBuilder();
            } catch {
                emit('notify', { type: 'error', message: 'Failed to save screen layout' });
            }
        };

        /* ================= Preview / Duplicate / Delete ================= */
        const previewScreen = async (screen) => {
            try {
                const res = await adminApi.screens.preview(screen.id);
                previewData.value = res.data;
                showPreview.value = true;
            } catch {
                emit('notify', { type: 'error', message: 'Failed to load preview' });
            }
        };

        const duplicateScreen = async (screen) => {
            try {
                await adminApi.screens.duplicate(screen.id);
                emit('notify', { type: 'success', message: 'Screen duplicated successfully' });
                await loadScreens();
            } catch {
                emit('notify', { type: 'error', message: 'Failed to duplicate screen' });
            }
        };

        const confirmDelete = (screen) => {
            screenToDelete.value = screen;
            showDeleteModal.value = true;
        };

        const deleteScreen = async () => {
            try {
                await adminApi.screens.delete(screenToDelete.value.id);
                emit('notify', { type: 'success', message: 'تم حذف الشاشة وتم حفظ التغييرات' });
                await loadScreens();
            } catch {
                emit('notify', { type: 'error', message: 'Failed to delete screen' });
            } finally {
                showDeleteModal.value = false;
                screenToDelete.value = null;
            }
        };

        /* ================= Allowed Widgets ================= */
        const openAllowedWidgetsModal = async (screen) => {
            selectedScreen.value = screen;
            try {
                const res = await adminApi.screens.allowedWidgets(screen.id);
                allowedWidgetIds.value = res.data.data.map(w => w.id);
            } catch {
                allowedWidgetIds.value = [];
            }
            showAllowedWidgetsModal.value = true;
        };

        const closeAllowedWidgetsModal = () => {
            showAllowedWidgetsModal.value = false;
            selectedScreen.value = null;
            allowedWidgetIds.value = [];
        };

        const allowWidget = (id) => {
            if (!allowedWidgetIds.value.includes(id)) {
                allowedWidgetIds.value.push(id);
            }
        };

        const disallowWidget = (id) => {
            allowedWidgetIds.value =
                allowedWidgetIds.value.filter(wid => wid !== id);
        };

        const saveAllowedWidgets = async () => {
            try {
                await adminApi.screens.updateAllowedWidgets(
                    selectedScreen.value.id,
                    { widget_ids: allowedWidgetIds.value }
                );
                emit('notify', { type: 'success', message: 'Allowed widgets updated successfully' });
                closeAllowedWidgetsModal();
            } catch {
                emit('notify', { type: 'error', message: 'Failed to save allowed widgets' });
            }
        };

        /* ================= Return ================= */
        return {
            screens,
            availableWidgets,
            themes,

            showModal,
            showBuilder,
            showPreview,
            showDeleteModal,
            showAllowedWidgetsModal,

            editingScreen,
            selectedScreen,
            screenToDelete,
            previewData,
            screenWidgets,

            formData,
            allowedWidgets,
            notAllowedWidgets,

            getWidgetIcon,
            getWidgetThemes,

            openCreateModal,
            editScreen,
            closeModal,
            saveScreen,

            openScreenBuilder,
            closeBuilder,
            addWidgetToScreen,
            removeWidgetFromScreen,
            moveWidgetUp,
            moveWidgetDown,
            updateWidgetTheme,
            saveScreenLayout,

            previewScreen,
            duplicateScreen,
            confirmDelete,
            deleteScreen,

            openAllowedWidgetsModal,
            closeAllowedWidgetsModal,
            allowWidget,
            disallowWidget,
            saveAllowedWidgets,
        };
    },
};
</script>
