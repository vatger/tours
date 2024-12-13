<script setup lang="ts">
const props = defineProps(['name'])
import AppSidebar from '@/Components/sidebar/SideBar.vue'
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/components/ui/breadcrumb'
import { Separator } from '@/components/ui/separator'
import {
  SidebarInset,
  SidebarProvider,
  SidebarTrigger,
} from '@/components/ui/sidebar'
</script>

<template>
    <div class="bg-neutral-100">
        <SidebarProvider>
            <AppSidebar :name="name" />
            <SidebarInset>
                <header class="flex h-16 shrink-0 items-center gap-2">
                <div class="flex items-center gap-2 px-4">
                    <SidebarTrigger class="-ml-1" />
                    <Separator orientation="vertical" class="mr-2 h-4" />
                    <Breadcrumb>
                    <BreadcrumbList>
                        <BreadcrumbItem class="hidden md:block">
                        <BreadcrumbLink href="#">
                            Dashboard
                        </BreadcrumbLink>
                        </BreadcrumbItem>
                        <BreadcrumbSeparator class="hidden md:block" />
                        <BreadcrumbItem>
                        <BreadcrumbPage>Learn</BreadcrumbPage>
                        </BreadcrumbItem>
                    </BreadcrumbList>
                    </Breadcrumb>
                </div>
                </header>
                <div class="flex flex-1 flex-col gap-4 p-4 pt-0">
                    <slot />
                </div>
            </SidebarInset>
        </SidebarProvider>
    </div>
  </template>