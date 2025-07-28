<template>
    <AppLayout title="Roles">
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">Roles Management</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-4">
                            <a href="/admin/roles/create" class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700">
                                Create New Role
                            </a>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Slug</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Permissions</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="role in roles.data" :key="role.id">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ role.display_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ role.slug }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ role.permissions_count }} permissions</td>
                                        <td class="space-x-2 px-6 py-4 whitespace-nowrap">
                                            <a :href="`/admin/roles/${role.id}`" class="text-blue-600 hover:text-blue-900">View</a>
                                            <a :href="`/admin/roles/${role.id}/edit`" class="text-green-600 hover:text-green-900">Edit</a>
                                            <button v-if="!role.is_system" class="text-red-600 hover:text-red-900">Delete</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
    roles: Object,
});
</script>
