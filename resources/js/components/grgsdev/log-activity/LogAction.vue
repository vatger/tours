<script setup lang="ts">
import { computed, nextTick, onMounted, ref } from 'vue'
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '@/components/ui/card';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip'
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible'
import { ChevronDown, ChevronUp, Search, Filter } from 'lucide-vue-next'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';

import { Calendar } from '@/components/ui/calendar';
import { ActivityLog } from '@/types/activityLog';

const containerRef = ref<HTMLElement | null>(null)
const isLoading = ref(false)
const datePopoverOpen = ref(false)
const descriptionPopoverOpen = ref(false)
const subjectPopoverOpen = ref(false)
const props = defineProps({
    activities: {
        type: Array as () => ActivityLog[],
        required: true,
        default: () => []
    },
    initialExpanded: {
        type: Boolean,
        default: false
    }
})
const visibleActivities = computed(() => {
    return filteredAndSortedActivities.value.slice(0, currentPage.value * itemsPerPage.value)
})

// Adicione este método para carregar mais itens
const loadMore = () => {
    if (isLoading.value || currentPage.value >= totalPages.value) return

    isLoading.value = true
    currentPage.value += 1
    isLoading.value = false
}

// Adicione este observer no onMounted
onMounted(() => {
    const container = containerRef.value
    if (container) {
        container.addEventListener('scroll', () => {
            if (container.scrollTop + container.clientHeight >= container.scrollHeight - 50) {
                loadMore()
            }
        })
    }
})
// Opções para o filtro de descrição
const descriptionOptions = [
    { value: 'created', label: 'Created' },
    { value: 'updated', label: 'Updated' },
    { value: 'deleted', label: 'Deleted' },
    { value: 'restored', label: 'Restored' }
]

const emit = defineEmits(['activity-selected'])

const isTableExpanded = ref(props.initialExpanded)
const selectedActivity = ref<ActivityLog | null>(null)
const isDialogOpen = ref(false)
const searchQuery = ref('')
const dateFilter = ref<Date | undefined>(undefined)
const descriptionFilter = ref<string>('')
const subjectFilter = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(5) // Valor padrão: 5 itens por página

// Estado de ordenação
type SortField = 'date' | 'description' | 'subject'
type SortDirection = 'asc' | 'desc'

const sortConfig = ref<{
    field: SortField
    direction: SortDirection
}>({
    field: 'date',
    direction: 'desc'
})

// Função para parsear datas
const parseCustomDate = (dateString: string) => {
    const [datePart, timePart] = dateString.split(' ')
    const [day, month, year] = datePart.split('/').map(Number)
    const [hours, minutes] = timePart.split(':').map(Number)
    return new Date(year, month - 1, day, hours, minutes)
}

// Computed property para atividades filtradas e ordenadas
const filteredAndSortedActivities = computed(() => {
    // Filtra as atividades
    const filtered = props.activities.filter(activity => {
        // Filtro por busca geral
        if (searchQuery.value) {
            const query = searchQuery.value.toLowerCase()
            const descriptionMatch = activity.description?.toLowerCase().includes(query) ?? false
            const subjectMatch = activity.subject_type?.toLowerCase().includes(query) ?? false
            if (!descriptionMatch && !subjectMatch) return false
        }

        // Filtro por data
        if (dateFilter.value) {
            const activityDate = new Date(activity.created_at.short_with_time.split(' ')[0].split('/').reverse().join('-'))
            const filterDate = new Date(dateFilter.value)
            if (
                activityDate.getDate() !== filterDate.getDate() ||
                activityDate.getMonth() !== filterDate.getMonth() ||
                activityDate.getFullYear() !== filterDate.getFullYear()
            ) {
                return false
            }
        }

        // Filtro por descrição
        if (descriptionFilter.value && descriptionFilter.value !== 'all') {
            if (!activity.description?.toLowerCase().includes(descriptionFilter.value.toLowerCase())) {
                return false
            }
        }
        // Filtro por causador
        if (subjectFilter.value) {
            if (!activity.subject_type?.toLowerCase().includes(subjectFilter.value.toLowerCase())) {
                return false
            }
        }

        return true
    })



    // Ordena as atividades filtradas
    return filtered.sort((a, b) => {
        let comparison = 0

        switch (sortConfig.value.field) {
            case 'date':
                const dateA = parseCustomDate(a.created_at.short_with_time).getTime()
                const dateB = parseCustomDate(b.created_at.short_with_time).getTime()
                comparison = dateB - dateA
                break

            case 'description':
                const descA = a.description || ''
                const descB = b.description || ''
                comparison = descA.localeCompare(descB)
                break

            case 'subject':
                const subjectA = a.subject_type || ''
                const subjectB = b.subject_type || ''
                comparison = subjectA.localeCompare(subjectB)
                break
        }

        return sortConfig.value.direction === 'desc' ? comparison : -comparison
    })
})



