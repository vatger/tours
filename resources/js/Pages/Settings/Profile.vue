<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import { TransitionRoot } from '@headlessui/vue'

import DeleteUser from '@/Components/Settings/DeleteUser.vue'
import InputError from '@/Components/InputError.vue'
import { Button } from "@/Components/ui/button"
import { Input } from "@/Components/ui/input"
import { Label } from "@/Components/ui/label"
import AppLayout from '@/Layouts/AppLayout.vue'
import SettingsLayout from './Layout.vue'
import { Separator } from "@/Components/ui/separator"
import SettingsHeading from "@/Components/Settings/Heading.vue"

interface Props {
    mustVerifyEmail: boolean
    status?: string
    className?: string
}

defineProps<Props>()

interface BreadcrumbItem {
    title: string
    href: string
}

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile Settings',
        href: '/settings/profile'
    }
]

const page = usePage()
const user = page.props.auth.user

const form = useForm({
    name: user.name,
    email: user.email,
})

const submit = () => {
    form.patch(route('profile.update'))
}
</script>

<template>
    <AppLayout :breadcrumb-items="breadcrumbItems">
        <Head title="Profile Settings" />

        <SettingsLayout>
            <div class="flex flex-col">
                <SettingsHeading 
                    title="Profile Information"
                    description="Update your name and email address"
                />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>

                        <Input
                            id="name"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            required
                            autocomplete="name"
                        />

                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email Address</Label>

                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            autocomplete="username"
                        />

                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="mt-2 text-sm text-gray-800">
                            Your email address is unverified.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                Click here to re-send the verification email.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Save</Button>

                        <TransitionRoot
                            :show="form.recentlySuccessful"
                            enter="transition ease-in-out"
                            enter-from="opacity-0"
                            leave="transition ease-in-out"
                            leave-to="opacity-0"
                        >
                            <p class="text-sm text-gray-600">
                                Saved.
                            </p>
                        </TransitionRoot>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
