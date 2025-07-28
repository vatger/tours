<template>
    <AppLayout title="User Details">
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">User Details: {{ user.name }}</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- User Information -->
                            <div>
                                <h3 class="mb-4 text-lg font-medium">User Information</h3>
                                <dl class="space-y-2">
                                    <div>
                                        <dt class="text-sm font-medium">Name</dt>
                                        <dd class="text-sm text-gray-600 dark:text-gray-400">{{ user.name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium">Email</dt>
                                        <dd class="text-sm text-gray-600 dark:text-gray-400">{{ user.email }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium">Status</dt>
                                        <dd class="text-sm text-gray-600 dark:text-gray-400">
                                            <span
                                                class="rounded-full px-2 py-1 text-xs"
                                                :class="user.email_verified_at ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                            >
                                                {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Roles -->
                            <div>
                                <h3 class="mb-4 text-lg font-medium">Roles</h3>
                                <div class="space-y-2">
                                    <div
                                        v-for="role in user.roles"
                                        :key="role.id"
                                        class="mr-2 inline-block rounded-full bg-blue-100 px-3 py-1 text-sm text-blue-800"
                                    >
                                        {{ role.display_name }}
                                    </div>
                                    <div v-if="!user.roles.length" class="text-sm text-gray-500">No roles assigned</div>
                                </div>
                            </div>

                            <!-- Permissions -->
                            <div class="md:col-span-2">
                                <h3 class="mb-4 text-lg font-medium">Permissions</h3>
                                <div class="grid grid-cols-2 gap-2 md:grid-cols-3 lg:grid-cols-4">
                                    <div
                                        v-for="permission in user.permissions"
                                        :key="permission.id"
                                        class="rounded bg-gray-100 px-2 py-1 text-xs dark:bg-gray-700"
                                    >
                                        {{ permission.display_name }}
                                    </div>
                                    <div v-if="!user.permissions.length" class="col-span-full text-sm text-gray-500">No permissions assigned</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex space-x-4">
                            <a
                                :href="route('admin.users.edit', user.id)"
                                class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700"
                            >
                                Edit User
                            </a>
                            <a :href="route('admin.users.index')" class="rounded bg-gray-500 px-4 py-2 font-bold text-white hover:bg-gray-700">
                                Back to Users
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';

defineProps({
    user: Object,
});
</script>
