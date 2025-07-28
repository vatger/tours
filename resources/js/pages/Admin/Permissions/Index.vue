<template>
    <AppLayout title="Permissions">
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">Permissions Management</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div v-for="group in grouped_permissions" :key="group.name" class="mb-8">
                            <h3 class="mb-4 text-lg font-medium">{{ group.name }}</h3>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                <div v-for="permission in group.permissions" :key="permission.id" class="rounded-lg border p-4">
                                    <h4 class="font-medium">{{ permission.display_name }}</h4>
                                    <p class="text-sm text-gray-600">{{ permission.name }}</p>
                                    <p class="text-xs text-gray-500">{{ permission.description }}</p>
                                </div>
                            </div>
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
    grouped_permissions: Object,
});
</script>
