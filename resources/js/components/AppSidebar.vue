<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import NavFooter from '@/components/NavFooter.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { BookOpenText, FolderGit2, LayoutDashboard } from 'lucide-vue-next';
import ApplicationLogo from './ApplicationLogo.vue';
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar';
import { ref } from 'vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        url: '/dashboard',
        icon: LayoutDashboard,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        url: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        url: 'https://laravel.com/docs/starter-kits',
        icon: BookOpenText,
    },
];

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
        <Sidebar variant="sidebar" collapsible="icon">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" as-child>
                            <a href="#" class="flex items-center gap-3">
                                <div
                                    class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
                                >
                                    <ApplicationLogo class="size-5 fill-current text-white" />
                                </div>
                                <div class="grid flex-1 text-left text-sm leading-tight">
                                    <span class="truncate font-semibold">Laravel</span>
                                    <span class="truncate text-xs">Starter Kit</span>
                                </div>
                            </a>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain :items="mainNavItems" />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter :items="footerNavItems" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
        <SidebarInset>
            <slot />
        </SidebarInset>
    </SidebarProvider>
</template>
