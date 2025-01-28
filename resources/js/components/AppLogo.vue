<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue'

interface Props {
    class?: string
}

defineProps<Props>()
</script>

<template>
    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
        <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
    </div>
    <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate mb-0.5 font-semibold leading-none">Laravel</span>
        <span class="truncate text-[11px] tracking-tight opacity-80 leading-none">Starter Kit</span>
    </div>
</template>
