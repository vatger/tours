<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import DataTable from './partials/DataTable.vue'
import DataForm from './partials/DataForm.vue'
import DataView from './partials/DataView.vue'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { useObjects } from '@/composables/useObjects'
import { computed, ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { debounce } from 'lodash-es'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import Heading from '@/components/Heading.vue'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { useAuth } from '@/composables/useAuth'

const { hasPermission, hasSomePermissions } = useAuth()
const breadcrumbs = computed(() => [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Activity Logs', href: route('activityLogs.index') }
])
const { activityLogs, filters } = defineProps<{
    activityLogs: any
    filters: {
        search?: string
        sort?: string | null  // Will be in format '-name,email' etc.
        per_page?: number
    }
}>()
const { isFormOpen, isViewOpen, currentObject, formAction, openForm, openView } = useObjects()
const searchQuery = ref(filters.search || '')
const perPage = ref(filters.per_page || 10)
const onlyTrashed = ref(false)
// Debounced search
watch(searchQuery, debounce((value) => {
    router.get(route('activityLogs.index'), {
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
    router.get(route('activityLogs.index'), {
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
    router.get(route('activityLogs.index'), {
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
const objectToDelete = ref<any | null>(null)
// Abrir modal de confirmação
const openDeleteModal = (object: any) => {
    objectToDelete.value = object
    isDeleteModalOpen.value = true
}
// Executar exclusão
const deleteObject = () => {
    if (objectToDelete.value) {
        router.delete(route('activityLogs.destroy', { object: objectToDelete.value.id }), {
            onSuccess: () => {
                isDeleteModalOpen.value = false
                objectToDelete.value = null
            }
        })
    }
}
// Abrir modal de confirmação de RESTORE exclusão
const isRestoreDeleteModalOpen = ref(false)
const objectToRestoreDelete = ref<any | null>(null)
const openRestoreDeleteModal = (object: any) => {
    objectToRestoreDelete.value = object
    isRestoreDeleteModalOpen.value = true
}
// Executar RESTORE exclusão
const restoreDelete = () => {
    if (objectToRestoreDelete.value) {
        router.put(route('activityLogs.trashed.restore', { object_id: objectToRestoreDelete.value.id }), {
        })
    }
    isRestoreDeleteModalOpen.value = false
    objectToRestoreDelete.value = null
}
// Abrir modal de confirmação de exclusão PERMANENTE
const isForceDeleteModalOpen = ref(false)
const objectToForceDelete = ref<any | null>(null)
const openForceDeleteModal = (object: any) => {
    objectToForceDelete.value = object
    isForceDeleteModalOpen.value = true
}
// Executar exclusão PERMANENTE
const forceDelete = () => {
    if (objectToForceDelete.value) {
        router.delete(route('activityLogs.trashed.destroy', { object_id: objectToForceDelete.value.id }), {
            onSuccess: () => {
                isForceDeleteModalOpen.value = false
                objectToForceDelete.value = null
            }
        })
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Activity Logs" />
        <div class="space-y-4">
            <div class="flex flex-col justify-between gap-2 mx-2 sm:flex-row sm:items-center">
                <div class="flex items-center justify-between w-full gap-2">
                    <Heading class="mb-1" :title="$t('Activity Logs')" :description="$t('List in system')" />

                </div>
            </div>

            <div class="p-2">
                <div class="flex items-center justify-between gap-4">
                    <!-- Tooltip para Search -->
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Input v-model="searchQuery" placeholder="Search by ..." class="max-w-md" />
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

            <DataTable :activityLogs="activityLogs" :sort="filters.sort" :search="filters.search"
                :per_page="filters.per_page" :only_trashed="onlyTrashed" @edit="(object) => {
                    currentObject = object
                    formAction = 'edit'
                    isFormOpen = true
                }" @view="(object) => openView(object)" @delete="openDeleteModal" @restore="openRestoreDeleteModal"
                @force-delete="openForceDeleteModal" />


            <DataForm width-class="min-w-[70vw] max-w-3xl" :object="currentObject" :action="formAction"
                :open="isFormOpen" @update:open="isFormOpen = $event" @success="router.reload()" />

            <DataView :object="currentObject" :open="isViewOpen" width-class="min-w-[70vw] max-w-3xl"
                @update:open="isViewOpen = $event" />

            <!-- Adicione este modal de confirmação -->
            <Dialog :open="isDeleteModalOpen" @update:open="isDeleteModalOpen = $event">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{{ $t('Confirm Deletion') }}</DialogTitle>
                        <DialogDescription>
                            {{ $t('Are you sure you want to delete') }} "{{ objectToDelete?.name }}"?
                        </DialogDescription>
                    </DialogHeader>
                    <div class="flex justify-end gap-2 mt-4">
                        <Button variant="outline" @click="isDeleteModalOpen = false">{{ $t('Cancel') }}</Button>
                        <Button variant="destructive" @click="deleteObject">{{ $t('Delete') }}</Button>
                    </div>
                </DialogContent>
            </Dialog>

            <Dialog :open="isForceDeleteModalOpen" @update:open="isForceDeleteModalOpen = $event">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{{ $t('Confirm Permanent Deletion') }}</DialogTitle>
                        <DialogDescription>
                            {{ $t('Are you sure you want to permanently delete') }} "{{ objectToForceDelete?.name }}"?
                        </DialogDescription>
                    </DialogHeader>
                    <div class="flex justify-end gap-2 mt-4">
                        <Button variant="outline" @click="isForceDeleteModalOpen = false">Cancelar</Button>
                        <Button variant="destructive" @click="forceDelete">Excluir Permanente</Button>
                    </div>
                </DialogContent>
            </Dialog>

            <Dialog :open="isRestoreDeleteModalOpen" @update:open="isRestoreDeleteModalOpen = $event">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{{ $t('Confirm Restore') }}</DialogTitle>
                        <DialogDescription>
                            {{ $t('Are you sure you want to restore') }} "{{ objectToRestoreDelete?.name }}"?
                        </DialogDescription>
                    </DialogHeader>
                    <div class="flex justify-end gap-2 mt-4">
                        <Button variant="outline" @click="isRestoreDeleteModalOpen = false">{{ $t('Cancel') }}</Button>
                        <Button variant="destructive" @click="restoreDelete">{{ $t('Restore') }}</Button>
                    </div>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
