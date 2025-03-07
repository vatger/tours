<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
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
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];
</script>

<template>
    <VNavigationDrawer app>
        <VList>
            <VListItem>
                <VListItemIcon>
                    <Link :href="route('dashboard')">
                        <AppLogo />
                    </Link>
                </VListItemIcon>
            </VListItem>
        </VList>

        <VList>
            <NavMain :items="mainNavItems" />
        </VList>

        <VList>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </VList>
    </VNavigationDrawer>
    <slot />
</template>
