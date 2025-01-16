<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

defineProps<{
    name?: string;
}>();
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 pt-0">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <svg class="absolute inset-0 size-full stroke-gray-900/10 dark:stroke-neutral-100/10" fill="none">
                        <defs>
                            <pattern id="pattern-01" x="0" y="0" width="10" height="10" patternUnits="userSpaceOnUse">
                                <path d="M-3 13 15-5M-5 5l18-18M-1 21 17 3"></path>
                            </pattern>
                        </defs>
                        <rect stroke="none" fill="url(#pattern-01)" width="100%" height="100%"></rect>
                    </svg>
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <svg class="absolute inset-0 size-full stroke-gray-900/10 dark:stroke-neutral-100/10" fill="none">
                        <defs>
                            <pattern id="pattern-01" x="0" y="0" width="10" height="10" patternUnits="userSpaceOnUse">
                                <path d="M-3 13 15-5M-5 5l18-18M-1 21 17 3"></path>
                            </pattern>
                        </defs>
                        <rect stroke="none" fill="url(#pattern-01)" width="100%" height="100%"></rect>
                    </svg>
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <svg class="absolute inset-0 size-full stroke-gray-900/10 dark:stroke-neutral-100/10" fill="none">
                        <defs>
                            <pattern id="pattern-01" x="0" y="0" width="10" height="10" patternUnits="userSpaceOnUse">
                                <path d="M-3 13 15-5M-5 5l18-18M-1 21 17 3"></path>
                            </pattern>
                        </defs>
                        <rect stroke="none" fill="url(#pattern-01)" width="100%" height="100%"></rect>
                    </svg>
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
                <svg class="absolute inset-0 size-full stroke-gray-900/10 dark:stroke-neutral-100/10" fill="none">
                    <defs>
                        <pattern id="pattern-01" x="0" y="0" width="10" height="10" patternUnits="userSpaceOnUse">
                            <path d="M-3 13 15-5M-5 5l18-18M-1 21 17 3"></path>
                        </pattern>
                    </defs>
                    <rect stroke="none" fill="url(#pattern-01)" width="100%" height="100%"></rect>
                </svg>
            </div>
        </div>
    </AppLayout>
</template>
