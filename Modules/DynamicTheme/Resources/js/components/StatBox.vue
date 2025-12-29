<template>
    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-center">
        <span class="text-3xl block mb-2">{{ icon }}</span>
        <p class="text-sm font-medium text-gray-600">{{ label }}</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ count }}</p>
    </div>
</template>

<script>
export default {
    name: 'StatBox',
    props: {
        icon: {
            type: String,
            required: true
        },
        label: {
            type: String,
            required: true
        },
        count: {
            type: Number,
            default: 0
        }
    }
};
</script>
