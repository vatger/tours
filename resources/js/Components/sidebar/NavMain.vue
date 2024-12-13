<script setup lang="ts">
  import { ChevronRight } from 'lucide-vue-next'
  import type { Component } from 'vue'
  import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
  } from '@/components/ui/collapsible'
  import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuAction,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
  } from '@/components/ui/sidebar'
  
  interface SubItem {
    title: string
    url: string
  }
  
  interface NavItem {
    title: string
    url: string
    icon: Component
    isActive?: boolean
    items?: SubItem[]
  }
  
  defineProps<{
    items: NavItem[]
  }>()
  </script>

<template>
    <SidebarGroup>
      <SidebarGroupLabel>Platform</SidebarGroupLabel>
      <SidebarMenu>
        <Collapsible
          v-for="item in items"
          :key="item.title"
          :default-open="item.isActive"
          asChild
        >
          <SidebarMenuItem>
            <SidebarMenuButton asChild :tooltip="item.title">
              <a :href="item.url">
                <component :is="item.icon" />
                <span>{{ item.title }}</span>
              </a>
            </SidebarMenuButton>
            <template v-if="item.items?.length">
              <CollapsibleTrigger asChild>
                <SidebarMenuAction class="data-[state=open]:rotate-90">
                  <ChevronRight />
                  <span class="sr-only">Toggle</span>
                </SidebarMenuAction>
              </CollapsibleTrigger>
              <CollapsibleContent>
                <SidebarMenuSub>
                  <SidebarMenuSubItem
                    v-for="subItem in item.items"
                    :key="subItem.title"
                  >
                    <SidebarMenuSubButton asChild>
                      <a :href="subItem.url">
                        <span>{{ subItem.title }}</span>
                      </a>
                    </SidebarMenuSubButton>
                  </SidebarMenuSubItem>
                </SidebarMenuSub>
              </CollapsibleContent>
            </template>
          </SidebarMenuItem>
        </Collapsible>
      </SidebarMenu>
    </SidebarGroup>
  </template>
