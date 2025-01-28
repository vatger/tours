<script setup lang="ts">
import AppHeader from '@/components/AppHeader.vue'
import AppShell from '@/components/AppShell.vue'
import AppContent from '@/components/AppContent.vue'
import type { BreadcrumbItemType } from '@/types'

interface Props {
    breadcrumbs?: BreadcrumbItemType[]
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
})
</script>

<template>
    <AppShell>
        <AppHeader :breadcrumbs="props.breadcrumbs" />
        <AppContent>
            <slot />
        </AppContent>
    </AppShell>
</template>