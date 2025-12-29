<template>
    <div class="bg-gradient-to-br rounded-lg p-4 shadow-sm border" :class="bgClasses">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">{{ label }}</p>
                <p class="text-2xl font-bold mt-2" :class="textClasses">{{ total }}</p>
                <p class="text-xs text-gray-600 mt-1">{{ visible }} visible</p>
            </div>
            <span class="text-3xl">{{ icon }}</span>
        </div>
    </div>
</template>

<script>
export default {
    name: 'StatCard',
    props: {
        icon: {
            type: String,
            required: true
        },
        label: {
            type: String,
            required: true
        },
        total: {
            type: Number,
            default: 0
        },
        visible: {
            type: Number,
            default: 0
        }
    },
    setup(props) {
        const getBgColor = () => {
            if (props.label.includes('Screen')) return 'from-blue-50 to-blue-100 border-blue-200';
            if (props.label.includes('Widget')) return 'from-purple-50 to-purple-100 border-purple-200';
            if (props.label.includes('Child')) return 'from-indigo-50 to-indigo-100 border-indigo-200';
            return 'from-orange-50 to-orange-100 border-orange-200';
        };

        const getTextColor = () => {
            if (props.label.includes('Screen')) return 'text-blue-600';
            if (props.label.includes('Widget')) return 'text-purple-600';
            if (props.label.includes('Child')) return 'text-indigo-600';
            return 'text-orange-600';
        };

        return {
            bgClasses: getBgColor(),
            textClasses: getTextColor(),
        };
    }
};
</script>
