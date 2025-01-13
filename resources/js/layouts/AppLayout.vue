<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue';
import AppHeader from '@/components/AppHeader.vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppSidebar>
        <AppHeader :breadcrumbs="props.breadcrumbs" />
        <slot />
    </AppSidebar>
</template>
