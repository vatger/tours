<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { SidebarProvider } from '@/components/ui/sidebar'

interface Props {
  variant?: 'header' | 'sidebar'
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'header'
})

const isOpen = ref(true)

onMounted(() => {
  isOpen.value = localStorage.getItem('sidebar') !== 'false'
})

const handleSidebarChange = (open: boolean) => {
  isOpen.value = open
  localStorage.setItem('sidebar', String(open))
}
</script>

<template>
  <div v-if="variant === 'header'" class="flex flex-col min-h-screen w-full">
    <slot />
  </div>
  <SidebarProvider
    v-else
    :default-open="isOpen"
    :open="isOpen"
    @update:open="handleSidebarChange"
  >
    <slot />
  </SidebarProvider>
</template>
