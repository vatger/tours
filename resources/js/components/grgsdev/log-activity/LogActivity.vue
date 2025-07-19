<script setup lang="ts">
import { computed, nextTick, ref } from 'vue'
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '@/components/ui/card';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible'
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip'
import { ChevronDown, ChevronUp, Search, ChevronLeft, ChevronRight, Filter } from 'lucide-vue-next'
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

const datePopoverOpen = ref(false)
const descriptionPopoverOpen = ref(false)
const causerPopoverOpen = ref(false)
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

// Opções para o filtro de descrição
const descriptionOptions = [
    { value: 'created', label: 'Created' },
    { value: 'updated', label: 'Updated' },
    { value: 'deleted', label: 'Deleted' },
    { value: 'restored', label: 'Restored' }
]

const emit = defineEmits(['activity-selected'])

const isTableExpanded = ref(props.initialExpanded)
const selectedActivity = ref<Activity | null>(null)
const isDialogOpen = ref(false)
const searchQuery = ref('')
const dateFilter = ref<Date | undefined>(undefined)
const descriptionFilter = ref<string>('')
const causerFilter = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(5) // Valor padrão: 5 itens por página

// Opções para o número de itens por página
const pageSizeOptions = [5, 10, 15]

// Estado de ordenação
type SortField = 'date' | 'description' | 'causer'
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
            const causerMatch = activity.causer_name?.toLowerCase().includes(query) ?? false
            if (!descriptionMatch && !causerMatch) return false
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
        if (causerFilter.value) {
            if (!activity.causer_name?.toLowerCase().includes(causerFilter.value.toLowerCase())) {
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

            case 'causer':
                const causerA = a.causer_name || ''
                const causerB = b.causer_name || ''
                comparison = causerA.localeCompare(causerB)
                break
        }

        return sortConfig.value.direction === 'desc' ? comparison : -comparison
    })
})

// Computed property para determinar quais páginas mostrar
const visiblePages = computed(() => {
    const pages = []
    const total = totalPages.value
    const current = currentPage.value
    const maxVisible = 5 // Máximo de números de página visíveis

    // Sempre mostra a primeira página
    pages.push(1)

    // Determina se precisa mostrar "..." após a primeira página
    if (current - 1 > 2) {
        pages.push(-1) // -1 representa "..."
    }

    // Mostra páginas ao redor da atual
    const start = Math.max(2, current - 1)
    const end = Math.min(total - 1, current + 1)

    for (let i = start; i <= end; i++) {
        if (!pages.includes(i)) {
            pages.push(i)
        }
    }

    // Determina se precisa mostrar "..." antes da última página
    if (total - current > 2) {
        if (!pages.includes(total - 1)) {
            pages.push(-1) // -1 representa "..."
        }
    }

    // Sempre mostra a última página se for diferente da primeira
    if (total > 1 && !pages.includes(total)) {
        pages.push(total)
    }

    return pages
})

// Computed property para atividades paginadas
const paginatedActivities = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value
    const end = start + itemsPerPage.value
    return filteredAndSortedActivities.value.slice(start, end)
})

// Computed property para total de páginas
const totalPages = computed(() => {
    return Math.ceil(filteredAndSortedActivities.value.length / itemsPerPage.value)
})

// Funções de navegação da paginação
const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++
    }
}

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--
    }
}

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page
    }
}

// Função para mudar o número de itens por página
const changeItemsPerPage = (value: number) => {
    itemsPerPage.value = value
    // Resetar para a primeira página ao mudar o tamanho
    currentPage.value = 1
}

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

