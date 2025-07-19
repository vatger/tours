<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Models" />
        <div class="space-y-4">
            <div class="flex flex-col justify-between gap-4 mx-4 sm:flex-row sm:items-center">
                <div class="flex items-center justify-between w-full gap-4">
                    <Heading :title="$t('Models')" :description="$t('List development system models')" />
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button class="flex items-center justify-center w-12 h-12 rounded-full"
                                @click="openCreateModal">
                                <PlusIcon class="w-5 h-5" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>{{ $t('Add New Model') }}</p>
                        </TooltipContent>
                    </Tooltip>
                </div>
            </div>

            <div class="space-y-8">
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <Input v-model="filters.search" placeholder="Filter models..." class="max-w-xs" />
                            <div class="flex space-x-2">
                                <Select v-model="filters.per_page">
                                    <SelectTrigger class="w-[100px]">
                                        <SelectValue placeholder="Per page" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem :value="10">10</SelectItem>
                                        <SelectItem :value="25">25</SelectItem>
                                        <SelectItem :value="50">50</SelectItem>
                                        <SelectItem :value="100">100</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </CardHeader>

                    <CardContent class="p-4 flex flex-col h-[calc(100vh-300px)]">
                        <div class="relative flex flex-col flex-1 overflow-hidden">
                            <div class="sticky top-0 z-20 bg-gray-50 dark:bg-gray-800 border-b">
                                <div class="flex">
                                    <div class="sticky left-0 z-30 bg-gray-50 dark:bg-gray-800 flex-shrink-0 name-cell">
                                        <TableHead>
                                            <span class="flex items-center cursor-pointer" @click="sortBy('name')">
                                                {{ $t('Name') }}
                                                <span v-if="filters.sort_field === 'name'" class="ml-1">
                                                    {{ filters.sort_direction === 'asc' ? '↑' : '↓' }}
                                                </span>
                                            </span>
                                        </TableHead>
                                    </div>
                                    <div class="flex-shrink-0 version-cell">
                                        <TableHead>{{ $t('Version') }}</TableHead>
                                    </div>
                                    <div class="flex-shrink-0 attributes-cell">
                                        <TableHead>{{ $t('Attributes') }}</TableHead>
                                    </div>
                                    <div class="flex-shrink-0 relations-cell">
                                        <TableHead>{{ $t('Relations') }}</TableHead>
                                    </div>
                                    <div class="flex-shrink-0 updated-cell">
                                        <TableHead>
                                            <span class="flex items-center cursor-pointer"
                                                @click="sortBy('updated_at')">
                                                {{ $t('Updated At') }}
                                                <span v-if="filters.sort_field === 'updated_at'" class="ml-1">
                                                    {{ filters.sort_direction === 'asc' ? '↑' : '↓' }}
                                                </span>
                                            </span>
                                        </TableHead>
                                    </div>
                                    <div class="flex-shrink-0 actions-cell">
                                        <TableHead class="text-center">{{ $t('Actions') }}</TableHead>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-1 overflow-y-auto">
                                <Table>
                                    <TableBody>
                                        <div v-if="props.models.data.length === 0"
                                            class="flex items-center justify-center h-full text-muted-foreground">
                                            <p>{{ $t('No models found') }}</p>
                                        </div>
                                        <div v-else class="table-container">
                                            <TableRow v-for="model in props.models.data" :key="model.name" class="flex">
                                                <TableCell
                                                    class="sticky left-0 z-10 bg-background dark:bg-gray-900 name-cell">
                                                    {{ model.name }}
                                                </TableCell>
                                                <TableCell class="flex-shrink-0 version-cell">
                                                    {{ model.version }}
                                                </TableCell>
                                                <TableCell class="flex-shrink-0 attributes-cell">
                                                    {{ model.attributes_count }}
                                                </TableCell>
                                                <TableCell class="flex-shrink-0 relations-cell">
                                                    {{ model.relations_count }}
                                                </TableCell>
                                                <TableCell class="flex-shrink-0 updated-cell">
                                                    {{ model.updated_at ? model.updated_at : 'N/A' }}
                                                </TableCell>
                                                <TableCell class="flex-shrink-0 actions-cell">
                                                    <div class="flex justify-center space-x-2">
                                                        <Button variant="ghost" size="sm"
                                                            @click="openViewModal(model.name)">
                                                            <EyeIcon class="w-4 h-4" />
                                                        </Button>
                                                        <Button variant="ghost" size="sm" @click="openEditModal(model)">
                                                            <PencilIcon class="w-4 h-4" />
                                                        </Button>
                                                        <Button variant="ghost" size="sm"
                                                            class="text-destructive hover:text-destructive"
                                                            @click="confirmDelete(model.name)">
                                                            <TrashIcon class="w-4 h-4" />
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        </div>
                                    </TableBody>
                                </Table>
                            </div>
                        </div>

                        <div class="sticky bottom-0 px-6 py-2 border-t bg-background dark:bg-gray-600 mt-2">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-muted-foreground">
                                    Showing {{ props.models.from }} to {{ props.models.to }} of {{ props.models.total }}
                                    results
                                </div>
                                <Pagination :links="props.models.links" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Dialog v-model:open="showFormModal">
                    <DialogContent class="max-w-4xl h-[90vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>{{ isEditing ? 'Edit Model' : 'Create Model' }}</DialogTitle>
                        </DialogHeader>
                        <ModelForm v-if="showFormModal" v-model="formData" :errors="formErrors" :is-editing="isEditing"
                            @submit="submitForm" @cancel="showFormModal = false" />
                    </DialogContent>
                </Dialog>

                <Dialog v-model:open="showViewModal">
                    <DialogContent class="max-w-4xl h-[90vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>Model Details: {{ currentModel?.name }}</DialogTitle>
                        </DialogHeader>
                        <ModelDetails v-if="showViewModal && currentModel" :model="currentModel"
                            @edit="openEditModal" />
                    </DialogContent>
                </Dialog>

                <AlertDialog v-model:open="showDeleteDialog">
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                            <AlertDialogDescription>
                                This action cannot be undone. This will permanently delete "{{ modelToDelete }}" and
                                remove your data from our servers.
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                            <AlertDialogAction variant="destructive" @click="deleteModel">Delete</AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { PlusIcon, EyeIcon, PencilIcon, TrashIcon } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { Card, CardHeader, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableRow } from '@/components/ui/table'
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import Heading from '@/components/Heading.vue'
import Pagination from './../Pagination.vue'
import ModelForm from './partials/ModelForm.vue'
import ModelDetails from './partials/ModelDetails.vue'
import { debounce } from 'lodash'

