<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue';
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator } from '@/components/ui/breadcrumb';
import { Separator } from '@/components/ui/separator';
import { SidebarInset, SidebarProvider, SidebarTrigger } from '@/components/ui/sidebar';
import { type BreadcrumbItem as BreadcrumbItemType } from '@/types';
import { ref } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const isOpen = ref(typeof window !== 'undefined' ? localStorage.getItem('sidebar') !== 'false' : true);

const handleSidebarChange = (open: boolean) => {
    isOpen.value = open;
    if (typeof window !== 'undefined') {
        localStorage.setItem('sidebar', String(open));
    }
};
</script>

<template>
    <SidebarProvider :default-open="isOpen" :open="isOpen" @update:open="handleSidebarChange">
        <AppSidebar />
        <SidebarInset>
            <header class="flex h-16 w-full shrink-0 items-center justify-between gap-2 border-b px-4">
                <div class="flex items-center gap-2">
                    <SidebarTrigger class="-ml-1" />
                    <template v-if="breadcrumbs.length > 0">
                        <Separator orientation="vertical" class="mr-2 h-4" />
                        <Breadcrumb>
                            <BreadcrumbList>
                                <template v-for="(item, index) in breadcrumbs" :key="index">
                                    <BreadcrumbItem>
                                        <template v-if="index === breadcrumbs.length - 1">
                                            <BreadcrumbPage>{{ item.title }}</BreadcrumbPage>
                                        </template>
                                        <template v-else>
                                            <BreadcrumbLink :href="item.href">
                                                {{ item.title }}
                                            </BreadcrumbLink>
                                        </template>
                                    </BreadcrumbItem>
                                    <BreadcrumbSeparator v-if="index !== breadcrumbs.length - 1" />
                                </template>
                            </BreadcrumbList>
                        </Breadcrumb>
                    </template>
                </div>
            </header>
            <slot />
        </SidebarInset>
    </SidebarProvider>
</template>
