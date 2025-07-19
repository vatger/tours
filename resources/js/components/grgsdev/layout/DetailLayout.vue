<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';

defineProps<{
    breadcrumbs: BreadcrumbItem[]
}>()
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-2 py-2 space-y-2">
            <slot />
        </div>
    </AppLayout>
</template>
