<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import type { BreadcrumbItem } from '@/types'
import AppLogo from '@/components/AppLogo.vue'
import AppLogoIcon from '@/components/AppLogoIcon.vue'
import { Button } from "@/components/ui/button"
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip'
import type { NavItem } from '@/types'
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTrigger,
} from '@/components/ui/sheet'
import type { SharedData } from '@/types'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Menu, ChevronDown, FolderGit2, BookOpenText, Search, LayoutGrid } from 'lucide-vue-next'
import { useInitials } from '@/composables/useInitials.ts'
import { cn } from '@/lib/utils'
import UserMenuContent from '@/components/UserMenuContent.vue'
import Icon from '@/components/Icon.vue'

interface Props {
    breadcrumbs?: BreadcrumbItem[]
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => []
})

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        url: '/dashboard',
        icon: LayoutGrid,
    },
]

const rightNavItems: NavItem[] = [
    {
        title: 'Repository',
        url: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        url: 'https://laravel.com/docs/starter-kits',
        icon: BookOpenText,
    },
]

const activeItemStyles = "bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100"

const { auth } = usePage<SharedData>().props
const getInitials = useInitials()
</script>

<template>
    <div>
        <div class="border-b border-sidebar-border/80">
            <div class="flex h-16 items-center px-4 md:max-w-7xl mx-auto">
                <!-- Mobile Menu -->
                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger :as-child="true">
                            <Button variant="ghost" size="icon" class="mr-2 w-[34px] h-[34px]">
                                <Menu class="h-5 w-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="bg-neutral-50 h-full flex flex-col items-stretch w-64">
                            <SheetHeader class="text-left flex justify-start">
                                <AppLogoIcon class="h-6 w-6 fill-current text-black dark:text-white" />
                            </SheetHeader>
                            <div class="flex flex-col h-full space-y-4 mt-6 flex-1">
                                <div class="flex flex-col text-sm h-full justify-between">
                                    <div class="flex flex-col space-y-4">
                                        <Link v-for="item in mainNavItems" 
                                              :key="item.title" 
                                              :href="item.url" 
                                              class="flex items-center space-x-2 font-medium">
                                            <Icon v-if="item.icon" :icon-node="item.icon" class="h-5 w-5" />
                                            <span>{{ item.title }}</span>
                                        </Link>
                                    </div>

                                    <div class="flex flex-col space-y-4">
                                        <a v-for="item in rightNavItems"
                                           :key="item.title"
                                           :href="item.url"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="flex items-center space-x-2 font-medium">
                                            <Icon v-if="item.icon" :icon-node="item.icon" class="h-5 w-5" />
                                            <span>{{ item.title }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>

                <Link href="/dashboard" class="flex items-center space-x-2">
                    <AppLogo />
                </Link>

                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center space-x-6 h-full ml-6">
                    <NavigationMenu class="h-full flex items-stretch">
                        <NavigationMenuList class="h-full space-x-2 flex items-stretch">
                            <NavigationMenuItem v-for="(item, index) in mainNavItems"
                                              :key="index"
                                              class="h-full flex items-center relative">
                                <Link :href="item.url">
                                    <NavigationMenuLink :class="[
                                        navigationMenuTriggerStyle(),
                                        activeItemStyles,
                                        'px-3 h-9 cursor-pointer'
                                    ]">
                                        <Icon v-if="item.icon" :icon-node="item.icon" class="mr-2 h-4 w-4" />
                                        {{ item.title }}
                                    </NavigationMenuLink>
                                </Link>
                                <div class="h-0.5 translate-y-px bg-black dark:bg-white w-full absolute bottom-0 left-0"></div>
                            </NavigationMenuItem>
                        </NavigationMenuList>
                    </NavigationMenu>
                </div>

                <div class="ml-auto flex items-center space-x-2">
                    <div class="relative flex items-center space-x-1">
                        <Button variant="ghost" size="icon" class="w-9 h-9 cursor-pointer">
                            <Search class="h-5 w-5" />
                        </Button>
                        <div class="hidden lg:flex space-x-1">
                            <TooltipProvider v-for="item in rightNavItems"
                                           :key="item.title"
                                           :delay-duration="0">
                                <Tooltip>
                                    <TooltipTrigger>
                                        <Button variant="ghost" size="icon" :as-child="true" class="w-9 h-9 cursor-pointer">
                                            <a :href="item.url" target="_blank" rel="noopener noreferrer">
                                                <span class="sr-only">{{ item.title }}</span>
                                                <Icon v-if="item.icon" :icon-node="item.icon" class="h-5 w-5" />
                                            </a>
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>{{ item.title }}</p>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </div>
                    </div>
                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <Button variant="ghost" class="h-9 px-1.5">
                                <Avatar class="h-7 w-7 overflow-hidden rounded-lg">
                                    <AvatarImage :src="auth.user.avatar" :alt="auth.user.name" />
                                    <AvatarFallback class="rounded-lg text-black dark:text-white">
                                        {{ getInitials(auth.user.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <ChevronDown class="h-4 w-4 hidden lg:block" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent class="w-56" align="end">
                            <UserMenuContent :user="auth.user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </div>
        <div v-if="props.breadcrumbs.length > 1" class="w-full flex border-b border-sidebar-border/70">
            <div class="flex h-12 items-center justify-start w-full px-4 md:max-w-7xl mx-auto text-neutral-500">
                <Breadcrumbs :breadcrumbs="props.breadcrumbs" />
            </div>
        </div>
    </div>
</template>