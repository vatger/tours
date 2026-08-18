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

defineProps<{
  items: NavItem[];
}>();

const page = usePage();
</script>

<template>
  <SidebarGroup class="px-2 py-0">
    <SidebarGroupLabel>Tours</SidebarGroupLabel>
    <SidebarMenu>
      <SidebarMenuItem v-for="item in items" :key="item.title">
        <SidebarMenuButton as-child :is-active="urlIsActive(item.href, page.url)" :tooltip="item.title">
          <Link :href="item.href">
            <component :is="item.icon" />
            <span class="flex items-center gap-2">
              {{ item.title }}
              <span v-if="item.isLive" class="relative flex size-2" aria-label="Live flight active" role="status">
                <span class="absolute inline-flex size-full animate-ping rounded-full bg-green-500 opacity-75"></span>
                <span class="relative inline-flex size-2 rounded-full bg-green-500"></span>
              </span>
            </span>
          </Link>
        </SidebarMenuButton>
      </SidebarMenuItem>
    </SidebarMenu>
  </SidebarGroup>
</template>
