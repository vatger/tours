<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
    'aria-labelledby'?: string;
    'aria-describedby'?: string;
}>();
</script>

<template>
    <div 
        :class="cn('rounded-lg border bg-card text-card-foreground shadow-sm', props.class)"
        role="region"
        :aria-labelledby="props['aria-labelledby']"
        :aria-describedby="props['aria-describedby']"
    >
        <slot />
    </div>
</template>
