<script setup lang="ts">
import { LayoutDashboard, type LucideIcon } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import {
    SidebarGroup,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/Components/ui/sidebar';

interface NavItem {
    title: string;
    url: string;
    icon: any; // Using any for now since Vue's type system handles components differently
    isActive?: boolean;
}

interface Props {
    items?: NavItem[];
}

withDefaults(defineProps<Props>(), {
    items: () => [],
});
</script>

<template>
    <SidebarGroup>
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton as-child :is-active="true">
                    <Link :href="route('dashboard')">
                        <LayoutDashboard />
                        <span>Dashboard</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton as-child :is-active="item.isActive">
                    <Link :href="item.url">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
