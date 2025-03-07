<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

const props = withDefaults(
    defineProps<{
        class?: HTMLAttributes['class'];
        size?: 'small' | 'large';
        shape?: 'circle' | 'square';
    }>(),
    {
        size: 'small',
        shape: 'circle',
    },
);
</script>

<template>
    <VAvatar :class="props.class" :size="props.size" :rounded="props.shape === 'circle'">
        <slot />
    </VAvatar>
</template>