const props = defineProps<{
    models: {
        data: Array<{
            name: string
            version: string
            attributes_count: number
            relations_count: number
            updated_at: string
        }>
        links: Array<{ url: string | null, label: string, active: boolean }>
        from: number
        to: number
        total: number
    }
    filters: {
        search: string
        sort_field: string
        sort_direction: string
        per_page: number
    }
}>()

const breadcrumbs = computed(() => [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Models', href: route('models-generate.index') }
])

const filters = reactive({
    search: props.filters.search || '',
    per_page: props.filters.per_page || 10,
    sort_field: props.filters.sort_field || 'updated_at',
    sort_direction: props.filters.sort_direction || 'desc'
})

const showDeleteDialog = ref(false)
const modelToDelete = ref('')
const showFormModal = ref(false)
const showViewModal = ref(false)
const isEditing = ref(false)
const currentModel = ref<any>(null)
const formErrors = ref<any>({})

const formData = reactive({
    name: '',
    version: 'V1',
    softDeletes: true,
    timestamps: true,
    useIsActive: false,
    useApprovedStatus: false,
    useScribe: false,
    authorize: false,
    logsActivity: false,
    clearsResponseCache: false,
    attributes: [
        {
            name: 'id',
            type: 'string',
            length: 255,
            max: 255,
            min: 0,
            precision: 0,
            scale: 0,
            validate: true,
            required: true,
            nullable: false,
            unique: true,
            translated: false,
            sortAble: true,
            filterAble: true,
            exactFilter: false,
            searchAble: false,
            description: 'The unique identifier',
            example: ''
        }
    ],
    relations: []
})

