<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from '@/components/ui/collapsible'

import { ChevronsUpDown } from "lucide-vue-next"
import { ref } from "vue"

import { Button } from "@/components/ui/button"

defineProps<{
    items: NavItem[];
}>();

const page = usePage();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                
                    <SidebarMenuButton
                        as-child
                        :is-active="urlIsActive(item.href, page.url)"
                        :tooltip="item.title"
                    >
                        
                        <Link :href="item.href">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>

                        <div v-if="item.dropdownItems">
                            {{ item.dropdownItems }}
                        </div>
                    </SidebarMenuButton>

                    <!-- drop down component goes here -->
            </SidebarMenuItem>

            <Collapsible v-model:open="isOpen">
                <CollapsibleTrigger>
                    <!-- Can I use this in my project? -->
                    <Button variant="ghost" size="sm" class="w-9 p-0">
                        <ChevronsUpDown class="h-4 w-4" />
                        <span class="sr-only">Toggle</span>
                    </Button>
                </CollapsibleTrigger>
                <CollapsibleContent>
                Yes. Free to use for personal and commercial projects. No attribution
                required.
                </CollapsibleContent>
            </Collapsible>

            <!-- <CollapsibleTrigger asChild>
                <SidebarMenuButton />
            </CollapsibleTrigger>

            <CollapsibleContent>
                <SidebarMenuSub>
                    <SidebarMenuSubItem />
                </SidebarMenuSub>
            </CollapsibleContent> -->

        </SidebarMenu>
    </SidebarGroup>

    <!-- <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Settings</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="urlIsActive(item.href, page.url)"
                    :tooltip="item.title"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup> -->
</template>
