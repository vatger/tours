<script setup lang="ts">
import { computed } from 'vue'
import type { LucideIcon as LucideIconType } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

interface Props {
    iconNode: LucideIconType
    class?: string
    size?: number | string
    color?: string
    strokeWidth?: number | string
}

const props = withDefaults(defineProps<Props>(), {
    class: '',
    size: 16,
    strokeWidth: 2
})

const className = computed(() => cn('h-4 w-4', props.class))
</script>

<template>
    <component 
        :is="iconNode" 
        :class="className"
        :size="props.size"
        :stroke-width="props.strokeWidth"
        :color="props.color"
    />
</template>
