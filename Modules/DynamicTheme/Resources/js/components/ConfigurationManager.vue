<template>
    <div class="space-y-6">
        <!-- Configurations Overview -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-900">📊 Configuration Management</h2>
                <button
                    @click="$emit('create-config')"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                >
                    ➕ New Configuration
                </button>
            </div>

            <!-- Tabs for different views -->
            <div class="border-b border-gray-200 mb-6">
                <div class="flex space-x-8">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            'py-2 px-2 font-medium transition border-b-2',
                            activeTab === tab.id
                                ? 'border-blue-600 text-blue-600'
                                : 'border-transparent text-gray-600 hover:text-gray-900'
                        ]"
                    >
                        {{ tab.label }}
                    </button>
                </div>
            </div>

            <!-- Tab Content -->
            <div v-if="activeTab === 'overview'" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="config in configurations"
                        :key="config.id"
                        @click="$emit('select-config', config.id)"
                        :class="[
                            'p-4 rounded-lg border-2 cursor-pointer transition',
                            selectedConfigId === config.id
                                ? 'border-blue-600 bg-blue-50'
                                : 'border-gray-200 bg-gray-50 hover:border-gray-300'
                        ]"
                    >
                        <!-- Configuration Header -->
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">{{ config.name }}</h3>
                                <p class="text-sm text-gray-600 mt-1">{{ config.description }}</p>
                            </div>
                            <span v-if="config.is_active" class="px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded">
                                ✅ Active
                            </span>
                        </div>

                        <!-- Statistics -->
                        <div class="grid grid-cols-2 gap-2 mb-4 text-sm">
                            <div class="bg-white rounded p-2 border border-gray-200">
                                <p class="text-gray-600">Screens</p>
                                <p class="text-lg font-bold text-blue-600">{{ config.stats?.total_screens || 0 }}</p>
                            </div>
                            <div class="bg-white rounded p-2 border border-gray-200">
                                <p class="text-gray-600">Widgets</p>
                                <p class="text-lg font-bold text-purple-600">{{ config.stats?.total_widgets || 0 }}</p>
                            </div>
                            <div class="bg-white rounded p-2 border border-gray-200">
                                <p class="text-gray-600">Children</p>
                                <p class="text-lg font-bold text-indigo-600">{{ config.stats?.total_children || 0 }}</p>
                            </div>
                            <div class="bg-white rounded p-2 border border-gray-200">
                                <p class="text-gray-600">Assets</p>
                                <p class="text-lg font-bold text-orange-600">
                                    {{ (config.stats?.total_asset_overrides || 0) + (config.stats?.total_child_asset_overrides || 0) }}
                                </p>
                            </div>
                        </div>

                        <!-- Visibility Status -->
                        <div class="grid grid-cols-2 gap-2 mb-4 text-xs">
                            <div class="text-gray-600">
                                👁️ {{ config.stats?.visible_screens || 0 }}/{{ config.stats?.total_screens || 0 }} screens
                            </div>
                            <div class="text-gray-600">
                                👁️ {{ config.stats?.visible_widgets || 0 }}/{{ config.stats?.total_widgets || 0 }} widgets
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <button
                                @click.stop="$emit('edit-config', config.id)"
                                class="flex-1 px-2 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600"
                            >
                                ✏️ Edit
                            </button>
                            <button
                                @click.stop="$emit('clone-config', config.id)"
                                class="flex-1 px-2 py-1 bg-yellow-500 text-white text-sm rounded hover:bg-yellow-600"
                            >
                                📋 Clone
                            </button>
                            <button
                                v-if="!config.is_active"
                                @click.stop="$emit('activate-config', config.id)"
                                class="flex-1 px-2 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600"
                            >
                                ⭐ Activate
                            </button>
                        </div>

                        <!-- Last Modified -->
                        <p class="text-xs text-gray-500 mt-3">
                            Updated: {{ formatDate(config.updated_at) }}
                        </p>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="configurations.length === 0" class="text-center py-12">
                    <p class="text-gray-500 mb-4">No configurations yet</p>
                    <button
                        @click="$emit('create-config')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        Create First Configuration
                    </button>
                </div>
            </div>

            <!-- Detailed View -->
            <div v-if="activeTab === 'details' && selectedConfig" class="space-y-6">
                <ConfigurationDetails
                    :config="selectedConfig"
                    :stats="configStats"
                    @clone="$emit('clone-config', selectedConfigId)"
                    @edit="$emit('edit-config', selectedConfigId)"
                    @activate="$emit('activate-config', selectedConfigId)"
                    @delete="$emit('delete-config', selectedConfigId)"
                />
            </div>

            <!-- Comparison View -->
            <div v-if="activeTab === 'compare'" class="space-y-6">
                <ConfigurationComparison
                    :configurations="configurations"
                    @select-for-comparison="selectForComparison"
                />
            </div>

            <!-- Activity View -->
            <div v-if="activeTab === 'activity'" class="space-y-6">
                <ConfigurationActivity
                    :config="selectedConfig"
                    :activities="configActivities"
                />
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, watch } from 'vue';
import ConfigurationDetails from './ConfigurationDetails.vue';
import ConfigurationComparison from './ConfigurationComparison.vue';
import ConfigurationActivity from './ConfigurationActivity.vue';

export default {
    name: 'ConfigurationManager',
    components: {
        ConfigurationDetails,
        ConfigurationComparison,
        ConfigurationActivity,
    },
    props: {
        configurations: {
            type: Array,
            default: () => []
        },
        selectedConfigId: {
            type: [Number, String],
            default: null
        }
    },
    emits: [
        'select-config',
        'create-config',
        'edit-config',
        'clone-config',
        'activate-config',
        'delete-config',
    ],
    setup(props) {
        const activeTab = ref('overview');
        const tabs = [
            { id: 'overview', label: '📋 Overview' },
            { id: 'details', label: '📊 Details' },
            { id: 'compare', label: '⚖️ Compare' },
            { id: 'activity', label: '📝 Activity' },
        ];

        const selectedConfig = computed(() =>
            props.configurations.find(c => c.id === props.selectedConfigId)
        );

        const configStats = computed(() => selectedConfig.value?.stats || {});
        const configActivities = computed(() => selectedConfig.value?.activities || []);

        const formatDate = (date) => {
            if (!date) return 'N/A';
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            });
        };

        const selectForComparison = (configId) => {
            // Emit event to parent for comparison selection
        };

        return {
            activeTab,
            tabs,
            selectedConfig,
            configStats,
            configActivities,
            formatDate,
            selectForComparison,
        };
    }
};
</script>
