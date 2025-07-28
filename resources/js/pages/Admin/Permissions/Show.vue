<template>
    <AppLayout title="Permission Details">
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">Permission Details: {{ permission.display_name }}</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <h3 class="mb-4 text-lg font-medium">Permission Information</h3>
                                <dl class="space-y-2">
                                    <div>
                                        <dt class="text-sm font-medium">Name</dt>
                                        <dd class="text-sm text-gray-600 dark:text-gray-400">{{ permission.name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium">Display Name</dt>
                                        <dd class="text-sm text-gray-600 dark:text-gray-400">{{ permission.display_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium">Slug</dt>
                                        <dd class="text-sm text-gray-600 dark:text-gray-400">{{ permission.slug }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium">Group</dt>
                                        <dd class="text-sm text-gray-600 dark:text-gray-400">{{ permission.group }}</dd>
                                    </div>
                                    <div v-if="permission.description">
                                        <dt class="text-sm font-medium">Description</dt>
                                        <dd class="text-sm text-gray-600 dark:text-gray-400">{{ permission.description }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="mb-4 text-lg font-medium">Roles with this Permission</h3>
                                <div class="space-y-2">
                                    <div
                                        v-for="role in permission.roles"
                                        :key="role.id"
                                        class="mr-2 mb-2 inline-block rounded-full bg-blue-100 px-3 py-1 text-sm text-blue-800"
                                    >
                                        {{ role.display_name }}
                                    </div>
                                    <div v-if="!permission.roles.length" class="text-sm text-gray-500">No roles have this permission</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <a href="/admin/permissions" class="rounded bg-gray-500 px-4 py-2 font-bold text-white hover:bg-gray-700">
                                Back to Permissions
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
    permission: Object,
});
</script>
