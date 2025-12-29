<template>
    <div class="space-y-6">
        <!-- Configuration Selection -->
        <div class="bg-white rounded-lg p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">⚖️ Compare Configurations</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <!-- Config 1 Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Configuration 1</label>
                    <select
                        v-model="config1Id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                        <option :value="null">Select configuration...</option>
                        <option
                            v-for="config in configurations"
                            :key="config.id"
                            :value="config.id"
                        >
                            {{ config.name }}
                        </option>
                    </select>
                </div>

                <!-- Config 2 Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Configuration 2</label>
                    <select
                        v-model="config2Id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                        <option :value="null">Select configuration...</option>
                        <option
                            v-for="config in configurations"
                            :key="config.id"
                            :value="config.id"
                        >
                            {{ config.name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!config1 || !config2" class="text-center py-12">
                <p class="text-gray-500">Select two configurations to compare</p>
            </div>

            <!-- Comparison View -->
            <template v-else>
                <!-- General Info -->
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <h4 class="font-semibold text-gray-900 mb-4">📋 General Information</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="col-span-1">
                            <p class="text-sm font-medium text-gray-600">Property</p>
                        </div>
                        <div class="col-span-1">
                            <p class="text-sm font-medium text-gray-600">{{ config1.name }}</p>
                        </div>
                        <div class="col-span-1">
                            <p class="text-sm font-medium text-gray-600">{{ config2.name }}</p>
                        </div>

                        <!-- Status -->
                        <div class="col-span-1 text-sm font-medium text-gray-700">Status</div>
                        <div class="col-span-1 text-sm">
                            <span v-if="config1.is_active" class="px-2 py-1 bg-green-100 text-green-700 rounded">
                                ✅ Active
                            </span>
                            <span v-else class="px-2 py-1 bg-gray-100 text-gray-700 rounded">
                                Inactive
                            </span>
                        </div>
                        <div class="col-span-1 text-sm">
                            <span v-if="config2.is_active" class="px-2 py-1 bg-green-100 text-green-700 rounded">
                                ✅ Active
                            </span>
                            <span v-else class="px-2 py-1 bg-gray-100 text-gray-700 rounded">
                                Inactive
                            </span>
                        </div>

                        <!-- Created Date -->
                        <div class="col-span-1 text-sm font-medium text-gray-700">Created</div>
                        <div class="col-span-1 text-sm text-gray-600">{{ formatDate(config1.created_at) }}</div>
                        <div class="col-span-1 text-sm text-gray-600">{{ formatDate(config2.created_at) }}</div>

                        <!-- Updated Date -->
                        <div class="col-span-1 text-sm font-medium text-gray-700">Last Updated</div>
                        <div class="col-span-1 text-sm text-gray-600">{{ formatDate(config1.updated_at) }}</div>
                        <div class="col-span-1 text-sm text-gray-600">{{ formatDate(config2.updated_at) }}</div>
                    </div>
                </div>

                <!-- Statistics Comparison -->
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <h4 class="font-semibold text-gray-900 mb-4">📊 Statistics</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Metric Name -->
                        <div class="col-span-1">
                            <p class="text-sm font-medium text-gray-600">Metric</p>
                        </div>
                        <div class="col-span-1">
                            <p class="text-sm font-medium text-gray-600">{{ config1.name }}</p>
                        </div>
                        <div class="col-span-1">
                            <p class="text-sm font-medium text-gray-600">{{ config2.name }}</p>
                        </div>

                        <!-- Screens -->
                        <div class="col-span-1 text-sm font-medium text-gray-700">
                            🖥️ Screens
                        </div>
                        <div class="col-span-1">
                            <ComparisonValue
                                :value1="config1.stats.total_screens"
                                :value2="config2.stats.total_screens"
                            />
                        </div>
                        <div class="col-span-1">
                            <ComparisonValue
                                :value1="config1.stats.total_screens"
                                :value2="config2.stats.total_screens"
                                flip
                            />
                        </div>

                        <!-- Widgets -->
                        <div class="col-span-1 text-sm font-medium text-gray-700">
                            🎨 Widgets
                        </div>
                        <div class="col-span-1">
                            <ComparisonValue
                                :value1="config1.stats.total_widgets"
                                :value2="config2.stats.total_widgets"
                            />
                        </div>
                        <div class="col-span-1">
                            <ComparisonValue
                                :value1="config1.stats.total_widgets"
                                :value2="config2.stats.total_widgets"
                                flip
                            />
                        </div>

                        <!-- Children -->
                        <div class="col-span-1 text-sm font-medium text-gray-700">
                            👶 Children
                        </div>
                        <div class="col-span-1">
                            <ComparisonValue
                                :value1="config1.stats.total_children"
                                :value2="config2.stats.total_children"
                            />
                        </div>
                        <div class="col-span-1">
                            <ComparisonValue
                                :value1="config1.stats.total_children"
                                :value2="config2.stats.total_children"
                                flip
                            />
                        </div>

                        <!-- Total Assets -->
                        <div class="col-span-1 text-sm font-medium text-gray-700">
                            📦 Assets
                        </div>
                        <div class="col-span-1">
                            <ComparisonValue
                                :value1="config1.stats.total_asset_overrides + config1.stats.total_child_asset_overrides"
                                :value2="config2.stats.total_asset_overrides + config2.stats.total_child_asset_overrides"
                            />
                        </div>
                        <div class="col-span-1">
                            <ComparisonValue
                                :value1="config1.stats.total_asset_overrides + config1.stats.total_child_asset_overrides"
                                :value2="config2.stats.total_asset_overrides + config2.stats.total_child_asset_overrides"
                                flip
                            />
                        </div>
                    </div>
                </div>

                <!-- Visibility Comparison -->
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <h4 class="font-semibold text-gray-900 mb-4">👁️ Visibility Status</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="col-span-1">
                            <p class="text-sm font-medium text-gray-600">Item Type</p>
                        </div>
                        <div class="col-span-1">
                            <p class="text-sm font-medium text-gray-600">{{ config1.name }}</p>
                        </div>
                        <div class="col-span-1">
                            <p class="text-sm font-medium text-gray-600">{{ config2.name }}</p>
                        </div>

                        <!-- Visible Screens -->
                        <div class="col-span-1 text-sm font-medium text-gray-700">Visible Screens</div>
                        <div class="col-span-1 text-sm">
                            {{ config1.stats.visible_screens }}/{{ config1.stats.total_screens }}
                        </div>
                        <div class="col-span-1 text-sm">
                            {{ config2.stats.visible_screens }}/{{ config2.stats.total_screens }}
                        </div>

                        <!-- Visible Widgets -->
                        <div class="col-span-1 text-sm font-medium text-gray-700">Visible Widgets</div>
                        <div class="col-span-1 text-sm">
                            {{ config1.stats.visible_widgets }}/{{ config1.stats.total_widgets }}
                        </div>
                        <div class="col-span-1 text-sm">
                            {{ config2.stats.visible_widgets }}/{{ config2.stats.total_widgets }}
                        </div>

                        <!-- Visible Children -->
                        <div class="col-span-1 text-sm font-medium text-gray-700">Visible Children</div>
                        <div class="col-span-1 text-sm">
                            {{ config1.stats.visible_children }}/{{ config1.stats.total_children }}
                        </div>
                        <div class="col-span-1 text-sm">
                            {{ config2.stats.visible_children }}/{{ config2.stats.total_children }}
                        </div>
                    </div>
                </div>

                <!-- Difference Summary -->
                <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                    <h4 class="font-semibold text-gray-900 mb-4">📈 Differences</h4>
                    
                    <div class="space-y-2 text-sm">
                        <div v-if="config1.stats.total_screens !== config2.stats.total_screens" class="flex justify-between">
                            <span class="text-gray-700">Screens difference:</span>
                            <span :class="getDifferenceColor(config1.stats.total_screens - config2.stats.total_screens)">
                                {{ config1.stats.total_screens - config2.stats.total_screens > 0 ? '+' : '' }}{{ config1.stats.total_screens - config2.stats.total_screens }}
                            </span>
                        </div>
                        <div v-if="config1.stats.total_widgets !== config2.stats.total_widgets" class="flex justify-between">
                            <span class="text-gray-700">Widgets difference:</span>
                            <span :class="getDifferenceColor(config1.stats.total_widgets - config2.stats.total_widgets)">
                                {{ config1.stats.total_widgets - config2.stats.total_widgets > 0 ? '+' : '' }}{{ config1.stats.total_widgets - config2.stats.total_widgets }}
                            </span>
                        </div>
                        <div v-if="config1.stats.total_children !== config2.stats.total_children" class="flex justify-between">
                            <span class="text-gray-700">Children difference:</span>
                            <span :class="getDifferenceColor(config1.stats.total_children - config2.stats.total_children)">
                                {{ config1.stats.total_children - config2.stats.total_children > 0 ? '+' : '' }}{{ config1.stats.total_children - config2.stats.total_children }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-700">Assets difference:</span>
                            <span :class="getDifferenceColor((config1.stats.total_asset_overrides + config1.stats.total_child_asset_overrides) - (config2.stats.total_asset_overrides + config2.stats.total_child_asset_overrides))">
                                {{ (config1.stats.total_asset_overrides + config1.stats.total_child_asset_overrides) - (config2.stats.total_asset_overrides + config2.stats.total_child_asset_overrides) > 0 ? '+' : '' }}{{ (config1.stats.total_asset_overrides + config1.stats.total_child_asset_overrides) - (config2.stats.total_asset_overrides + config2.stats.total_child_asset_overrides) }}
                            </span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
import { ref, computed } from 'vue';
import ComparisonValue from './ComparisonValue.vue';

export default {
    name: 'ConfigurationComparison',
    components: {
        ComparisonValue,
    },
    props: {
        configurations: {
            type: Array,
            default: () => []
        }
    },
    emits: ['select-for-comparison'],
    setup(props) {
        const config1Id = ref(null);
        const config2Id = ref(null);

        const config1 = computed(() =>
            props.configurations.find(c => c.id === config1Id.value)
        );

        const config2 = computed(() =>
            props.configurations.find(c => c.id === config2Id.value)
        );

        const formatDate = (date) => {
            if (!date) return 'N/A';
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            });
        };

        const getDifferenceColor = (difference) => {
            if (difference === 0) return 'text-gray-600';
            return difference > 0 ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold';
        };

        return {
            config1Id,
            config2Id,
            config1,
            config2,
            formatDate,
            getDifferenceColor,
        };
    }
};
</script>
