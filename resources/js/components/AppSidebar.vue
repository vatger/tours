<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { logout, tours } from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LogOut } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

interface Props {
    mainNavItems?: NavItem[];
}

withDefaults(defineProps<Props>(), {
    mainNavItems: () => [],
});

/*const mainNavItems: NavItem[] = [
    {
        title: 'Tours',
        href: tours(),
        icon: LayoutGrid,
    },
];*/

const footerNavItems: NavItem[] = [
    {
        title: 'GDPR',
        href: 'https://vatger.de/gdpr',
        icon: Folder,
    },
    {
        title: 'Imprint',
        href: 'https://vatger.de/imprint',
        icon: BookOpen,
    },
    {
        title: 'Logout',
        href: logout(),
        icon: LogOut,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="tours()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
