<template>
    <DetailLayout :breadcrumbs="breadcrumbs">

        <Head :title="title" />
        <div class="px-4 py-4">
            <slot></slot>
        </div>
    </DetailLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { DetailLayout } from '@/components/grgsdev/layout';

defineProps<{
    title: string;
    breadcrumbs: BreadcrumbItem[];
}>();
</script>
