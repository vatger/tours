<template>
    <AppLayout title="Edit User">
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">Edit User: {{ user.name }}</h2>
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
                                <label for="email" class="block text-sm font-medium">Email</label>
                                <input type="email" id="email" v-model="form.email" class="mt-1 block w-full rounded-md border-gray-300" />
                            </div>
                            <div class="mb-4">
                                <label for="roles" class="block text-sm font-medium">Roles</label>
                                <select multiple v-model="form.roles" class="mt-1 block w-full rounded-md border-gray-300">
                                    <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.display_name }}</option>
                                </select>
                            </div>
                            <button type="submit" class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700">Update User</button>
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

const props = defineProps({
    user: Object,
    roles: Array,
});

const form = reactive({
    name: props.user.name,
    email: props.user.email,
    roles: props.user.roles.map((role) => role.id),
});

const submit = () => {
    // Form submission logic
};
</script>
