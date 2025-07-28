<template>
    <AppLayout title="Team Details">
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">Team Details: {{ team.name }}</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <h3 class="mb-4 text-lg font-medium">Team Information</h3>
                                <dl class="space-y-2">
                                    <div>
                                        <dt class="text-sm font-medium">Name</dt>
                                        <dd class="text-sm text-gray-600 dark:text-gray-400">{{ team.name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium">Slug</dt>
                                        <dd class="text-sm text-gray-600 dark:text-gray-400">{{ team.slug }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium">Owner</dt>
                                        <dd class="text-sm text-gray-600 dark:text-gray-400">{{ team.owner.name }}</dd>
                                    </div>
                                    <div v-if="team.description">
                                        <dt class="text-sm font-medium">Description</dt>
                                        <dd class="text-sm text-gray-600 dark:text-gray-400">{{ team.description }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="mb-4 text-lg font-medium">Members</h3>
                                <div class="space-y-2">
                                    <div v-for="member in team.members" :key="member.id" class="flex items-center justify-between rounded border p-2">
                                        <div>
                                            <div class="text-sm font-medium">{{ member.user.name }}</div>
                                            <div class="text-xs text-gray-500">{{ member.user.email }}</div>
                                        </div>
                                        <div class="text-xs">
                                            <span
                                                v-for="role in member.roles"
                                                :key="role.id"
                                                class="mr-1 rounded bg-blue-100 px-2 py-1 text-blue-800"
                                            >
                                                {{ role.display_name }}
                                            </span>
                                        </div>
                                    </div>
                                    <div v-if="!team.members.length" class="text-sm text-gray-500">No members</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex space-x-4">
                            <a :href="`/admin/teams/${team.id}/edit`" class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700">
                                Edit Team
                            </a>
                            <a href="/admin/teams" class="rounded bg-gray-500 px-4 py-2 font-bold text-white hover:bg-gray-700"> Back to Teams </a>
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
    team: Object,
});
</script>
