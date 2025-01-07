<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { Button } from "@/components/ui/button"
import Heading from "@/components/Heading.vue"
import { cn } from "@/lib/utils"
import { Separator } from "@/components/ui/separator"

interface NavItem {
    title: string
    href: string
}

const sidebarNavItems: NavItem[] = [
    {
        title: "Profile",
        href: "/settings/profile",
    },
    {
        title: "Password",
        href: "/settings/password",
    },
    {
        title: "Appearance",
        href: "/settings/appearance"
    }
]

const currentPath = window.location.pathname
const currentItem = sidebarNavItems.find(item => currentPath === item.href)
</script>

<template>
    <div class="p-5 sm:p-8 md:p-10">
        <Heading 
            title="Settings"
            description="Manage your profile and account settings"
        />

        <div class="flex flex-col space-y-8 md:flex-row md:space-x-12 md:space-y-0">
            <aside class="md:w-1/3 lg:w-1/4 xl:w-1/5 w-full">
                <nav class="flex-col space-x-0 space-y-1 flex">
                    <Button 
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                        variant="ghost" 
                        :class="[
                            'w-full justify-center justify-start',
                            currentPath === item.href ? 'bg-muted' : 'hover:underline'
                        ]"
                        as-child
                    > 
                        <Link :href="item.href">
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="md:hidden my-6" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
