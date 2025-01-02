<script setup lang="ts">
import { Github, BookOpenText } from 'lucide-vue-next';
import NavMain from '@/Components/NavMain.vue';
import NavFooter from '@/Components/NavFooter.vue';
import NavUser from '@/Components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import ApplicationLogo from './ApplicationLogo.vue';

interface NavItem {
    title: string;
    url: string;
    icon: any; // Using any for now since Vue's type system handles components differently
}

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        url: 'https://github.com/laravel/vue-starter-kit',
        icon: Github,
    },
    {
        title: 'Documentation',
        url: 'https://laravel.com/docs/starter-kits',
        icon: BookOpenText,
    },
];
</script>

<template>
    <Sidebar variant="sidebar" collapsible="icon">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <a href="#" class="flex items-center gap-3">
                            <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                                <ApplicationLogo class="size-5 text-white fill-current" />
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
            <NavMain />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" class="mt-auto" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>
