<template>
    <AppLayout title="Create Team">
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">Create New Team</h2>
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
                                <label for="description" class="block text-sm font-medium">Description</label>
                                <textarea id="description" v-model="form.description" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
                            </div>
                            <button type="submit" class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700">Create Team</button>
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

const form = reactive({
    name: '',
    description: '',
});

const submit = () => {
    // Form submission logic
};
</script>
