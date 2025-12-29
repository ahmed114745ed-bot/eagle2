<template>
    <div class="flex flex-col">
        <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-medium text-gray-700">{{ label }}</span>
            <span class="text-sm font-bold text-gray-900">{{ visible }}/{{ total }}</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div
                :class="[
                    'h-2 rounded-full transition-all',
                    colorClasses[color]
                ]"
                :style="{ width: total > 0 ? (visible / total * 100) + '%' : '0%' }"
            ></div>
        </div>
        <p class="text-xs text-gray-600 mt-1">
            {{ total > 0 ? Math.round((visible / total) * 100) : 0 }}% visible
        </p>
    </div>
</template>

<script>
export default {
    name: 'VisibilityBar',
    props: {
        label: {
            type: String,
            required: true
        },
        visible: {
            type: Number,
            default: 0
        },
        total: {
            type: Number,
            default: 0
        },
        color: {
            type: String,
            default: 'blue'
        }
    },
    setup() {
        const colorClasses = {
            blue: 'bg-blue-500',
            purple: 'bg-purple-500',
            indigo: 'bg-indigo-500',
            green: 'bg-green-500',
            orange: 'bg-orange-500',
            gray: 'bg-gray-500',
        };

        return {
            colorClasses,
        };
    }
};
</script>
