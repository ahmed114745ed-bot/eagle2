<template>
    <div class="space-y-4">
        <h3 class="text-lg font-semibold text-gray-900">📝 Activity Log</h3>

        <div v-if="!config" class="text-center py-8 text-gray-500">
            <p>Select a configuration to view activity</p>
        </div>

        <div v-else-if="!activities || activities.length === 0" class="bg-gray-50 rounded-lg p-8 text-center">
            <p class="text-gray-500">No activity recorded for this configuration</p>
        </div>

        <div v-else class="space-y-3">
            <!-- Activity Timeline -->
            <div class="relative">
                <div v-for="(activity, index) in activities" :key="activity.id" class="flex gap-4 pb-6">
                    <!-- Timeline line -->
                    <div v-if="index < activities.length - 1" class="absolute left-4 top-12 bottom-0 w-0.5 bg-gray-200"></div>

                    <!-- Timeline dot -->
                    <div :class="['mt-1 flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-bold relative z-10', getActivityColor(activity.type)]">
                        {{ getActivityIcon(activity.type) }}
                    </div>

                    <!-- Activity content -->
                    <div class="flex-1 bg-white rounded-lg p-4 border border-gray-200">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-semibold text-gray-900">{{ getActivityLabel(activity.type) }}</p>
                                <p class="text-sm text-gray-600 mt-1">{{ activity.description }}</p>
                            </div>
                            <span class="text-xs text-gray-500 whitespace-nowrap ml-4">
                                {{ formatTime(activity.created_at) }}
                            </span>
                        </div>

                        <!-- Activity details -->
                        <div v-if="activity.details" class="mt-3 pt-3 border-t border-gray-200">
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div v-for="(value, key) in activity.details" :key="key" class="text-gray-600">
                                    <span class="font-medium">{{ key }}:</span> {{ value }}
                                </div>
                            </div>
                        </div>

                        <!-- User info -->
                        <div v-if="activity.user" class="mt-3 pt-3 border-t border-gray-200 text-xs text-gray-500">
                            👤 {{ activity.user.name }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary stats -->
        <div v-if="activities && activities.length > 0" class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-8 pt-6 border-t border-gray-200">
            <StatBox
                icon="✏️"
                label="Edits"
                :count="countByType('edit')"
            />
            <StatBox
                icon="➕"
                label="Additions"
                :count="countByType('add')"
            />
            <StatBox
                icon="🗑️"
                label="Deletions"
                :count="countByType('delete')"
            />
            <StatBox
                icon="⭐"
                label="Activations"
                :count="countByType('activate')"
            />
        </div>
    </div>
</template>

<script>
import { computed } from 'vue';
import StatBox from './StatBox.vue';

export default {
    name: 'ConfigurationActivity',
    components: {
        StatBox,
    },
    props: {
        config: {
            type: Object,
            default: null
        },
        activities: {
            type: Array,
            default: () => []
        }
    },
    setup(props) {
        const getActivityColor = (type) => {
            const colors = {
                create: 'bg-green-500',
                edit: 'bg-blue-500',
                delete: 'bg-red-500',
                activate: 'bg-purple-500',
                add: 'bg-indigo-500',
                remove: 'bg-orange-500',
                clone: 'bg-yellow-500',
            };
            return colors[type] || 'bg-gray-500';
        };

        const getActivityIcon = (type) => {
            const icons = {
                create: '✨',
                edit: '✏️',
                delete: '🗑️',
                activate: '⭐',
                add: '➕',
                remove: '➖',
                clone: '📋',
            };
            return icons[type] || '•';
        };

        const getActivityLabel = (type) => {
            const labels = {
                create: 'Configuration Created',
                edit: 'Configuration Edited',
                delete: 'Configuration Deleted',
                activate: 'Configuration Activated',
                add: 'Item Added',
                remove: 'Item Removed',
                clone: 'Configuration Cloned',
            };
            return labels[type] || 'Activity';
        };

        const formatTime = (date) => {
            if (!date) return 'N/A';
            const now = new Date();
            const activityDate = new Date(date);
            const diffMs = now - activityDate;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);

            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins}m ago`;
            if (diffHours < 24) return `${diffHours}h ago`;
            if (diffDays < 7) return `${diffDays}d ago`;
            
            return activityDate.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: activityDate.getFullYear() !== now.getFullYear() ? 'numeric' : undefined,
            });
        };

        const countByType = (type) => {
            return props.activities.filter(a => a.type === type).length;
        };

        return {
            getActivityColor,
            getActivityIcon,
            getActivityLabel,
            formatTime,
            countByType,
        };
    }
};
</script>
