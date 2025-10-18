<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType, NavItem } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    sidebarItems?: NavItem[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    sidebarItems: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar :main-nav-items="sidebarItems" />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
    </AppShell>
</template>
