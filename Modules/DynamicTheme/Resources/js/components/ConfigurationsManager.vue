<template>
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900">
                    📁 Saved Configurations
                </h2>
                <button
                    @click="openCreateModal"
                    class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700"
                >
                    ➕ New Configuration
                </button>
            </div>
        </div>

        <div class="p-4">
            <!-- Loading State -->
            <div v-if="loading" class="text-center py-8">
                <div class="animate-spin inline-block w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
                <p class="mt-2 text-gray-500">Loading configurations...</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="configurations.length === 0" class="text-center py-8">
                <div class="text-4xl mb-2">📭</div>
                <p class="text-gray-500">No configurations yet</p>
                <p class="text-sm text-gray-400">Create your first configuration to get started</p>
            </div>

            <!-- Configurations List -->
            <div v-else class="space-y-3">
                <div
                    v-for="config in configurations"
                    :key="config.id"
                    :class="[
                        'border rounded-lg p-4 transition-all',
                        config.is_active
                            ? 'border-green-500 bg-green-50'
                            : 'border-gray-200 hover:border-blue-300'
                    ]"
                >
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <span v-if="config.is_active" class="text-green-600">⭐</span>
                                <h3 class="font-semibold text-gray-900">{{ config.name }}</h3>
                                <span
                                    v-if="config.is_active"
                                    class="px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded-full"
                                >
                                    Active
                                </span>
                            </div>
                            <p v-if="config.description" class="text-sm text-gray-500 mt-1">
                                {{ config.description }}
                            </p>
                            <div class="flex space-x-4 mt-2 text-xs text-gray-400">
                                <span>📱 {{ config.screen_overrides_count || 0 }} screens</span>
                                <span>🧩 {{ config.widget_overrides_count || 0 }} widgets</span>
                                <span>🎨 {{ config.asset_overrides_count || 0 }} assets</span>
                            </div>
                        </div>

                        <div class="flex space-x-2 ml-4">
                            <button
                                v-if="!config.is_active"
                                @click="activateConfig(config)"
                                class="px-3 py-1.5 text-sm bg-green-600 text-white rounded hover:bg-green-700"
                                title="Activate this configuration"
                            >
                                ✓ Activate
                            </button>
                            <button
                                @click="editConfig(config)"
                                class="px-3 py-1.5 text-sm text-blue-600 hover:bg-blue-50 rounded"
                                title="Edit configuration"
                            >
                                ✏️ Edit
                            </button>
                            <button
                                @click="cloneConfig(config)"
                                class="px-3 py-1.5 text-sm text-purple-600 hover:bg-purple-50 rounded"
                                title="Clone configuration"
                            >
                                📋 Clone
                            </button>
                            <button
                                v-if="!config.is_active"
                                @click="deleteConfig(config)"
                                class="px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded"
                                title="Delete configuration"
                            >
                                🗑️
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Clone Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
                <div class="p-4 border-b">
                    <h3 class="text-lg font-semibold">
                        {{ modalMode === 'create' ? '➕ New Configuration' : '📋 Clone Configuration' }}
                    </h3>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                        <input
                            v-model="formData.name"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :placeholder="modalMode === 'clone' ? 'Enter new name for the copy' : 'e.g., Christmas 2025'"
                        />
                    </div>
                    <div v-if="modalMode === 'create'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea
                            v-model="formData.description"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Optional description"
                        ></textarea>
                    </div>
                </div>
                <div class="p-4 border-t flex justify-end space-x-3">
                    <button
                        @click="closeModal"
                        class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitModal"
                        :disabled="!formData.name || submitting"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ submitting ? 'Saving...' : (modalMode === 'create' ? 'Create' : 'Clone') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
                <div class="p-4 border-b">
                    <h3 class="text-lg font-semibold text-red-600">🗑️ Delete Configuration</h3>
                </div>
                <div class="p-4">
                    <p class="text-gray-600">
                        Are you sure you want to delete "<strong>{{ configToDelete?.name }}</strong>"?
                    </p>
                    <p class="text-sm text-gray-500 mt-2">
                        This will permanently delete all screen, widget, and asset overrides.
                    </p>
                </div>
                <div class="p-4 border-t flex justify-end space-x-3">
                    <button
                        @click="showDeleteModal = false; configToDelete = null"
                        class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg"
                    >
                        Cancel
                    </button>
                    <button
                        @click="confirmDelete"
                        :disabled="submitting"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
                    >
                        {{ submitting ? 'Deleting...' : 'Delete' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { configurationsApi } from '../services/api';

export default {
    name: 'ConfigurationsManager',
    emits: ['edit', 'notify'],

    setup(props, { emit }) {
        const configurations = ref([]);
        const loading = ref(true);
        const submitting = ref(false);

        // Modal state
        const showModal = ref(false);
        const modalMode = ref('create'); // 'create' or 'clone'
        const formData = ref({ name: '', description: '' });
        const configToClone = ref(null);

        // Delete modal state
        const showDeleteModal = ref(false);
        const configToDelete = ref(null);

        const loadConfigurations = async () => {
            try {
                loading.value = true;
                const response = await configurationsApi.list();
                configurations.value = response.data.data || [];
            } catch (error) {
                emit('notify', { type: 'error', message: 'Failed to load configurations' });
            } finally {
                loading.value = false;
            }
        };

        const openCreateModal = () => {
            modalMode.value = 'create';
            formData.value = { name: '', description: '' };
            configToClone.value = null;
            showModal.value = true;
        };

        const cloneConfig = (config) => {
            modalMode.value = 'clone';
            formData.value = { name: config.name + ' (Copy)', description: '' };
            configToClone.value = config;
            showModal.value = true;
        };

        const closeModal = () => {
            showModal.value = false;
            formData.value = { name: '', description: '' };
            configToClone.value = null;
        };

        const submitModal = async () => {
            if (!formData.value.name) return;

            try {
                submitting.value = true;

                if (modalMode.value === 'create') {
                    await configurationsApi.create(formData.value);
                    emit('notify', { type: 'success', message: 'Configuration created successfully' });
                } else {
                    await configurationsApi.clone(configToClone.value.id, formData.value.name);
                    emit('notify', { type: 'success', message: 'Configuration cloned successfully' });
                }

                closeModal();
                await loadConfigurations();
            } catch (error) {
                const message = error.response?.data?.message || 'Operation failed';
                emit('notify', { type: 'error', message });
            } finally {
                submitting.value = false;
            }
        };

        const editConfig = (config) => {
            emit('edit', config);
        };

        const activateConfig = async (config) => {
            try {
                await configurationsApi.activate(config.id);
                emit('notify', { type: 'success', message: `"${config.name}" is now active` });
                await loadConfigurations();
            } catch (error) {
                emit('notify', { type: 'error', message: 'Failed to activate configuration' });
            }
        };

        const deleteConfig = (config) => {
            configToDelete.value = config;
            showDeleteModal.value = true;
        };

        const confirmDelete = async () => {
            if (!configToDelete.value) return;

            try {
                submitting.value = true;
                await configurationsApi.delete(configToDelete.value.id);
                emit('notify', { type: 'success', message: 'Configuration deleted' });
                showDeleteModal.value = false;
                configToDelete.value = null;
                await loadConfigurations();
            } catch (error) {
                const message = error.response?.data?.message || 'Failed to delete configuration';
                emit('notify', { type: 'error', message });
            } finally {
                submitting.value = false;
            }
        };

        onMounted(loadConfigurations);

        return {
            configurations,
            loading,
            submitting,
            showModal,
            modalMode,
            formData,
            showDeleteModal,
            configToDelete,
            openCreateModal,
            cloneConfig,
            closeModal,
            submitModal,
            editConfig,
            activateConfig,
            deleteConfig,
            confirmDelete,
            loadConfigurations,
        };
    },
};
</script>
