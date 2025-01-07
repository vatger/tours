<script setup lang="ts">
const props = defineProps(['name'])
  import {
    BookOpen,
    Bot,
    Command,
    Frame,
    LifeBuoy,
    Map,
    PieChart,
    Send,
    Settings2,
    SquareTerminal,
  } from 'lucide-vue-next'
  
  import ApplicationLogo from '@/components/ApplicationLogo.vue';
  import NavMain from '@/components/sidebar/NavMain.vue'
  import NavProjects from '@/components/sidebar/NavProjects.vue'
  import NavSecondary from '@/components/sidebar/NavSecondary.vue'
  import NavUser from '@/components/sidebar/NavUser.vue'
  import { type NavItemType } from '@/types'
  import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
  } from '@/components/ui/sidebar'
  
  interface Project {
    name: string
    url: string
    icon: any
  }
  
  interface User {
    name: string
    email: string
    avatar: string
  }
  
  const data = {
    user: {
      name: 'shadcn',
      email: 'm@example.com',
      avatar: '/avatars/shadcn.jpg',
    } as User,
    navMain: [
      {
        title: 'Playground',
        url: '#',
        icon: SquareTerminal,
        isActive: false,
        items: [
          {
            title: 'History',
            url: '#',
          },
          {
            title: 'Starred',
            url: '#',
          },
          {
            title: 'Settings',
            url: '#',
          },
        ],
      },
      {
        title: 'Models',
        url: '#',
        icon: Bot,
        items: [
          {
            title: 'Genesis',
            url: '#',
          },
          {
            title: 'Explorer',
            url: '#',
          },
          {
            title: 'Quantum',
            url: '#',
          },
        ],
      },
      {
        title: 'Documentation',
        url: '#',
        icon: BookOpen,
        items: [
          {
            title: 'Introduction',
            url: '#',
          },
          {
            title: 'Get Started',
            url: '#',
          },
          {
            title: 'Tutorials',
            url: '#',
          },
          {
            title: 'Changelog',
            url: '#',
          },
        ],
      },
      {
        title: 'Settings',
        url: '#',
        icon: Settings2,
        items: [
          {
            title: 'General',
            url: '#',
          },
          {
            title: 'Team',
            url: '#',
          },
          {
            title: 'Billing',
            url: '#',
          },
          {
            title: 'Limits',
            url: '#',
          },
        ],
      },
    ] as NavItemType[],
    navSecondary: [
      {
        title: 'Support',
        url: '#',
        icon: LifeBuoy,
      },
      {
        title: 'Feedback',
        url: '#',
        icon: Send,
      },
    ] as NavItemType[],
    projects: [
      {
        name: 'Design Engineering',
        url: '#',
        icon: Frame,
      },
      {
        name: 'Sales & Marketing',
        url: '#',
        icon: PieChart,
      },
      {
        name: 'Travel',
        url: '#',
        icon: Map,
      },
    ] as Project[],
  }
  </script>

<template>
    <Sidebar variant="inset" v-bind="$attrs">
      <SidebarHeader>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton size="lg" asChild>
              <a href="#">
                <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                  <ApplicationLogo class="text-white size-5" />
                </div>
                <div class="grid flex-1 text-left text-sm leading-tight">
                  <span class="truncate font-semibold">{{ props.name }}</span>
                  <span class="truncate text-xs">Starter Kit</span>
                </div>
              </a>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>
      <SidebarContent>
        <NavMain :items="data.navMain" />
        <NavProjects :projects="data.projects" />
        <NavSecondary :items="data.navSecondary" class="mt-auto" />
      </SidebarContent>
      <SidebarFooter>
        <NavUser :user="data.user" />
      </SidebarFooter>
    </Sidebar>
  </template>
  