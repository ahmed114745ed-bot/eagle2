<template>
    <div class="min-h-screen bg-gray-900">
        <!-- Header -->
        <header class="bg-gray-800 shadow-lg border-b border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-white">
                        ⚙️ Admin Panel - Dynamic Theme
                    </h1>
                    <div class="flex space-x-4">
                        <button
                            @click="showImportModal = true"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                        >
                            📥 Import Excel
                        </button>
                        <button
                            @click="exportAll"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                        >
                            📤 Export All
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Tabs -->
        <div class="bg-gray-800 border-b border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="flex space-x-8">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        @click="activeTab = tab.key"
                        :class="[
                            'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                            activeTab === tab.key
                                ? 'border-blue-500 text-blue-400'
                                : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-600'
                        ]"
                    >
                        {{ tab.icon }} {{ tab.label }}
                    </button>
                </nav>
            </div>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Widgets Tab -->
            <WidgetsManager
                v-if="activeTab === 'widgets'"
                :widgets="widgets"
                @create="createWidget"
                @update="updateWidget"
                @delete="deleteWidget"
                @refresh="loadWidgets"
            />

            <!-- Themes Tab -->
            <ThemesManager
                v-if="activeTab === 'themes'"
                :widgets="widgets"
                :themes="themes"
                @create="createTheme"
                @update="updateTheme"
                @delete="deleteTheme"
                @refresh="loadThemes"
            />

            <!-- Screens Tab -->
            <ScreensManager
                v-if="activeTab === 'screens'"
                :screens="screens"
                :widgets="widgets"
                :themes="themes"
                @create="createScreen"
                @update="updateScreen"
                @delete="deleteScreen"
                @refresh="loadScreens"
            />
        </main>

        <!-- Import Modal -->
        <ImportModal
            :show="showImportModal"
            @close="showImportModal = false"
            @imported="onImported"
        />

        <!-- Notification -->
        <NotificationToast
            :show="!!notification"
            :title="notification?.message || ''"
            :type="notification?.type || 'info'"
            @close="notification = null"
        />
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import WidgetsManager from './WidgetsManager.vue';
import ThemesManager from './ThemesManager.vue';
import ScreensManager from './ScreensManager.vue';
import ImportModal from './ImportModal.vue';
import NotificationToast from '../components/NotificationToast.vue';
import { adminApi } from '../services/adminApi';

export default {
    name: 'AdminApp',
    components: {
        WidgetsManager,
        ThemesManager,
        ScreensManager,
        ImportModal,
        NotificationToast,
    },
    setup() {
        const activeTab = ref('widgets');
        const tabs = [
            { key: 'widgets', label: 'Widgets', icon: '📦' },
            { key: 'themes', label: 'Themes', icon: '🎨' },
            { key: 'screens', label: 'Screens', icon: '📱' },
        ];

        const widgets = ref([]);
        const themes = ref([]);
        const screens = ref([]);
        const showImportModal = ref(false);
        const notification = ref(null);

        onMounted(async () => {
            await Promise.all([loadWidgets(), loadThemes(), loadScreens()]);
        });

        const loadWidgets = async () => {
            try {
                const response = await adminApi.widgets.getAll();
                widgets.value = response.data.data || response.data;
            } catch (error) {
                showNotification('Failed to load widgets', 'error');
            }
        };

        const loadThemes = async () => {
            try {
                const response = await adminApi.themes.getAll();
                themes.value = response.data.data || response.data;
            } catch (error) {
                showNotification('Failed to load themes', 'error');
            }
        };

        const loadScreens = async () => {
            try {
                const response = await adminApi.screens.getAll();
                screens.value = response.data.data || response.data;
            } catch (error) {
                showNotification('Failed to load screens', 'error');
            }
        };

        const createWidget = async (widgetData) => {
            try {
                await adminApi.widgets.create(widgetData);
                await loadWidgets();
                showNotification('Widget created successfully', 'success');
            } catch (error) {
                showNotification('Failed to create widget', 'error');
            }
        };

        const updateWidget = async (id, widgetData) => {
            try {
                await adminApi.widgets.update(id, widgetData);
                await loadWidgets();
                showNotification('Widget updated successfully', 'success');
            } catch (error) {
                showNotification('Failed to update widget', 'error');
            }
        };

        const deleteWidget = async (id) => {
            try {
                await adminApi.widgets.delete(id);
                await loadWidgets();
                showNotification('Widget deleted successfully', 'success');
            } catch (error) {
                showNotification('Failed to delete widget', 'error');
            }
        };

        const createTheme = async (themeData) => {
            try {
                await adminApi.themes.create(themeData);
                await loadThemes();
                showNotification('Theme created successfully', 'success');
            } catch (error) {
                showNotification('Failed to create theme', 'error');
            }
        };

        const updateTheme = async (id, themeData) => {
            try {
                await adminApi.themes.update(id, themeData);
                await loadThemes();
                showNotification('Theme updated successfully', 'success');
            } catch (error) {
                showNotification('Failed to update theme', 'error');
            }
        };

        const deleteTheme = async (id) => {
            try {
                await adminApi.themes.delete(id);
                await loadThemes();
                showNotification('Theme deleted successfully', 'success');
            } catch (error) {
                showNotification('Failed to delete theme', 'error');
            }
        };

        const createScreen = async (screenData) => {
            try {
                await adminApi.screens.create(screenData);
                await loadScreens();
                showNotification('Screen created successfully', 'success');
            } catch (error) {
                showNotification('Failed to create screen', 'error');
            }
        };

        const updateScreen = async (id, screenData) => {
            try {
                await adminApi.screens.update(id, screenData);
                await loadScreens();
                showNotification('Screen updated successfully', 'success');
            } catch (error) {
                showNotification('Failed to update screen', 'error');
            }
        };

        const deleteScreen = async (id) => {
            try {
                await adminApi.screens.delete(id);
                await loadScreens();
                showNotification('Screen deleted successfully', 'success');
            } catch (error) {
                showNotification('Failed to delete screen', 'error');
            }
        };

        const exportAll = () => {
            window.open('/api/admin/export/all', '_blank');
        };

        const onImported = async () => {
            showImportModal.value = false;
            await Promise.all([loadWidgets(), loadThemes(), loadScreens()]);
            showNotification('Import completed successfully', 'success');
        };

        const showNotification = (message, type = 'info') => {
            notification.value = { message, type };
            setTimeout(() => {
                notification.value = null;
            }, 3000);
        };

        return {
            activeTab,
            tabs,
            widgets,
            themes,
            screens,
            showImportModal,
            notification,
            loadWidgets,
            loadThemes,
            loadScreens,
            createWidget,
            updateWidget,
            deleteWidget,
            createTheme,
            updateTheme,
            deleteTheme,
            createScreen,
            updateScreen,
            deleteScreen,
            exportAll,
            onImported,
        };
    },
};
</script>