const openDialog = (activity: Activity) => {
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
    causerFilter.value = ''
    causerPopoverOpen.value = false
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
    causerPopoverOpen.value = false
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
                                <CardTitle class="text-sm">{{ $t('Activity History List') }} {{
                                    filteredAndSortedActivities.length }} {{ $t('items') }}
                                </CardTitle>
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
                            <Popover v-model:open="causerPopoverOpen">
                                <PopoverTrigger as-child>
                                    <Button variant="outline" size="sm" class="flex items-center gap-1">
                                        <Filter class="w-4 h-4" />
                                        <span>{{ $t('Causer') }}</span>
                                        <span v-if="causerFilter" class="ml-1 text-blue-500">●</span>
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-64 p-2">
                                    <Input v-model="causerFilter" :placeholder="$t('Filter causer...')"
                                        @update:model-value="currentPage = 1" />
                                    <Button variant="ghost" size="sm" class="mt-2" @click="clearCauserFilter">
                                        {{ $t('Clear filter') }}
                                    </Button>
                                </PopoverContent>
                            </Popover>

                            <!-- Botão para limpar todos os filtros -->
                            <Button v-if="dateFilter || descriptionFilter || causerFilter || searchQuery"
                                variant="ghost" size="sm" @click="clearAllFilters">
                                {{ $t('Clear all') }}
                            </Button>
                        </div>
                        <div
                            class="max-h-[300px] sm:max-h-[200px] lg:max-h-[300px] overflow-y-auto relative overscroll-contain">
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
                                            <button @click="() => toggleSort('causer')"
                                                class="flex items-center hover:underline">
                                                {{ $t('Causer') }}
                                                <component :is="getSortIcon('causer')" v-if="getSortIcon('causer')"
                                                    class="w-3 h-3 ml-1" />
                                            </button>
                                        </TableHead>
                                        <TableHead class="w-[20%] py-1 px-2 text-left align-middle">
                                            {{ $t('Actions') }}
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>

                                <TableBody>
                                    <TableRow v-for="activity in paginatedActivities" :key="activity.id"
                                        class="leading-none">
                                        <TableCell class="w-[25%] py-[4px] px-2 text-left align-middle">
                                            {{ activity.created_at.short }}
                                        </TableCell>
                                        <TableCell class="w-[35%] py-[4px] px-2 text-left align-middle truncate">
                                            {{ activity.description }}
                                        </TableCell>
                                        <TableCell class="w-[20%] py-[4px] px-2 text-left align-middle">
                                            {{ activity.causer_name || '-' }}
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
                                </TableBody>
                            </Table>
                        </div>

                        <!-- Controles de paginação -->
                        <div class="flex items-center justify-between p-4 border-t">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $t('Items per page') }}:
                                </span>
                                <Select :model-value="itemsPerPage" @update:model-value="changeItemsPerPage">
                                    <SelectTrigger class="h-8 w-[70px]">
                                        <SelectValue placeholder="5" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="option in pageSizeOptions" :key="option" :value="option">
                                            {{ option }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $t('Showing') }} {{ Math.min((currentPage - 1) * itemsPerPage + 1,
                                    filteredAndSortedActivities.length) }}
                                {{ $t('to') }} {{ Math.min(currentPage * itemsPerPage,
                                    filteredAndSortedActivities.length)
                                }}
                                {{ $t('of') }} {{ filteredAndSortedActivities.length }} {{ $t('entries') }}
                            </div>

                            <div class="flex items-center space-x-2">
                                <Button variant="outline" size="sm" :disabled="currentPage === 1" @click="prevPage">
                                    <ChevronLeft class="w-4 h-4" />
                                </Button>

                                <div class="flex space-x-1">
                                    <template v-for="page in visiblePages" :key="page">
                                        <Button v-if="page !== -1" variant="outline" size="sm"
                                            :class="{ 'bg-gray-100 dark:bg-gray-700': currentPage === page }"
                                            @click="goToPage(page)">
                                            {{ page }}
                                        </Button>
                                        <span v-else class="flex items-center px-3 text-sm text-gray-500">
                                            ...
                                        </span>
                                    </template>
                                </div>

                                <Button variant="outline" size="sm" :disabled="currentPage === totalPages"
                                    @click="nextPage">
                                    <ChevronRight class="w-4 h-4" />
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </CollapsibleContent>
            </Collapsible>
        </Card>

        <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
            <DialogContent class="w-full max-w-[70vw] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>{{ $t('Log Activity Details') }}</DialogTitle>
                    <DialogDescription>
                        {{ $t('Viewing log details for the selected activity') }}
                    </DialogDescription>
                    <DialogDescription>
                        {{ selectedActivity?.causer_name || 'System' }} has {{ selectedActivity?.description }} at
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
