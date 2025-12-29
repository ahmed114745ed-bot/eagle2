<template>
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900">📱 Screens</h2>
            <!--    <button
                    @click="showCreateModal = true"
                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg"
                    title="Create new screen"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>
                -->
            </div>
        </div>

        <div class="p-2 max-h-64 overflow-y-auto">
            <draggable
                v-model="localScreensList"
                item-key="id"
                handle=".drag-handle"
                ghost-class="opacity-50"
                @end="onDragEnd"
            >
                <template #item="{ element: screen }">
                    <div
                        :class="[
                            'p-3 rounded-lg mb-2 transition-colors border-2',
                            selectedScreen?.id === screen.id
                                ? 'bg-blue-100 border-blue-500'
                                : 'bg-gray-50 hover:bg-gray-100 border-transparent',
                            !isScreenVisible(screen) ? 'opacity-50' : ''
                        ]"
                    >
                        <div class="flex items-center justify-between">
                            <!-- Drag Handle -->
                            <div class="flex items-center space-x-2">
                                <button
                                    class="drag-handle cursor-move text-gray-400 hover:text-gray-600"
                                    title="Drag to reorder"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                    </svg>
                                </button>

                                <!-- Visibility Toggle -->
                                <button
                                    @click.stop="$emit('toggle-visibility', screen)"
                                    :class="[
                                        'p-1 rounded transition-colors',
                                        isScreenVisible(screen) ? 'text-green-600 hover:bg-green-50' : 'text-gray-400 hover:bg-gray-100'
                                    ]"
                                    :title="isScreenVisible(screen) ? 'Click to hide' : 'Click to show'"
                                >
                                    <svg v-if="isScreenVisible(screen)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Screen Info -->
                            <div
                                class="flex-1 ml-2 cursor-pointer"
                                @click="$emit('select', screen)"
                            >
                                <p class="font-medium text-gray-900">{{ screen.screen_name }}</p>
                                <p class="text-sm text-gray-500">{{ screen.screen_key }}</p>
                            </div>

                            <!-- Status Badge -->
                            <span
                                :class="[
                                    'px-2 py-1 text-xs rounded-full',
                                    screen.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                                ]"
                            >
                                {{ screen.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </template>
            </draggable>
        </div>

        <!-- Create Screen Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl p-6 w-96">
                <h3 class="text-lg font-semibold mb-4">Create New Screen</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Screen Key</label>
                        <input
                            v-model="newScreen.screen_key"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="e.g., home_new"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Screen Name</label>
                        <input
                            v-model="newScreen.screen_name"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="e.g., Home - New"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Min App Version</label>
                        <input
                            v-model="newScreen.min_app_version"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="e.g., 2.0.0"
                        />
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button
                        @click="showCreateModal = false"
                        class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg"
                    >
                        Cancel
                    </button>
                    <button
                        @click="createScreen"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        Create
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, watch } from 'vue';
import draggable from 'vuedraggable';

export default {
    name: 'ScreensList',
    components: {
        draggable,
    },
    props: {
        screens: {
            type: Array,
            required: true,
        },
        selectedScreen: {
            type: Object,
            default: null,
        },
        screenOverrides: {
            type: Array,
            default: () => [],
        },
    },
    emits: ['select', 'create', 'toggle-visibility', 'reorder'],
    setup(props, { emit }) {
        const showCreateModal = ref(false);
        const newScreen = ref({
            screen_key: '',
            screen_name: '',
            min_app_version: '2.0.0',
        });

        // Local mutable list for draggable - initialize from sorted screens
        const localScreensList = ref([]);

        // Sort screens by override display_order or original order
        const sortedScreens = computed(() => {
            return [...props.screens].sort((a, b) => {
                const overrideA = props.screenOverrides.find(o => o.screen_id === a.id);
                const overrideB = props.screenOverrides.find(o => o.screen_id === b.id);
                const orderA = overrideA?.display_order ?? a.display_order ?? 0;
                const orderB = overrideB?.display_order ?? b.display_order ?? 0;
                return orderA - orderB;
            });
        });

        // Watch for changes in screens/overrides and update local list
        watch([() => props.screens, () => props.screenOverrides], () => {
            localScreensList.value = [...sortedScreens.value];
        }, { immediate: true, deep: true });

        // Check if screen is visible based on overrides
        const isScreenVisible = (screen) => {
            const override = props.screenOverrides.find(o => o.screen_id === screen.id);
            return override ? override.is_visible : true;
        };

        const onDragEnd = () => {
            // Emit reorder with the current list order
            emit('reorder', [...localScreensList.value]);
        };

        const createScreen = () => {
            if (newScreen.value.screen_key && newScreen.value.screen_name) {
                emit('create', { ...newScreen.value });
                showCreateModal.value = false;
                newScreen.value = {
                    screen_key: '',
                    screen_name: '',
                    min_app_version: '2.0.0',
                };
            }
        };

        return {
            showCreateModal,
            newScreen,
            localScreensList,
            isScreenVisible,
            onDragEnd,
            createScreen,
        };
    },
};
</script>
