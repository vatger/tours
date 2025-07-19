<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import UsersTable from './partials/UsersTable.vue'
import UserForm from './partials/UserForm.vue'
import UserView from './partials/UserView.vue'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { useUsers } from '@/composables/useUsers'
import { computed, ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { debounce } from 'lodash-es'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { PlusIcon } from 'lucide-vue-next'
import Heading from '@/components/Heading.vue'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'
import { useAuth } from '@/composables/useAuth'

const { hasPermission, hasSomePermissions } = useAuth()
const breadcrumbs = computed(() => [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Users', href: route('users.index') }
])

const { users, filters } = defineProps<{
    users: any
    filters: {
        search?: string
        sort?: string | null  // Will be in format '-name,email' etc.
        per_page?: number,
        only_trashed: boolean
    }
}>()

const { isFormOpen, isViewOpen, currentUser, formAction, openForm, openView } = useUsers()
const searchQuery = ref(filters.search || '')
const perPage = ref(filters.per_page || 10)
const onlyTrashed = ref(filters.only_trashed || false)
// Debounced search
watch(searchQuery, debounce((value) => {
    router.get(route('users.index'), {
        filter: {
            Search: value, // Spatie expects filter[Search]
            ...(onlyTrashed.value ? { OnlyTrashed: true } : {})
        },
        sort: filters.sort, // Keep current sort
        per_page: perPage.value
    }, {
        preserveState: true,
        replace: true
    })
}, 300))


// Immediate per page change
watch(perPage, (value) => {
    router.get(route('users.index'), {
        filter: {
            Search: searchQuery.value,
            ...(onlyTrashed.value ? { OnlyTrashed: true } : {})
        },
        sort: filters.sort, // Keep current sort
        per_page: value
    }, {
        preserveState: true,
        replace: true
    })
})
watch(onlyTrashed, (value) => {
    router.get(route('users.index'), {
        filter: {
            Search: searchQuery.value,
            ...(value ? { OnlyTrashed: true } : {})
        },
        sort: filters.sort,
        per_page: perPage.value
    }, {
        preserveState: true,
        replace: true
    })
})
const isDeleteModalOpen = ref(false)
const userToDelete = ref<any | null>(null)

// Abrir modal de confirmação
const openDeleteModal = (user: any) => {
    userToDelete.value = user
    isDeleteModalOpen.value = true
}

// Executar exclusão
const deleteUser = () => {
    if (userToDelete.value) {
        router.delete(route('users.destroy', { user: userToDelete.value.id }), {
            onSuccess: () => {
                isDeleteModalOpen.value = false
                userToDelete.value = null
            }
        })
    }
}




// Abrir modal de confirmação de RESTORE exclusão
const isRestoreDeleteModalOpen = ref(false)
const userToRestoreDelete = ref<any | null>(null)
const openRestoreDeleteModal = (user: any) => {
    userToRestoreDelete.value = user
    isRestoreDeleteModalOpen.value = true
}

// Executar RESTORE exclusão
const restoreDeleteUser = () => {
    if (userToRestoreDelete.value) {
        router.put(route('users.trashed.restore', { user_id: userToRestoreDelete.value.id }), {

        })
    }
    isRestoreDeleteModalOpen.value = false
    userToRestoreDelete.value = null

}



// Abrir modal de confirmação de exclusão PERMANENTE
const isForceDeleteModalOpen = ref(false)
const userToForceDelete = ref<any | null>(null)
const openForceDeleteModal = (user: any) => {
    userToForceDelete.value = user
    isForceDeleteModalOpen.value = true
}

// Executar exclusão PERMANENTE
const forceDeleteUser = () => {
    if (userToForceDelete.value) {
        router.delete(route('users.trashed.destroy', { user_id: userToForceDelete.value.id }), {
            onSuccess: () => {
                isForceDeleteModalOpen.value = false
                userToForceDelete.value = null
            }
        })
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Users" />
        <div class="space-y-4">
            <div class="flex flex-col justify-between gap-2 mx-2 sm:flex-row sm:items-center">
                <div class="flex items-center justify-between w-full gap-2">
                    <Heading class="mb-1" :title="$t('Users')" :description="$t('List users in system')" />
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button v-if="hasPermission('users.create')"
                                class="flex items-center justify-center w-12 h-12 rounded-full" @click="openForm()">
                                <PlusIcon class="w-5 h-5" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>{{ $t('Add New User') }}</p>
                        </TooltipContent>
                    </Tooltip>
                </div>
            </div>

            <div class="p-2">
                <!-- Linha do checkbox Only Trashed -->
                <div v-if="hasSomePermissions('users.trashed.show', 'users.trashed.delete', 'users.trashed.restore')"
                    class="flex items-center gap-2 mb-2">
                    <Label for="only-trashed" class="flex items-center space-x-3">
                        <Checkbox id="remember" v-model="onlyTrashed" :tabindex="3" />
                        <span>{{ $t('Show deleted only') }}</span>
                    </Label>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <!-- Tooltip para Search -->
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Input v-model="searchQuery" :placeholder="$t('Search by')" class="max-w-md" />
                        </TooltipTrigger>
                        <TooltipContent>
                            <span>{{ $t('Enter part or all of the text to search') }}</span>
                        </TooltipContent>
                    </Tooltip>

                    <!-- Tooltip para Per Page -->

                    <Select v-model="perPage">
                        <SelectTrigger class="w-[120px]">
                            <SelectValue placeholder="Items per page" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="option in [10, 25, 50, 100]" :key="option" :value="option">
                                {{ option }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                </div>
            </div>

            <UsersTable :users="users" :sort="filters.sort" :search="filters.search" :per_page="filters.per_page"
                :only_trashed="onlyTrashed" @edit="(user) => {
                    currentUser = user
                    formAction = 'edit'
                    isFormOpen = true
                }" @view="(user) => openView(user)" @delete="openDeleteModal" @restore="openRestoreDeleteModal"
                @force-delete="openForceDeleteModal" />


            <UserForm width-class="min-w-[70vw] max-w-3xl" :user="currentUser" :action="formAction" :open="isFormOpen"
                @update:open="isFormOpen = $event" @success="router.reload()" />

            <UserView :user="currentUser" :open="isViewOpen" width-class="min-w-[70vw] max-w-3xl"
                @update:open="isViewOpen = $event" />

            <!-- Adicione este modal de confirmação -->
            <Dialog :open="isDeleteModalOpen" @update:open="isDeleteModalOpen = $event">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{{ $t('Confirm Deletion') }}</DialogTitle>
                        <DialogDescription>
                            {{ $t('Are you sure you want to delete') }} "{{ userToDelete?.name }}"?
                        </DialogDescription>
                    </DialogHeader>
                    <div class="flex justify-end gap-2 mt-4">
                        <Button variant="outline" @click="isDeleteModalOpen = false">{{ $t('actions.cancel') }}</Button>
                        <Button variant="destructive" @click="deleteUser">{{ $t('actions.delete') }}</Button>
                    </div>
                </DialogContent>
            </Dialog>

            <Dialog :open="isForceDeleteModalOpen" @update:open="isForceDeleteModalOpen = $event">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{{ $t('Confirm Permanent Deletion') }}</DialogTitle>
                        <DialogDescription>
                            {{ $t('Are you sure you want to permanently delete') }} "{{ userToForceDelete?.name }}"?
                        </DialogDescription>
                    </DialogHeader>
                    <div class="flex justify-end gap-2 mt-4">
                        <Button variant="outline" @click="isForceDeleteModalOpen = false">{{ $t('actions.cancel')
                        }}</Button>
                        <Button variant="destructive" @click="forceDeleteUser">{{ $t('actions.delete_permanent')
                        }}</Button>
                    </div>
                </DialogContent>
            </Dialog>

            <Dialog :open="isRestoreDeleteModalOpen" @update:open="isRestoreDeleteModalOpen = $event">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{{ $t('Confirm Restore') }}</DialogTitle>
                        <DialogDescription>
                            {{ $t('Are you sure you want to restore') }} "{{ userToRestoreDelete?.name }}"?
                        </DialogDescription>
                    </DialogHeader>
                    <div class="flex justify-end gap-2 mt-4">
                        <Button variant="outline" @click="isRestoreDeleteModalOpen = false">{{ $t('actions.cancel')
                        }}</Button>
                        <Button variant="destructive" @click="restoreDeleteUser">{{ $t('actions.restore') }}</Button>
                    </div>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
