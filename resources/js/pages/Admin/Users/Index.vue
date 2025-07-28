<template>
    <AppLayout>
        <Head title="Users Management" />

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 bg-white p-6">
                        <div class="mb-6 flex items-center justify-between">
                            <h2 class="text-2xl font-semibold text-gray-900">Users Management</h2>
                            <Link
                                :href="route('admin.users.create')"
                                class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-blue-700 focus:bg-blue-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-blue-900"
                            >
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Add User
                            </Link>
                        </div>

                        <!-- Search and Filters -->
                        <div class="mb-6 flex flex-col gap-4 sm:flex-row">
                            <div class="flex-1">
                                <input
                                    v-model="searchForm.search"
                                    type="text"
                                    placeholder="Search users..."
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
                                    @input="debounceSearch"
                                />
                            </div>
                            <div class="sm:w-48">
                                <select
                                    v-model="searchForm.role"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
                                    @change="search"
                                >
                                    <option value="">All Roles</option>
                                    <option v-for="role in availableRoles" :key="role.id" :value="role.slug">
                                        {{ role.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Users Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">User</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Roles</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Created</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 flex-shrink-0">
                                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-300">
                                                        <span class="text-sm font-medium text-gray-700">
                                                            {{ user.name.charAt(0).toUpperCase() }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                                                    <div class="text-sm text-gray-500">{{ user.email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-wrap gap-1">
                                                <span
                                                    v-for="role in user.roles"
                                                    :key="role.id"
                                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                                    :class="getRoleClass(role.slug)"
                                                >
                                                    {{ role.name }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                                            {{ formatDate(user.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                            <div class="flex items-center space-x-2">
                                                <Link :href="route('admin.users.show', user.id)" class="text-indigo-600 hover:text-indigo-900">
                                                    View
                                                </Link>
                                                <Link :href="route('admin.users.edit', user.id)" class="text-green-600 hover:text-green-900">
                                                    Edit
                                                </Link>
                                                <button
                                                    v-if="!user.is_superadmin && user.id !== $page.props.auth.user.id"
                                                    @click="confirmDelete(user)"
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="users.links" class="mt-6">
                            <nav class="flex items-center justify-between">
                                <div class="hidden sm:block">
                                    <p class="text-sm text-gray-700">Showing {{ users.from }} to {{ users.to }} of {{ users.total }} results</p>
                                </div>
                                <div class="flex flex-1 justify-between sm:justify-end">
                                    <Link
                                        v-if="users.prev_page_url"
                                        :href="users.prev_page_url"
                                        class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                    >
                                        Previous
                                    </Link>
                                    <Link
                                        v-if="users.next_page_url"
                                        :href="users.next_page_url"
                                        class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                    >
                                        Next
                                    </Link>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal
            :show="showDeleteModal"
            @close="showDeleteModal = false"
            @confirm="deleteUser"
            title="Delete User"
            :message="deleteMessage"
            confirm-text="Delete"
            confirm-class="bg-red-600 hover:bg-red-700 focus:ring-red-500"
        />
    </AppLayout>
</template>

<script setup>
import ConfirmationModal from '@/components/ConfirmationModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { computed, reactive, ref } from 'vue';

const props = defineProps({
    users: Object,
    availableRoles: Array,
    filters: Object,
});

const showDeleteModal = ref(false);
const userToDelete = ref(null);

const searchForm = reactive({
    search: props.filters?.search || '',
    role: props.filters?.role || '',
});

const deleteMessage = computed(() => {
    return userToDelete.value
        ? `Are you sure you want to delete ${userToDelete.value.name}? This action cannot be undone.`
        : 'Are you sure you want to delete this user?';
});

const debounceSearch = debounce(() => {
    search();
}, 300);

const search = () => {
    router.get(route('admin.users.index'), searchForm, {
        preserveState: true,
        preserveScroll: true,
    });
};

const getRoleClass = (roleSlug) => {
    const classes = {
        superadmin: 'bg-purple-100 text-purple-800',
        admin: 'bg-red-100 text-red-800',
        editor: 'bg-blue-100 text-blue-800',
        author: 'bg-green-100 text-green-800',
        user: 'bg-gray-100 text-gray-800',
        customer: 'bg-yellow-100 text-yellow-800',
        deliveryboy: 'bg-orange-100 text-orange-800',
    };
    return classes[roleSlug] || 'bg-gray-100 text-gray-800';
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const confirmDelete = (user) => {
    userToDelete.value = user;
    showDeleteModal.value = true;
};

const deleteUser = () => {
    if (userToDelete.value) {
        router.delete(route('admin.users.destroy', userToDelete.value.id), {
            onSuccess: () => {
                showDeleteModal.value = false;
                userToDelete.value = null;
            },
        });
    }
};
</script>