// Computed property para total de páginas
const totalPages = computed(() => {
    return Math.ceil(filteredAndSortedActivities.value.length / itemsPerPage.value)
})


const toggleSort = (field: SortField) => {
    if (sortConfig.value.field === field) {
        sortConfig.value.direction = sortConfig.value.direction === 'desc' ? 'asc' : 'desc'
    } else {
        sortConfig.value.field = field
        sortConfig.value.direction = 'desc'
    }
    // Resetar para a primeira página quando ordenar
    currentPage.value = 1
}

const getSortIcon = (field: SortField) => {
    if (sortConfig.value.field !== field) return null
    return sortConfig.value.direction === 'desc' ? ChevronDown : ChevronUp
}

const openDialog = (activity: ActivityLog) => {
    selectedActivity.value = activity
    isDialogOpen.value = true
    emit('activity-selected', activity)
}

// Funções para limpar filtros
const clearDateFilter = () => {
    dateFilter.value = undefined
    datePopoverOpen.value = false
}

const clearDescriptionFilter = () => {
    descriptionFilter.value = 'all'
    descriptionPopoverOpen.value = false
}

const clearCauserFilter = () => {
    subjectFilter.value = ''
    subjectPopoverOpen.value = false
}

const clearAllFilters = async () => {
    clearDateFilter()
    clearDescriptionFilter()
    clearCauserFilter()
    searchQuery.value = ''

    await nextTick()
    // Fecha todos os popovers após a atualização do DOM
    datePopoverOpen.value = false
    descriptionPopoverOpen.value = false
    subjectPopoverOpen.value = false
}
</script>

