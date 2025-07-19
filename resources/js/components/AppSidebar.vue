<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, BookOpenIcon, Folder, LayoutGrid, User2 } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { useNavFilter } from '@/composables/useNavFilter'
import { computed } from 'vue'

const developmentNavItems: NavItem[] = [

    {
        title: 'System Development',
        icon: Folder,
        permission: '',
        children: [
            {
                title: 'Models Generated',
                href: '/models-generate',
                icon: BookOpenIcon,
                permission: 'system.models.list'
            },
            {
                title: 'Settings',
                href: 'system-settings',
                icon: LayoutGrid,
                permission: 'system.settings'
            },
        ],
    },
    {
        title: 'Roles & Permissions',
        icon: Folder,
        permission: 'roles.permissions',
        children: [
            {
                title: 'Permissions',
                // href: '/login',
                icon: BookOpenIcon,
                children: [
                    {
                        title: 'Nivel 1',
                        icon: BookOpenIcon,
                        children: [
                            {
                                title: 'Create',
                                href: '/01create',
                                icon: BookOpenIcon,

                            },
                            {
                                title: 'Show',
                                href: '/01show',
                                icon: LayoutGrid,
                            },
                        ],

                    },
                    {
                        title: 'Nível 2',
                        href: '/02show',
                        icon: LayoutGrid,
                    },
                ],
            },
            {
                title: 'Roles',
                href: '/roles',
                icon: LayoutGrid,
            },
        ],
    },
];


const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Gestão',
        icon: Folder,
        children: [
            {
                title: 'Logs',
                icon: BookOpen,
                children: [
                    {
                        title: 'Activities',
                        href: '/activityLogs',
                        icon: BookOpen,
                        permission: 'activityLogs.list',

                    },

                ],
            },
            {
                title: 'Parameters',
                icon: BookOpen,
                children: [
                    {
                        title: 'Genders',
                        href: '/genders',
                        icon: BookOpen,
                        permission: 'genders.list',

                    },

                ],
            },
            {
                title: 'Usuários',
                href: '/users',
                icon: User2,
                permission: 'users.list',
            },

        ],
    },
];


const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];

const { filterNavItems } = useNavFilter()
const filteredDevelopmentNavItems = computed(() => filterNavItems(developmentNavItems))
const filteredMainNavItems = computed(() => filterNavItems(mainNavItems))
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                        <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain v-if="filteredDevelopmentNavItems.length > 0" :items="filteredDevelopmentNavItems"
                label="System Development" />

            <NavMain :items="filteredMainNavItems" label="Plataforma" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
