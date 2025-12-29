<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <h1 class="text-2xl font-bold text-gray-900">
                            🎨 Theme Configurations
                        </h1>
                        <span v-if="activeConfig" class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">
                            Active: {{ activeConfig.name }}
                        </span>
                    </div>
                    <div class="flex space-x-4">
                        <button
                            v-if="currentView === 'editor'"
                            @click="goBack"
                            class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg"
                        >
                            ← Back to List
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Configurations List View -->
            <div v-if="currentView === 'list'">
                <ConfigurationsManager
                    ref="configManager"
                    @edit="openEditor"
                    @notify="showNotification"
                />
            </div>

            <!-- Configuration Editor View -->
            <div v-else-if="currentView === 'editor'">
                <ConfigurationEditor
                    :configuration="editingConfig"
                    @save="onConfigSaved"
                    @notify="showNotification"
                    @back="goBack"
                />
            </div>
        </main>

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
import { ref, computed, onMounted } from 'vue';
import ConfigurationsManager from './ConfigurationsManager.vue';
import ConfigurationEditor from './ConfigurationEditor.vue';
import NotificationToast from './NotificationToast.vue';
import { configurationsApi } from '../services/api';

export default {
    name: 'ClientDashboard',
    components: {
        ConfigurationsManager,
        ConfigurationEditor,
        NotificationToast,
    },
    setup() {
        const currentView = ref('list');
        const editingConfig = ref(null);
        const activeConfig = ref(null);
        const notification = ref(null);
        const configManager = ref(null);

        const loadActiveConfig = async () => {
            try {
                const response = await configurationsApi.list();
                const configs = response.data.data || [];
                activeConfig.value = configs.find(c => c.is_active) || null;
            } catch (error) {
                // Silently fail
            }
        };

        const openEditor = async (config) => {
            try {
                const response = await configurationsApi.getFull(config.id);
                editingConfig.value = response.data.data || response.data;
                currentView.value = 'editor';
            } catch (error) {
                showNotification({ type: 'error', message: 'Failed to load configuration' });
            }
        };

        const goBack = () => {
            currentView.value = 'list';
            editingConfig.value = null;
            // Reload configurations list
            if (configManager.value) {
                configManager.value.loadConfigurations();
            }
            loadActiveConfig();
        };

        const onConfigSaved = () => {
            showNotification({ type: 'success', message: 'Configuration saved successfully' });
        };

        const showNotification = (data) => {
            notification.value = data;
            setTimeout(() => {
                notification.value = null;
            }, 3000);
        };

        onMounted(loadActiveConfig);

        return {
            currentView,
            editingConfig,
            activeConfig,
            notification,
            configManager,
            openEditor,
            goBack,
            onConfigSaved,
            showNotification,
        };
    },
};
</script>
