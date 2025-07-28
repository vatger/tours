<template>
    <AppLayout title="Create Role">
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">Create New Role</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium">Name</label>
                                <input type="text" id="name" v-model="form.name" class="mt-1 block w-full rounded-md border-gray-300" />
                            </div>
                            <div class="mb-4">
                                <label for="display_name" class="block text-sm font-medium">Display Name</label>
                                <input
                                    type="text"
                                    id="display_name"
                                    v-model="form.display_name"
                                    class="mt-1 block w-full rounded-md border-gray-300"
                                />
                            </div>
                            <div class="mb-4">
                                <label for="description" class="block text-sm font-medium">Description</label>
                                <textarea id="description" v-model="form.description" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="mb-2 block text-sm font-medium">Permissions</label>
                                <div v-for="group in permissions" :key="group.group" class="mb-4">
                                    <h4 class="mb-2 font-medium">{{ group.group }}</h4>
                                    <div class="space-y-2">
                                        <label v-for="permission in group.permissions" :key="permission.id" class="flex items-center">
                                            <input type="checkbox" :value="permission.id" v-model="form.permissions" class="mr-2" />
                                            {{ permission.display_name }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700">Create Role</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { reactive } from 'vue';

defineProps({
    permissions: Array,
});

const form = reactive({
    name: '',
    display_name: '',
    description: '',
    permissions: [],
});

const submit = () => {
    // Form submission logic
};
</script>
