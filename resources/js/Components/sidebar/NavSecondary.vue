<script setup lang="ts">
  import type { Component } from 'vue'
  import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
  } from '@/Components/ui/sidebar'
  
  interface NavItem {
    title: string
    url: string
    icon: Component
  }
  
  defineProps<{
    items: NavItem[]
  }>()
  </script>

<template>
    <SidebarGroup v-bind="$attrs">
      <SidebarGroupContent>
        <SidebarMenu>
          <SidebarMenuItem v-for="item in items" :key="item.title">
            <SidebarMenuButton size="sm" asChild>
              <a :href="item.url">
                <component :is="item.icon" />
                <span>{{ item.title }}</span>
              </a>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarGroupContent>
    </SidebarGroup>
  </template>