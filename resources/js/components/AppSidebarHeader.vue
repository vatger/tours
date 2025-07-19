<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import ToggleMode from './ToggleMode.vue';

import { loadLanguageAsync } from 'laravel-vue-i18n';
import { usePage } from '@inertiajs/vue3';
import LanguageSwitcher from './LanguageSwitcher.vue';
const locale = usePage().props.locale as string;
loadLanguageAsync(locale);
withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center justify-between border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12 md:px-4">

        <!-- class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"> -->
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
        <!-- Lado direito - Ajuste o padding aqui -->
        <div class="flex items-center gap-4 pr-2">
            <div class="relative">
                <LanguageSwitcher />
            </div>

            <div class="relative">
                <ToggleMode />
            </div>
            <!-- <div class="relative">
                <NotificationBell />
            </div> -->
        </div>
    </header>
</template>
