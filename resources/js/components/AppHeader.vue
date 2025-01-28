<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import type { BreadcrumbItem, NavItem } from '@/types'
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
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTrigger,
} from '@/components/ui/sheet'
import { Search } from 'lucide-vue-next'
import UserMenuContent from '@/components/UserMenuContent.vue'
import Icon from '@/components/Icon.vue'
import { getInitials } from '@/composables/useInitials'
import { computed } from 'vue'

interface Props {
    breadcrumbs?: BreadcrumbItem[]
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => []
})

const page = usePage()
const auth = computed(() => page.props.auth)

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        isActive: true
    },
    {
        title: 'Settings',
        href: '/settings',
        isActive: false
    }
]

const rightNavItems: NavItem[] = [
    {
        title: 'Documentation',
        href: '/docs',
        isActive: false
    }
]

const activeItemStyles = (isActive: boolean) => {
    return isActive ? 'text-primary font-medium' : 'text-muted-foreground'
}
</script>

<template>
    <div>
        <div class="border-b border-sidebar-border/80">
            <div class="flex h-16 items-center px-4 md:max-w-7xl mx-auto">
                <!-- Mobile Menu -->
                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger :as-child="true">
                            <Button variant="ghost" size="icon" class="w-9 h-9">
                                <Icon name="menu" class="h-5 w-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-[300px] p-0 pt-12">
                            <SheetHeader class="px-6 pb-6">
                                <AppLogo class="h-6" />
                            </SheetHeader>
                            <nav class="space-y-1 px-3">
                                <Link
                                    v-for="item in mainNavItems"
                                    :key="item.title"
                                    :href="item.href"
                                    class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                    :class="activeItemStyles(item.isActive)"
                                >
                                    <Icon v-if="item.icon" :name="item.icon" class="h-5 w-5" />
                                    {{ item.title }}
                                </Link>
                            </nav>
                        </SheetContent>
                    </Sheet>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden lg:flex lg:flex-1">
                    <Link href="/" class="flex items-center gap-x-2">
                        <AppLogoIcon class="h-8 w-8" />
                        <AppLogo class="hidden h-6 xl:block" />
                    </Link>

                    <NavigationMenu class="ml-10">
                        <NavigationMenuList>
                            <NavigationMenuItem v-for="item in mainNavItems" :key="item.title">
                                <NavigationMenuLink
                                    :href="item.href"
                                    :class="[navigationMenuTriggerStyle(), activeItemStyles(item.isActive)]"
                                >
                                    {{ item.title }}
                                </NavigationMenuLink>
                            </NavigationMenuItem>
                        </NavigationMenuList>
                    </NavigationMenu>
                </div>

                <div class="ml-auto flex items-center space-x-2">
                    <div class="relative flex items-center space-x-1">
                        <Button variant="ghost" size="icon" class="w-9 h-9 cursor-pointer">
                            <Search class="h-5 w-5" />
                        </Button>

                        <NavigationMenu>
                            <NavigationMenuList>
                                <NavigationMenuItem v-for="item in rightNavItems" :key="item.title">
                                    <NavigationMenuLink
                                        :href="item.href"
                                        :class="[navigationMenuTriggerStyle(), activeItemStyles(item.isActive)]"
                                    >
                                        {{ item.title }}
                                    </NavigationMenuLink>
                                </NavigationMenuItem>
                            </NavigationMenuList>
                        </NavigationMenu>
                    </div>

                    <DropdownMenu>
                        <TooltipProvider>
                            <Tooltip>
                                <TooltipTrigger>
                                    <DropdownMenuTrigger :as-child="true">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="relative h-9 w-9 rounded-full"
                                        >
                                            <img
                                                v-if="auth.user?.avatar"
                                                :src="auth.user.avatar"
                                                :alt="auth.user.name"
                                                class="rounded-full"
                                            />
                                            <span v-else class="flex h-9 w-9 items-center justify-center rounded-full bg-primary/10 text-sm font-medium text-primary">
                                                {{ getInitials(auth.user?.name) }}
                                            </span>
                                        </Button>
                                    </DropdownMenuTrigger>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{{ auth.user?.name }}</p>
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                        <DropdownMenuContent align="end" class="w-56">
                            <UserMenuContent />
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