<template>

    <div>
        <Card>
            <Collapsible v-model:open="isTableExpanded" class="w-full">
                <CardHeader class="p-0 border-b h-14">
                    <CollapsibleTrigger as-child>
                        <div
                            class="flex items-center justify-between h-full px-4 cursor-pointer hover:bg-accent sm:px-6">
                            <div class="flex items-center gap-2">
                                <CardTitle class="text-sm">{{ $t('Actions History List') }} {{
                                    filteredAndSortedActivities.length }} {{ $t('items') }}</CardTitle>
                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <ChevronDown
                                            class="w-5 h-5 transition-transform duration-200 text-muted-foreground"
                                            :class="{ 'transform rotate-180': isTableExpanded }" />
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>{{ isTableExpanded ? $t('Close') : $t('Show') }}</p>
                                    </TooltipContent>
                                </Tooltip>
                            </div>
                        </div>
                    </CollapsibleTrigger>
                </CardHeader>
                <CollapsibleContent>
                    <CardContent class="p-4 sm:p-6">
                        <div class="flex items-center gap-2 mb-4 ml-4">
                            <!-- Campo de busca -->
                            <div class="relative w-96">
                                <Search class="absolute w-4 h-4 text-gray-400 -translate-y-1/2 left-3 top-1/2" />
                                <Input v-model="searchQuery" type="text" :placeholder="$t('Search...')"
                                    class="py-2 pl-10 pr-4 text-sm" @input="currentPage = 1" />
                            </div>

                            <!-- Filtro por data -->
                            <Popover v-model:open="datePopoverOpen">
                                <PopoverTrigger as-child>
                                    <Button variant="outline" size="sm" class="flex items-center gap-1">
                                        <Filter class="w-4 h-4" />
                                        <span>{{ $t('Date') }}</span>
                                        <span v-if="dateFilter" class="ml-1 text-blue-500">●</span>
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-auto p-0">
                                    <Calendar v-model="dateFilter" mode="single"
                                        @update:model-value="currentPage = 1" />
                                    <div class="p-2 border-t">
                                        <Button variant="ghost" size="sm" @click="clearDateFilter">
                                            {{ $t('Clear filter') }}
                                        </Button>
                                    </div>
                                </PopoverContent>
                            </Popover>

                            <!-- Filtro por descrição - atualizado com v-model:open -->
                            <Popover v-model:open="descriptionPopoverOpen">
                                <PopoverTrigger as-child>
                                    <Button variant="outline" size="sm" class="flex items-center gap-1">
                                        <Filter class="w-4 h-4" />
                                        <span>{{ $t('Description') }}</span>
                                        <span v-if="descriptionFilter" class="ml-1 text-blue-500">●</span>
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-48 p-2">
                                    <Select v-model="descriptionFilter" @update:model-value="currentPage = 1">
                                        <SelectTrigger class="w-full">
                                            <SelectValue :placeholder="$t('Select description')" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <!-- Change the empty value to a non-empty string, like 'all' -->
                                            <SelectItem value="all">
                                                {{ $t('All') }}
                                            </SelectItem>
                                            <SelectItem v-for="option in descriptionOptions" :key="option.value"
                                                :value="option.value">
                                                {{ $t(option.label) }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <Button variant="ghost" size="sm" class="w-full mt-2"
                                        @click="clearDescriptionFilter">
                                        {{ $t('Clear filter') }}
                                    </Button>
                                </PopoverContent>
                            </Popover>
                            <!-- Filtro por causador - atualizado com v-model:open -->
                            <Popover v-model:open="subjectPopoverOpen">
                                <PopoverTrigger as-child>
                                    <Button variant="outline" size="sm" class="flex items-center gap-1">
                                        <Filter class="w-4 h-4" />
                                        <span>{{ $t('Subject') }}</span>
                                        <span v-if="subjectFilter" class="ml-1 text-blue-500">●</span>
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-64 p-2">
                                    <Input v-model="subjectFilter" :placeholder="$t('Filter subject...')"
                                        @update:model-value="currentPage = 1" />
                                    <Button variant="ghost" size="sm" class="mt-2" @click="clearCauserFilter">
                                        {{ $t('Clear filter') }}
                                    </Button>
                                </PopoverContent>
                            </Popover>

                            <!-- Botão para limpar todos os filtros -->
                            <Button v-if="dateFilter || descriptionFilter || subjectFilter || searchQuery"
                                variant="ghost" size="sm" @click="clearAllFilters">
                                {{ $t('Clear all') }}
                            </Button>
                        </div>
                        <!-- <div class="max-h-[300px] sm:max-h-[200px] lg:max-h-[300px] overflow-y-auto">
                    <Table class="w-full text-xs border-separate table-fixed border-spacing-0"> -->
                        <div ref="containerRef" class="max-h-[300px] overflow-y-auto relative overscroll-contain">
                            <Table class="w-full text-xs border-separate table-fixed border-spacing-0">
                                <!-- Cabeçalhos da tabela (mantido igual) -->
                                <TableHeader
                                    class="sticky top-0 z-20 bg-white shadow-sm dark:bg-gray-800 will-change-transform">
                                    <!-- <TableHeader class="sticky top-0 z-20 bg-white shadow-sm dark:bg-gray-800"> -->
                                    <TableRow class="leading-none">
                                        <TableHead class="w-[25%] py-1 px-2 text-left align-middle">
                                            <button @click="() => toggleSort('date')"
                                                class="flex items-center hover:underline">
                                                {{ $t('Date') }}
                                                <component :is="getSortIcon('date')" v-if="getSortIcon('date')"
                                                    class="w-3 h-3 ml-1" />
                                            </button>
                                        </TableHead>
                                        <TableHead class="w-[35%] py-1 px-2 text-left align-middle">
                                            <button @click="() => toggleSort('description')"
                                                class="flex items-center hover:underline">
                                                {{ $t('Description') }}
                                                <component :is="getSortIcon('description')"
                                                    v-if="getSortIcon('description')" class="w-3 h-3 ml-1" />
                                            </button>
                                        </TableHead>
                                        <TableHead class="w-[20%] py-1 px-2 text-left align-middle">
                                            <button @click="() => toggleSort('subject')"
                                                class="flex items-center hover:underline">
                                                {{ $t('Subject') }}
                                                <component :is="getSortIcon('subject')" v-if="getSortIcon('subject')"
                                                    class="w-3 h-3 ml-1" />
                                            </button>
                                        </TableHead>
                                        <TableHead class="w-[20%] py-1 px-2 text-left align-middle">
                                            {{ $t('Actions') }}
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>

                                <TableBody>
                                    <TableRow v-for="activity in visibleActivities" :key="activity.id"
                                        class="leading-none">
                                        <TableCell class="w-[25%] py-[4px] px-2 text-left align-middle">
                                            {{ activity.created_at.short }}
                                        </TableCell>
                                        <TableCell class="w-[35%] py-[4px] px-2 text-left align-middle truncate">
                                            {{ activity.description }}
                                        </TableCell>
                                        <TableCell class="w-[20%] py-[4px] px-2 text-left align-middle">
                                            {{ activity.subject_type || '-' }}
                                        </TableCell>
                                        <TableCell class="w-[20%] py-[4px] px-2 text-left align-middle">
                                            <div class="flex items-center space-x-2">
                                                <Button variant="ghost" size="icon" class="text-blue-600"
                                                    :title="$t('View')" @click="openDialog(activity)">
                                                    👁️
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="filteredAndSortedActivities.length === 0" class="leading-none">
                                        <TableCell colspan="5" class="py-[4px] text-center">
                                            {{ $t('No results found') }}
                                        </TableCell>
                                    </TableRow>

                                    <TableRow v-if="isLoading">
                                        <TableCell colspan="5" class="py-[4px] text-center">
                                            {{ $t('Loading...') }}
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <!-- Controles de paginação melhorados -->
                        <div class="flex items-center justify-between p-4 border-t">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $t('Showing') }} {{ Math.min(visibleActivities.length,
                                    filteredAndSortedActivities.length) }}
                                {{ $t('of') }} {{ filteredAndSortedActivities.length }} {{ $t('entries') }}
                            </div>

                            <Button v-if="visibleActivities.length < filteredAndSortedActivities.length"
                                variant="outline" size="sm" @click="loadMore" :disabled="isLoading">
                                {{ $t('Load more') }}
                            </Button>
                        </div>
                    </CardContent>
                </CollapsibleContent>
            </Collapsible>
        </Card>
        <!-- Modal de detalhes da atividade -->
        <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
            <DialogContent class="w-full max-w-[70vw] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>{{ $t('Log Activity Details') }}</DialogTitle>
                    <DialogDescription>
                        {{ $t('Viewing log details for the selected activity') }}
                    </DialogDescription>
                    <DialogDescription>
                        {{ selectedActivity?.subject_type || 'System' }} has {{ selectedActivity?.description }} at
                        {{
                            selectedActivity?.created_at.short }}
                    </DialogDescription>
                </DialogHeader>
                <div v-if="selectedActivity && selectedActivity.properties"
                    class="w-full max-w-[70vw] max-h-[80vh] overflow-y-auto mt-4">
                    <table class="w-full text-sm text-left border-collapse table-auto">
                        <thead class="sticky top-0 z-20 bg-white shadow-sm dark:bg-gray-800">
                            <tr>
                                <th class="w-1/2 p-2 font-semibold bg-gray-100 dark:bg-gray-800">
                                    {{ $t('Old') }}
                                </th>
                                <th class="w-1/2 p-2 font-semibold bg-gray-100 dark:bg-gray-800">
                                    {{ $t('Attributes') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <template
                                v-for="key in Object.keys({ ...(selectedActivity.properties.old || {}), ...(selectedActivity.properties.attributes || {}) })"
                                :key="key">
                                <tr class="border-t border-gray-200 dark:border-gray-700">
                                    <td class="p-2 bg-gray-50 dark:bg-gray-900">
                                        <span :class="{
                                            'font-semibold text-blue-600': selectedActivity.properties.attributes && selectedActivity.properties.attributes[key] !== (selectedActivity.properties.old ? selectedActivity.properties.old[key] : undefined)
                                        }">
                                            {{ $t(key) }}: {{ selectedActivity.properties.old ?
                                                selectedActivity.properties.old[key] || '-' : '-' }}
                                        </span>
                                    </td>
                                    <td class="p-2 bg-gray-50 dark:bg-gray-900">
                                        <span :class="{
                                            'font-semibold text-green-600': selectedActivity.properties.old && selectedActivity.properties.attributes && selectedActivity.properties.attributes[key] !== (selectedActivity.properties.old ? selectedActivity.properties.old[key] : undefined)
                                        }">
                                            {{ $t(key) }}: {{ selectedActivity.properties.attributes ?
                                                selectedActivity.properties.attributes[key] || '-' : '-' }}
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <div v-if="!Object.keys(selectedActivity.properties.old || {}).length && !Object.keys(selectedActivity.properties.attributes || {}).length"
                        class="bg-gray-100 dark:bg-gray-800 p-4 rounded-md overflow-auto max-h-[400px] text-sm text-gray-900 dark:text-gray-100 text-center">
                        {{ $t('No data available') }}
                    </div>
                </div>

                <div v-else
                    class="bg-gray-100 dark:bg-gray-800 p-4 rounded-md overflow-auto max-h-[400px] text-sm text-gray-900 dark:text-gray-100 text-center">
                    {{ $t('No activity data available') }}
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>
