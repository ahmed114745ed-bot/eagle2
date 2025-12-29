<template>
    <div class="text-sm">
        <span v-if="!flip" class="font-semibold" :class="getColor(value1, value2)">
            {{ value1 }}
        </span>
        <span v-else class="font-semibold" :class="getColor(value2, value1)">
            {{ value2 }}
        </span>
        <span v-if="showDifference" class="text-gray-600 text-xs ml-1">
            ({{ difference > 0 ? '+' : '' }}{{ difference }})
        </span>
    </div>
</template>

<script>
import { computed } from 'vue';

export default {
    name: 'ComparisonValue',
    props: {
        value1: {
            type: Number,
            required: true
        },
        value2: {
            type: Number,
            required: true
        },
        flip: {
            type: Boolean,
            default: false
        }
    },
    setup(props) {
        const difference = computed(() => {
            if (props.flip) {
                return props.value2 - props.value1;
            }
            return props.value1 - props.value2;
        });

        const showDifference = computed(() => difference.value !== 0);

        const getColor = (val1, val2) => {
            if (val1 > val2) return 'text-green-600';
            if (val1 < val2) return 'text-red-600';
            return 'text-gray-600';
        };

        return {
            difference,
            showDifference,
            getColor,
        };
    }
};
</script>
