<template>
    <AppLayout title="Role Details">
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">Role Details: {{ role.display_name }}</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <h3 class="mb-4 text-lg font-medium">Role Information</h3>
                                <dl class="space-y-2">
                                    <div>
                                        <dt class="text-sm font-medium">Name</dt>
                                        <dd class="text-sm text-gray-600 dark:text-gray-400">{{ role.name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium">Display Name</dt>
                                        <dd class="text-sm text-gray-600 dark:text-gray-400">{{ role.display_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium">Slug</dt>
                                        <dd class="text-sm text-gray-600 dark:text-gray-400">{{ role.slug }}</dd>
                                    </div>
                                    <div v-if="role.description">
                                        <dt class="text-sm font-medium">Description</dt>
                                        <dd class="text-sm text-gray-600 dark:text-gray-400">{{ role.description }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="mb-4 text-lg font-medium">Permissions</h3>
                                <div class="space-y-2">
                                    <div
                                        v-for="permission in role.permissions"
                                        :key="permission.id"
                                        class="mr-2 mb-2 inline-block rounded bg-gray-100 px-2 py-1 text-xs dark:bg-gray-700"
                                    >
                                        {{ permission.display_name }}
                                    </div>
                                    <div v-if="!role.permissions.length" class="text-sm text-gray-500">No permissions assigned</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex space-x-4">
                            <a :href="`/admin/roles/${role.id}/edit`" class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700">
                                Edit Role
                            </a>
                            <a href="/admin/roles" class="rounded bg-gray-500 px-4 py-2 font-bold text-white hover:bg-gray-700"> Back to Roles </a>
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
    role: Object,
});
</script>
