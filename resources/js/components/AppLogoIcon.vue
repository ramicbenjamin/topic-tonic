<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 40 42"
        :class="className"
        v-bind="$attrs"
        fill="currentColor"
    >
        <path
            d="M20 3c9.389 0 17 6.496 17 14.5 0 8.005-7.611 14.5-17 14.5-1.93 0-3.784-.28-5.5-.8L5 37l2.3-7.2C4.49 27.225 3 23.987 3 20.5 3 9.835 10.611 3 20 3zm-7 15h4l2-4 3 8 2-4h3"
            stroke="currentColor"
            stroke-width="2"
            fill="none"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
    </svg>
</template>

