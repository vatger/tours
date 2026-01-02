<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
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
  <AppLayout :breadcrumbs="breadcrumbs" :sidebarItems="sidebarItems">
    <slot />
  </AppLayout>
</template>
