<script setup lang="ts">
import { Sun, Moon, Monitor } from 'lucide-vue-next'
import { useAppearance } from '@/Composables/useAppearance'

interface Props {
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  class: ''
})

const { appearance, updateAppearance } = useAppearance()

const tabs = [
  { value: 'light', icon: Sun, label: 'Light' },
  { value: 'dark', icon: Moon, label: 'Dark' },
  { value: 'system', icon: Monitor, label: 'System' }
] as const
</script>

<template>
  <div 
    :class="[
      'inline-flex bg-neutral-100 dark:bg-neutral-800 p-1 gap-1 rounded-lg',
      props.class
    ]"
  >
    <button
      v-for="{ value, icon: Icon, label } in tabs"
      :key="value"
      @click="updateAppearance(value)"
      :class="[
        'flex items-center px-3.5 py-1.5 rounded-md transition-colors',
        appearance === value
          ? 'bg-white dark:bg-neutral-700 shadow-sm dark:text-neutral-100'
          : 'hover:bg-neutral-200/60 text-neutral-500 hover:text-black dark:hover:bg-neutral-700/60 dark:text-neutral-400'
      ]"
    >
      <Icon class="h-4 w-4 -ml-1" />
      <span class="ml-1.5 text-sm">{{ label }}</span>
    </button>
  </div>
</template>
