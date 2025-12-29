<template>
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center"
            >
                <!-- Backdrop -->
                <div
                    class="absolute inset-0 bg-black/50"
                    @click="$emit('close')"
                ></div>

                <!-- Modal Content -->
                <div class="relative bg-gray-800 rounded-3xl p-4 shadow-2xl max-w-md w-full mx-4">
                    <!-- Phone Frame -->
                    <div class="relative bg-black rounded-[2.5rem] p-2 shadow-inner">
                        <!-- Notch -->
                        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-6 bg-black rounded-b-2xl z-10"></div>

                        <!-- Screen -->
                        <div class="bg-gradient-to-b from-gray-900 to-gray-800 rounded-[2rem] overflow-hidden h-[600px]">
                            <!-- Status Bar -->
                            <div class="flex justify-between items-center px-6 pt-2 pb-1 text-white text-xs">
                                <span>9:41</span>
                                <div class="flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 3C7.03 3 3 7.03 3 12s4.03 9 9 9 9-4.03 9-9-4.03-9-9-9z"/>
                                    </svg>
                                    <span>100%</span>
                                </div>
                            </div>

                            <!-- Screen Content -->
                            <div class="relative h-full overflow-y-auto pb-20">
                                <!-- Header -->
                                <div class="flex items-center justify-between px-4 py-3">
                                    <h1 class="text-white text-lg font-bold">{{ screenData?.screen_name || 'Preview' }}</h1>
                                    <div class="flex space-x-2">
                                        <div class="w-8 h-8 bg-gray-700 rounded-full"></div>
                                    </div>
                                </div>

                                <!-- Widgets Preview -->
                                <div class="space-y-3 px-4">
                                    <div v-for="widget in regularWidgets" :key="widget.id">
                                        <!-- Tab Bar Widget -->
                                        <div v-if="widget.widget_type === 'tab_bar'" class="bg-gray-800/50 rounded-xl p-3">
                                            <div class="flex space-x-2 overflow-x-auto">
                                                <div
                                                    v-for="(child, index) in widget.children || [{name: 'Hot'}, {name: 'Following'}, {name: 'Games'}]"
                                                    :key="index"
                                                    :class="[
                                                        'px-4 py-2 rounded-full text-sm whitespace-nowrap',
                                                        index === 0 ? 'bg-pink-500 text-white' : 'bg-gray-700 text-gray-300'
                                                    ]"
                                                >
                                                    {{ child.name || child.title || 'Tab ' + (index + 1) }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Banner Widget -->
                                        <div v-else-if="widget.widget_type === 'banner'" class="relative">
                                            <div class="bg-gradient-to-r from-pink-500 to-purple-600 rounded-xl h-32 flex items-center justify-center">
                                                <span class="text-white/70 text-sm">Banner Image</span>
                                            </div>
                                            <div class="flex justify-center mt-2 space-x-1">
                                                <div v-for="i in 3" :key="i" :class="['w-2 h-2 rounded-full', i === 1 ? 'bg-pink-500' : 'bg-gray-600']"></div>
                                            </div>
                                        </div>

                                        <!-- Room Widget -->
                                        <div v-else-if="widget.widget_type === 'room'">
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="text-white font-semibold">Live Rooms</span>
                                                <span class="text-pink-400 text-sm">See All</span>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div v-for="i in 4" :key="i" class="relative">
                                                    <div class="bg-gradient-to-b from-gray-700 to-gray-800 rounded-xl aspect-square flex items-center justify-center">
                                                        <svg class="w-12 h-12 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                                        </svg>
                                                    </div>
                                                    <div class="absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">LIVE</div>
                                                    <div class="absolute bottom-2 left-2 right-2">
                                                        <p class="text-white text-xs truncate">Streamer {{ i }}</p>
                                                        <p class="text-gray-400 text-xs flex items-center">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                                                <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5z"/>
                                                            </svg>
                                                            {{ Math.floor(Math.random() * 10000) }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Ranking Widget -->
                                        <div v-else-if="widget.widget_type === 'ranking'" class="bg-gray-800/50 rounded-xl p-3">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-yellow-400 font-semibold">🏆 Rankings</span>
                                            </div>
                                            <div class="flex space-x-4 overflow-x-auto">
                                                <div v-for="i in 3" :key="i" class="flex flex-col items-center min-w-[60px]">
                                                    <div :class="['w-12 h-12 rounded-full', i === 1 ? 'bg-yellow-500' : i === 2 ? 'bg-gray-400' : 'bg-amber-700']"></div>
                                                    <span class="text-white text-xs mt-1">User {{ i }}</span>
                                                    <span class="text-yellow-400 text-xs">#{{ i }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Categories Widget -->
                                        <div v-else-if="widget.widget_type === 'categories'">
                                            <div class="flex space-x-3 overflow-x-auto py-2">
                                                <div v-for="cat in ['🎮 Games', '🎵 Music', '💃 Dance', '🎨 Art']" :key="cat" class="flex flex-col items-center min-w-[60px]">
                                                    <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-2xl">
                                                        {{ cat.split(' ')[0] }}
                                                    </div>
                                                    <span class="text-white text-xs mt-1">{{ cat.split(' ')[1] }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Country Filter Widget -->
                                        <div v-else-if="widget.widget_type === 'country_filter'" class="bg-gray-800/50 rounded-xl p-3">
                                            <div class="flex space-x-2">
                                                <span class="px-3 py-1 bg-pink-500 text-white text-sm rounded-full">🌍 All</span>
                                                <span class="px-3 py-1 bg-gray-700 text-gray-300 text-sm rounded-full">🇪🇬 Egypt</span>
                                                <span class="px-3 py-1 bg-gray-700 text-gray-300 text-sm rounded-full">🇸🇦 KSA</span>
                                            </div>
                                        </div>

                                        <!-- Default Widget -->
                                        <div v-else class="bg-gray-800/50 rounded-xl p-4">
                                            <span class="text-gray-400 text-sm">{{ widget.widget_type }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Positioned Widgets -->
                                <div v-for="widget in positionedWidgets" :key="widget.id">
                                    <div
                                        v-if="widget.widget_type === 'floating_button'"
                                        class="absolute"
                                        :style="getPositionStyle(widget.position)"
                                    >
                                        <div class="w-14 h-14 bg-gradient-to-br from-pink-500 to-purple-600 rounded-full flex items-center justify-center shadow-lg animate-pulse">
                                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom Navigation -->
                            <div class="absolute bottom-0 left-0 right-0 bg-gray-900/95 border-t border-gray-700 px-6 py-3">
                                <div class="flex justify-around items-center">
                                    <div v-for="item in ['🏠', '🔍', '➕', '💬', '👤']" :key="item" class="text-2xl text-gray-400">
                                        {{ item }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Close Button -->
                    <button
                        @click="$emit('close')"
                        class="absolute -top-3 -right-3 w-8 h-8 bg-white rounded-full shadow-lg flex items-center justify-center hover:bg-gray-100"
                    >
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>

                    <!-- Screen Info -->
                    <div class="text-center mt-4">
                        <p class="text-gray-400 text-sm">{{ screenData?.screen_key }}</p>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script>
import { computed } from 'vue';

export default {
    name: 'PreviewModal',
    props: {
        show: {
            type: Boolean,
            default: false,
        },
        screenData: {
            type: Object,
            default: null,
        },
    },
    emits: ['close'],
    setup(props) {
        const regularWidgets = computed(() => {
            return props.screenData?.widgets?.filter(w => !w.is_positioned) || [];
        });

        const positionedWidgets = computed(() => {
            return props.screenData?.widgets?.filter(w => w.is_positioned) || [];
        });

        const getPositionStyle = (position) => {
            if (!position) return {};

            const style = {};

            if (position.top_percentage !== undefined && position.top_percentage !== null) {
                style.top = `${position.top_percentage}%`;
            }
            if (position.bottom_percentage !== undefined && position.bottom_percentage !== null) {
                style.bottom = `${position.bottom_percentage}%`;
            }
            if (position.left_percentage !== undefined && position.left_percentage !== null) {
                style.left = `${position.left_percentage}%`;
            }
            if (position.right_percentage !== undefined && position.right_percentage !== null) {
                style.right = `${position.right_percentage}%`;
            }

            return style;
        };

        return {
            regularWidgets,
            positionedWidgets,
            getPositionStyle,
        };
    },
};
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
    transition: transform 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
    transform: scale(0.9);
}
</style>
