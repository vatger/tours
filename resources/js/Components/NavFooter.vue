<script setup lang="ts">
import { type LucideIcon, ExternalLink } from 'lucide-vue-next';
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuAction
} from '@/components/ui/sidebar';

interface NavItem {
    title: string;
    url: string;
    icon: any; // Using any for now since Vue's type system handles components differently
}

interface Props {
    items: NavItem[];
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <SidebarGroup :class="`group-data-[collapsible=icon]:p-0 ${$props.class || ''}`">
        <SidebarGroupContent>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in items" :key="item.title">
                    <SidebarMenuButton as-child>
                        <a 
                            :href="item.url" 
                            target="_blank" 
                            rel="noopener noreferrer"
                        >
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </a>
                    </SidebarMenuButton>
                    <SidebarMenuAction show-on-hover>
                        <ExternalLink />
                    </SidebarMenuAction>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
</template>
