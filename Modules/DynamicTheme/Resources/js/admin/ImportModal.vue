<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-[600px]">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-white">📥 Import Data</h3>
                <button @click="$emit('close')" class="text-gray-400 hover:text-white">✕</button>
            </div>

            <!-- Import Type Selection -->
            <div class="mb-6">
                <label class="block text-sm text-gray-400 mb-2">What do you want to import?</label>
                <div class="grid grid-cols-2 gap-3">
                    <button
                        @click="importType = 'widgets'"
                        :class="[
                            'p-4 rounded-lg border-2 text-left transition-all',
                            importType === 'widgets'
                                ? 'border-blue-500 bg-blue-900/30'
                                : 'border-gray-600 bg-gray-700 hover:border-gray-500'
                        ]"
                    >
                        <div class="text-2xl mb-2">🧩</div>
                        <div class="text-white font-medium">Widgets</div>
                        <div class="text-xs text-gray-400">Import widget definitions, settings, and actions</div>
                    </button>
                    <button
                        @click="importType = 'themes'"
                        :class="[
                            'p-4 rounded-lg border-2 text-left transition-all',
                            importType === 'themes'
                                ? 'border-purple-500 bg-purple-900/30'
                                : 'border-gray-600 bg-gray-700 hover:border-gray-500'
                        ]"
                    >
                        <div class="text-2xl mb-2">🎨</div>
                        <div class="text-white font-medium">Themes</div>
                        <div class="text-xs text-gray-400">Import themes and assets for widgets</div>
                    </button>
                </div>
            </div>

            <!-- File Upload -->
            <div class="mb-6">
                <label class="block text-sm text-gray-400 mb-2">Upload Excel File (.xlsx)</label>
                <div
                    class="border-2 border-dashed border-gray-600 rounded-lg p-8 text-center cursor-pointer hover:border-gray-500 transition-colors"
                    @click="triggerFileInput"
                    @dragover.prevent
                    @drop.prevent="handleDrop"
                >
                    <input
                        ref="fileInput"
                        type="file"
                        accept=".xlsx,.xls"
                        class="hidden"
                        @change="handleFileChange"
                    />
                    <div v-if="!selectedFile">
                        <div class="text-4xl mb-3">📄</div>
                        <p class="text-gray-400">Click to select or drag & drop your Excel file</p>
                        <p class="text-xs text-gray-500 mt-2">Supported: .xlsx, .xls</p>
                    </div>
                    <div v-else class="flex items-center justify-center space-x-3">
                        <div class="text-3xl">📊</div>
                        <div>
                            <p class="text-white font-medium">{{ selectedFile.name }}</p>
                            <p class="text-xs text-gray-400">{{ formatFileSize(selectedFile.size) }}</p>
                        </div>
                        <button
                            @click.stop="clearFile"
                            class="text-red-400 hover:text-red-300"
                        >
                            ✕
                        </button>
                    </div>
                </div>
            </div>

            <!-- Download Template -->
            <div class="mb-6 p-4 bg-gray-700 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white font-medium">Need a template?</p>
                        <p class="text-xs text-gray-400">Download a pre-formatted Excel template</p>
                    </div>
                    <button
                        @click="downloadTemplate"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-500 flex items-center"
                    >
                        <span class="mr-2">📥</span> Download Template
                    </button>
                </div>
            </div>

            <!-- Import Options -->
            <div class="mb-6">
                <label class="flex items-center text-gray-300">
                    <input
                        v-model="replaceExisting"
                        type="checkbox"
                        class="mr-2 rounded bg-gray-600 border-gray-500"
                    />
                    Replace existing data (by key)
                </label>
                <p class="text-xs text-gray-500 mt-1 ml-6">
                    If checked, existing items with matching keys will be updated.
                    Otherwise, duplicates will be skipped.
                </p>
            </div>

            <!-- Status -->
            <div v-if="importStatus" class="mb-6 p-4 rounded-lg" :class="statusClass">
                <div class="flex items-center space-x-3">
                    <span class="text-2xl">{{ statusIcon }}</span>
                    <div>
                        <p class="font-medium">{{ importStatus.message }}</p>
                        <p v-if="importStatus.details" class="text-sm opacity-80">{{ importStatus.details }}</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3">
                <button
                    @click="$emit('close')"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-500"
                >
                    Cancel
                </button>
                <button
                    @click="startImport"
                    :disabled="!canImport || importing"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                >
                    <span v-if="importing" class="mr-2 animate-spin">⏳</span>
                    <span v-else class="mr-2">📥</span>
                    {{ importing ? 'Importing...' : 'Import' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed } from 'vue';
import { adminApi } from '../services/adminApi';

export default {
    name: 'ImportModal',
    props: {
        show: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['close', 'imported'],
    setup(props, { emit }) {
        const fileInput = ref(null);
        const selectedFile = ref(null);
        const importType = ref('widgets');
        const replaceExisting = ref(false);
        const importing = ref(false);
        const importStatus = ref(null);

        const canImport = computed(() => {
            return selectedFile.value && importType.value;
        });

        const statusClass = computed(() => {
            if (!importStatus.value) return '';
            return {
                success: 'bg-green-900/50 text-green-300',
                error: 'bg-red-900/50 text-red-300',
                info: 'bg-blue-900/50 text-blue-300',
            }[importStatus.value.type] || 'bg-gray-700 text-gray-300';
        });

        const statusIcon = computed(() => {
            if (!importStatus.value) return '';
            return {
                success: '✅',
                error: '❌',
                info: 'ℹ️',
            }[importStatus.value.type] || '📋';
        });

        const triggerFileInput = () => {
            fileInput.value?.click();
        };

        const handleFileChange = (event) => {
            const file = event.target.files?.[0];
            if (file) {
                selectedFile.value = file;
                importStatus.value = null;
            }
        };

        const handleDrop = (event) => {
            const file = event.dataTransfer.files?.[0];
            if (file && (file.name.endsWith('.xlsx') || file.name.endsWith('.xls'))) {
                selectedFile.value = file;
                importStatus.value = null;
            }
        };

        const clearFile = () => {
            selectedFile.value = null;
            importStatus.value = null;
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        };

        const formatFileSize = (bytes) => {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        };

        const downloadTemplate = async () => {
            try {
                const response = await adminApi.import.downloadTemplate(importType.value);
                const blob = new Blob([response.data], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                });
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = `${importType.value}_template.xlsx`;
                link.click();
                window.URL.revokeObjectURL(url);
            } catch (error) {
                importStatus.value = {
                    type: 'error',
                    message: 'Failed to download template',
                    details: error.message,
                };
            }
        };

        const startImport = async () => {
            if (!canImport.value || importing.value) return;

            importing.value = true;
            importStatus.value = {
                type: 'info',
                message: 'Importing data...',
                details: 'Please wait while we process your file.',
            };

            try {
                const formData = new FormData();
                formData.append('file', selectedFile.value);
                formData.append('type', importType.value);
                formData.append('replace_existing', replaceExisting.value ? '1' : '0');

                const response = await adminApi.import.upload(formData);
                const result = response.data;

                importStatus.value = {
                    type: 'success',
                    message: 'Import completed successfully!',
                    details: `Created: ${result.created || 0}, Updated: ${result.updated || 0}, Skipped: ${result.skipped || 0}`,
                };

                emit('imported', result);
            } catch (error) {
                importStatus.value = {
                    type: 'error',
                    message: 'Import failed',
                    details: error.response?.data?.message || error.message,
                };
            } finally {
                importing.value = false;
            }
        };

        return {
            fileInput,
            selectedFile,
            importType,
            replaceExisting,
            importing,
            importStatus,
            canImport,
            statusClass,
            statusIcon,
            triggerFileInput,
            handleFileChange,
            handleDrop,
            clearFile,
            formatFileSize,
            downloadTemplate,
            startImport,
        };
    },
};
</script>
