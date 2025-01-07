<script setup lang="ts">
import { Head } from '@inertiajs/vue3'

import AppearanceTabs from '@/components/AppearanceTabs.vue'
import { type BreadcrumbItemType } from '@/types'
import SettingsHeading from "@/components/settings/Heading.vue"
import { Label } from "@/components/ui/label"

import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from './Layout.vue'

const breadcrumbItems: BreadcrumbItemType[] = [
    {
        title: 'Appearance Settings',
        href: '/settings/appearance'
    }
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Appearance Settings" />

        <SettingsLayout>   
            <div>
                <SettingsHeading 
                    title="Appearance Settings"
                    description="Update your account's appearance settings"
                />
                <div class="flex flex-col gap-4 items-start">
                    <Label>Appearance</Label>
                    <AppearanceTabs />
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
