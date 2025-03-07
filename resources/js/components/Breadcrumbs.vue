<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface BreadcrumbItem {
    title: string;
    href?: string;
}

defineProps<{
    breadcrumbs: BreadcrumbItem[];
}>();
</script>

<template>
    <v-breadcrumbs>
        <template v-for="(item, index) in breadcrumbs" :key="index">
            <v-breadcrumbs-item :to="item.href" :exact="index === breadcrumbs.length - 1">
                <template v-if="index === breadcrumbs.length - 1">
                    {{ item.title }}
                </template>
                <template v-else>
                    <Link :href="item.href ?? '#'">{{ item.title }}</Link>
                </template>
            </v-breadcrumbs-item>
        </template>
    </v-breadcrumbs>
</template>
