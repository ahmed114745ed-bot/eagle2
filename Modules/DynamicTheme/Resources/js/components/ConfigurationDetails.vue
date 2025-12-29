<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ config.name }}</h2>
                <p class="text-gray-600 mt-1">{{ config.description }}</p>
            </div>
            <div class="flex space-x-2">
                <button
                    v-if="!config.is_active"
                    @click="$emit('activate')"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                >
                    ⭐ Activate
                </button>
                <button
                    v-else
                    disabled
                    class="px-4 py-2 bg-green-100 text-green-700 rounded-lg opacity-50 cursor-not-allowed"
                >
                    ✅ Active
                </button>
                <button
                    @click="$emit('clone')"
                    class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700"
                >
                    📋 Clone
                </button>
                <button
                    @click="$emit('edit')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    ✏️ Edit
                </button>
            </div>
        </div>

        <!-- Statistics Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <StatCard
                icon="🖥️"
                label="Screens"
                :total="stats.total_screens"
                :visible="stats.visible_screens"
            />
            <StatCard
                icon="🎨"
                label="Widgets"
                :total="stats.total_widgets"
                :visible="stats.visible_widgets"
            />
            <StatCard
                icon="👶"
                label="Children"
                :total="stats.total_children"
                :visible="stats.visible_children"
            />
            <StatCard
                icon="📦"
                label="Assets"
                :total="stats.total_asset_overrides + stats.total_child_asset_overrides"
                :visible="stats.total_asset_overrides"
            />
        </div>

        <!-- Hierarchy Structure -->
        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Configuration Hierarchy</h3>
            
            <div class="space-y-4">
                <!-- Level 1: Screens -->
                <div class="bg-white rounded-lg p-4 border-l-4 border-blue-500">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-semibold text-gray-900">Level 1: Screens</h4>
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 text-sm rounded">
                            {{ stats.total_screens }} screens
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-3">
                        Configure which screens are visible and their display order
                    </p>
                    <div class="flex space-x-2">
                        <button
                            @click="expandLevel = expandLevel === 'screens' ? null : 'screens'"
                            class="px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600"
                        >
                            {{ expandLevel === 'screens' ? '▼' : '▶' }} View Details
                        </button>
                    </div>
                    
                    <div v-if="expandLevel === 'screens'" class="mt-4 space-y-2 border-t pt-4">
                        <div
                            v-for="screen in config.screen_overrides"
                            :key="screen.id"
                            class="flex justify-between items-center p-2 bg-gray-50 rounded"
                        >
                            <div>
                                <p class="font-medium text-gray-900">{{ screen.screen?.name }}</p>
                                <p class="text-xs text-gray-600">Order: {{ screen.display_order }}</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span v-if="screen.is_visible" class="text-green-600">👁️ Visible</span>
                                <span v-else class="text-gray-400">👁️ Hidden</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Level 2: Widgets -->
                <div class="bg-white rounded-lg p-4 border-l-4 border-purple-500">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-semibold text-gray-900">Level 2: Widgets</h4>
                        <span class="px-2 py-1 bg-purple-100 text-purple-700 text-sm rounded">
                            {{ stats.total_widgets }} widgets
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-3">
                        Widget visibility, theme selection, and display order
                    </p>
                    <div class="flex space-x-2">
                        <button
                            @click="expandLevel = expandLevel === 'widgets' ? null : 'widgets'"
                            class="px-3 py-1 text-sm bg-purple-500 text-white rounded hover:bg-purple-600"
                        >
                            {{ expandLevel === 'widgets' ? '▼' : '▶' }} View Details
                        </button>
                    </div>
                    
                    <div v-if="expandLevel === 'widgets'" class="mt-4 space-y-2 border-t pt-4">
                        <div
                            v-for="widget in config.widget_overrides"
                            :key="widget.id"
                            class="bg-gray-50 rounded p-3"
                        >
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ widget.screenWidget?.widget?.name }}</p>
                                    <p class="text-xs text-gray-600 mt-1">
                                        Screen: {{ widget.screen?.name }}
                                    </p>
                                    <p v-if="widget.selectedTheme" class="text-xs text-gray-600">
                                        Theme: {{ widget.selectedTheme?.name }}
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2 text-sm">
                                    <span v-if="widget.is_visible" class="text-green-600">👁️</span>
                                    <span v-else class="text-gray-400">👁️</span>
                                    <span class="text-gray-600">Order: {{ widget.display_order }}</span>
                                </div>
                            </div>

                            <!-- Widget's Children -->
                            <div v-if="widget.themeChildOverrides?.length > 0" class="mt-3 ml-4 space-y-2 border-t pt-3">
                                <p class="text-xs font-semibold text-indigo-700">Children ({{ widget.themeChildOverrides.length }}):</p>
                                <div
                                    v-for="child in widget.themeChildOverrides"
                                    :key="child.id"
                                    class="text-xs p-2 bg-white rounded border border-gray-200"
                                >
                                    <div class="flex justify-between items-center">
                                        <span>{{ child.themeChild?.name }}</span>
                                        <span v-if="child.is_visible" class="text-green-600">👁️</span>
                                        <span v-else class="text-gray-400">👁️</span>
                                    </div>
                                    <div v-if="child.assetOverrides?.length > 0" class="mt-1 text-gray-600">
                                        📦 {{ child.assetOverrides.length }} assets
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Level 5: Assets -->
                <div class="bg-white rounded-lg p-4 border-l-4 border-orange-500">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-semibold text-gray-900">Level 5: Assets</h4>
                        <span class="px-2 py-1 bg-orange-100 text-orange-700 text-sm rounded">
                            {{ stats.total_asset_overrides + stats.total_child_asset_overrides }} assets
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-3">
                        Theme asset overrides (colors, logos, fonts) and child-specific assets
                    </p>
                    <div class="flex space-x-2">
                        <button
                            @click="expandLevel = expandLevel === 'assets' ? null : 'assets'"
                            class="px-3 py-1 text-sm bg-orange-500 text-white rounded hover:bg-orange-600"
                        >
                            {{ expandLevel === 'assets' ? '▼' : '▶' }} View Details
                        </button>
                    </div>

                    <div v-if="expandLevel === 'assets'" class="mt-4 space-y-2 border-t pt-4">
                        <div v-if="config.asset_overrides?.length > 0">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Direct Theme Assets ({{ config.asset_overrides.length }}):</p>
                            <div
                                v-for="asset in config.asset_overrides"
                                :key="asset.id"
                                class="flex justify-between items-center p-2 bg-gray-50 rounded text-sm"
                            >
                                <span>{{ asset.themeAsset?.name }}</span>
                                <span class="text-gray-600">
                                    {{ asset.value_override ? '✏️ Value' : '📄 File' }}
                                </span>
                            </div>
                        </div>

                        <div v-if="stats.total_child_asset_overrides > 0" class="mt-4">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Child Assets ({{ stats.total_child_asset_overrides }}):</p>
                            <p class="text-sm text-gray-600">Assets override within theme children</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visibility Summary -->
        <div class="bg-white rounded-lg p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">👁️ Visibility Summary</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <VisibilityBar
                    label="Screens"
                    :visible="stats.visible_screens"
                    :total="stats.total_screens"
                    color="blue"
                />
                <VisibilityBar
                    label="Widgets"
                    :visible="stats.visible_widgets"
                    :total="stats.total_widgets"
                    color="purple"
                />
                <VisibilityBar
                    label="Children"
                    :visible="stats.visible_children"
                    :total="stats.total_children"
                    color="indigo"
                />
                <VisibilityBar
                    label="Total Items"
                    :visible="stats.visible_screens + stats.visible_widgets + stats.visible_children"
                    :total="stats.total_screens + stats.total_widgets + stats.total_children"
                    color="gray"
                />
            </div>
        </div>

        <!-- Delete Button -->
        <div class="flex justify-end">
            <button
                @click="confirmDelete"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
            >
                🗑️ Delete Configuration
            </button>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue';
import StatCard from './StatCard.vue';
import VisibilityBar from './VisibilityBar.vue';

export default {
    name: 'ConfigurationDetails',
    components: {
        StatCard,
        VisibilityBar,
    },
    props: {
        config: {
            type: Object,
            required: true,
        },
        stats: {
            type: Object,
            default: () => ({})
        }
    },
    emits: ['clone', 'edit', 'activate', 'delete'],
    setup() {
        const expandLevel = ref(null);

        const confirmDelete = () => {
            if (confirm('Are you sure you want to delete this configuration? This action cannot be undone.')) {
                // emit delete event
            }
        };

        return {
            expandLevel,
            confirmDelete,
        };
    }
};
</script>
