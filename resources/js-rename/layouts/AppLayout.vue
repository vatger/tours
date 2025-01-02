<script setup lang="ts">
import { ref } from 'vue';
import AppSidebar from '@/components/AppSidebar.vue';
import { 
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import { Separator } from '@/components/ui/separator';
import {
    SidebarInset,
    SidebarProvider,
    SidebarTrigger,
} from '@/components/ui/sidebar';

interface BreadcrumbItem {
    title: string;
    href: string;
}

interface Props {
    breadcrumbItems?: BreadcrumbItem[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbItems: () => [],
});

const isOpen = ref(localStorage.getItem('sidebar') === 'true' ?? true);

const handleSidebarChange = (open: boolean) => {
    isOpen.value = open;
    localStorage.setItem('sidebar', String(open));
};
</script>

<template>
    <SidebarProvider 
        :default-open="isOpen" 
        :open="isOpen"
        @update:open="handleSidebarChange"
    >
        <AppSidebar />
        <SidebarInset>
            <header class="flex h-16 shrink-0 items-center w-full justify-between gap-2 border-b px-4">
                <div class="flex items-center gap-2">
                    <SidebarTrigger class="-ml-1" />
                    <template v-if="breadcrumbItems.length > 0">
                        <Separator orientation="vertical" class="mr-2 h-4" />
                        <Breadcrumb>
                            <BreadcrumbList>
                                <template v-for="(item, index) in breadcrumbItems" :key="index">
                                    <BreadcrumbItem>
                                        <template v-if="index === breadcrumbItems.length - 1">
                                            <BreadcrumbPage>{{ item.title }}</BreadcrumbPage>
                                        </template>
                                        <template v-else>
                                            <BreadcrumbLink :href="item.href">
                                                {{ item.title }}
                                            </BreadcrumbLink>
                                        </template>
                                    </BreadcrumbItem>
                                    <BreadcrumbSeparator v-if="index !== breadcrumbItems.length - 1" />
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