const fetchModels = () => {
    router.get(route('models-generate.index'), filters, {
        preserveState: true,
        replace: true
    })
}

const debouncedFetch = debounce(fetchModels, 300)

watch(() => filters.search, debouncedFetch)
watch(() => filters.per_page, fetchModels)

const sortBy = (field: string) => {
    if (filters.sort_field === field) {
        filters.sort_direction = filters.sort_direction === 'asc' ? 'desc' : 'asc'
    } else {
        filters.sort_field = field
        filters.sort_direction = 'asc'
    }
    fetchModels()
}

const confirmDelete = (modelName: string) => {
    modelToDelete.value = modelName
    showDeleteDialog.value = true
}

const deleteModel = () => {
    router.delete(route('models-generate.destroy', { model: modelToDelete.value }), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            showDeleteDialog.value = false
        }
    })
}

const openCreateModal = () => {
    isEditing.value = false
    resetForm()
    showFormModal.value = true
}

const openEditModal = (model: any) => {
    isEditing.value = true
    currentModel.value = model
    Object.assign(formData, model)
    showFormModal.value = true
    showViewModal.value = false
}
const openViewModal = (modelName: string) => {
    router.get(route('models-generate.show', { model: modelName }), {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            currentModel.value = page.props.model
            showViewModal.value = true
        },
        onError: (errors) => {
            console.error('Failed to fetch model details:', errors)
        }
    })
}



const resetForm = () => {
    Object.assign(formData, {
        name: '',
        version: 'V1',
        softDeletes: true,
        timestamps: true,
        useIsActive: false,
        useApprovedStatus: false,
        useScribe: false,
        authorize: false,
        logsActivity: false,
        clearsResponseCache: false,
        attributes: [
            {
                name: 'id',
                type: 'string',
                length: 255,
                max: 255,
                min: 0,
                precision: 0,
                scale: 0,
                validate: true,
                required: true,
                nullable: false,
                unique: true,
                translated: false,
                sortAble: true,
                filterAble: true,
                exactFilter: false,
                searchAble: false,
                description: 'The unique identifier',
                example: ''
            }
        ],
        relations: []
    })
    formErrors.value = {}
}

const submitForm = () => {
    const url = isEditing.value
        ? route('models-generate.update', { model: formData.name })
        : route('models-generate.store')

    const method = isEditing.value ? 'put' : 'post'

    router[method](url, formData, {
        preserveScroll: true,
        onSuccess: () => {
            showFormModal.value = false
        },
        onError: (errors) => {
            formErrors.value = errors
        }
    })
}
</script>

<style scoped>
.name-cell {
    flex: 1;
    min-width: 200px;
    padding: 0.75rem 1rem;
}

.version-cell {
    min-width: 150px;
    width: 150px;
    padding: 0.75rem 1rem;
}

.attributes-cell {
    min-width: 150px;
    width: 150px;
    padding: 0.75rem 1rem;
}

.relations-cell {
    min-width: 150px;
    width: 150px;
    padding: 0.75rem 1rem;
}

.updated-cell {
    min-width: 200px;
    width: 200px;
    padding: 0.75rem 1rem;
}

.actions-cell {
    min-width: 150px;
    width: 150px;
    padding: 0.75rem 1rem;
}

.table-container {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.table-row {
    display: flex;
    width: 100%;
}

.table-cell {
    display: flex;
    align-items: center;
    border-bottom: 1px solid #e5e7eb;
}

.dark .table-cell {
    border-bottom-color: #374151;
}

.sticky-header {
    position: sticky;
    top: 0;
    z-index: 20;
    background-color: #f9fafb;
}

.dark .sticky-header {
    background-color: #1f2937;
}

.sticky-footer {
    position: sticky;
    bottom: 0;
    z-index: 10;
    background-color: white;
}

.dark .sticky-footer {
    background-color: #111827;
}
</style>
