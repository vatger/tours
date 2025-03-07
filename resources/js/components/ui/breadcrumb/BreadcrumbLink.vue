<script lang="ts" setup>
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = withDefaults(defineProps<{ class?: HTMLAttributes['class'] }>(), {
    as: 'a',
});
</script>

<template>
    <VBtn :class="cn('transition-colors hover:text-foreground', props.class)">
        <slot />
    </VBtn>
</template>
