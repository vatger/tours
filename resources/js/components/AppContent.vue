<script setup lang="ts">
import { SidebarInset } from '@/components/ui/sidebar'

interface Props {
  variant?: 'header' | 'sidebar'
  class?: string
}

defineProps<Props>()
</script>

<template>
  <SidebarInset v-if="variant === 'sidebar'" :class="class">
    <slot />
  </SidebarInset>
  <main
    v-else
    class="flex h-full flex-1 flex-col gap-4 rounded-xl max-w-7xl mx-auto w-full"
    :class="class"
  >
    <slot />
  </main>
</template>
