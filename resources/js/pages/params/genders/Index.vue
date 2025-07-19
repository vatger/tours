<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import GenderTable from './partials/DataTable.vue'
import GenderForm from './partials/DataForm.vue'
import GenderView from './partials/DataView.vue'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { useObjects } from '@/composables/useObjects'
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
    { title: 'Genders', href: route('genders.index') }
])
const { genders, filters } = defineProps<{
    genders: any
    filters: {
        search?: string
        sort?: string | null  // Will be in format '-name,email' etc.
        per_page?: number,
        only_trashed: boolean
    }
}>()
console.log(filters)
const { isFormOpen, isViewOpen, currentObject, formAction, openForm, openView } = useObjects()
const searchQuery = ref(filters.search || '')
const perPage = ref(filters.per_page || 10)
const onlyTrashed = ref(filters.only_trashed || false)
// Debounced search
watch(searchQuery, debounce((value) => {
    router.get(route('genders.index'), {
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
    router.get(route('genders.index'), {
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
    router.get(route('genders.index'), {
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
const openDeleteModal = (gender: any) => {
    objectToDelete.value = gender
    isDeleteModalOpen.value = true
}
// Executar exclusão
const deleteObject = () => {
    if (objectToDelete.value) {
        router.delete(route('genders.destroy', { gender: objectToDelete.value.id }), {
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
const openRestoreDeleteModal = (gender: any) => {
    objectToRestoreDelete.value = gender
    isRestoreDeleteModalOpen.value = true
}
// Executar RESTORE exclusão
const restoreDelete = () => {
    if (objectToRestoreDelete.value) {
        router.put(route('genders.trashed.restore', { gender_id: objectToRestoreDelete.value.id }), {
        })
    }
    isRestoreDeleteModalOpen.value = false
    objectToRestoreDelete.value = null
}
// Abrir modal de confirmação de exclusão PERMANENTE
const isForceDeleteModalOpen = ref(false)
const objectToForceDelete = ref<any | null>(null)
const openForceDeleteModal = (gender: any) => {
    objectToForceDelete.value = gender
    isForceDeleteModalOpen.value = true
}
// Executar exclusão PERMANENTE
const forceDelete = () => {
    if (objectToForceDelete.value) {
        router.delete(route('genders.trashed.destroy', { gender_id: objectToForceDelete.value.id }), {
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

        <Head title="Genders" />
        <div class="space-y-4">
            <div class="flex flex-col justify-between gap-2 mx-2 sm:flex-row sm:items-center">
                <div class="flex items-center justify-between w-full gap-2">
                    <Heading class="mb-1" :title="$t('Genders')" :description="$t('List in system')" />
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button v-if="hasPermission('genders.create')"
                                class="flex items-center justify-center w-12 h-12 rounded-full" @click="openForm()">
                                <PlusIcon class="w-5 h-5" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>{{ $t('Add New Gender') }}</p>
                        </TooltipContent>
                    </Tooltip>
                </div>
            </div>

            <div class="p-2">
                <!-- Linha do checkbox Only Trashed -->
                <div v-if="hasSomePermissions('genders.trashed.show', 'genders.trashed.delete', 'genders.trashed.restore')"
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

            <GenderTable :genders="genders" :sort="filters.sort" :search="filters.search" :per_page="filters.per_page"
                :only_trashed="onlyTrashed" @edit="(gender) => {
                    currentObject = gender
                    formAction = 'edit'
                    isFormOpen = true
                }" @view="(gender) => openView(gender)" @delete="openDeleteModal" @restore="openRestoreDeleteModal"
                @force-delete="openForceDeleteModal" />


            <GenderForm width-class="min-w-[70vw] max-w-3xl" :gender="currentObject" :action="formAction"
                :open="isFormOpen" @update:open="isFormOpen = $event" @success="router.reload()" />

            <GenderView :gender="currentObject" :open="isViewOpen" width-class="min-w-[70vw] max-w-3xl"
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
                        <Button variant="outline" @click="isDeleteModalOpen = false">{{ $t('actions.cancel') }}</Button>
                        <Button variant="destructive" @click="deleteObject">{{ $t('actions.delete') }}</Button>
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
                        <Button variant="outline" @click="isForceDeleteModalOpen = false">{{ $t('actions.cancel')
                        }}</Button>
                        <Button variant="destructive" @click="forceDelete">{{ $t('actions.delete') }}</Button>
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
                        <Button variant="outline" @click="isRestoreDeleteModalOpen = false">{{ $t('actions.cancel')
                        }}</Button>
                        <Button variant="destructive" @click="restoreDelete">{{ $t('actions.restore') }}</Button>
                    </div>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
