<script setup lang="ts">
  import {
    Folder,
    MoreHorizontal,
    Share,
    Trash2,
  } from 'lucide-vue-next'
  import type { Component } from 'vue'
  import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
  } from '@/Components/ui/dropdown-menu'
  import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuAction,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
  } from '@/Components/ui/sidebar'
  
  interface Project {
    name: string
    url: string
    icon: Component
  }
  
  defineProps<{
    projects: Project[]
  }>()
  
  const { isMobile } = useSidebar()
  </script>

<template>
    <SidebarGroup class="group-data-[collapsible=icon]:hidden">
      <SidebarGroupLabel>Projects</SidebarGroupLabel>
      <SidebarMenu>
        <SidebarMenuItem v-for="item in projects" :key="item.name">
          <SidebarMenuButton asChild>
            <a :href="item.url">
              <component :is="item.icon" />
              <span>{{ item.name }}</span>
            </a>
          </SidebarMenuButton>
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <SidebarMenuAction show-on-hover>
                <MoreHorizontal />
                <span class="sr-only">More</span>
              </SidebarMenuAction>
            </DropdownMenuTrigger>
            <DropdownMenuContent
              class="w-48"
              :side="isMobile ? 'bottom' : 'right'"
              :align="isMobile ? 'end' : 'start'"
            >
              <DropdownMenuItem>
                <Folder class="text-muted-foreground" />
                <span>View Project</span>
              </DropdownMenuItem>
              <DropdownMenuItem>
                <Share class="text-muted-foreground" />
                <span>Share Project</span>
              </DropdownMenuItem>
              <DropdownMenuSeparator />
              <DropdownMenuItem>
                <Trash2 class="text-muted-foreground" />
                <span>Delete Project</span>
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </SidebarMenuItem>
        <SidebarMenuItem>
          <SidebarMenuButton>
            <MoreHorizontal />
            <span>More</span>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarGroup>
  </template>
  
  