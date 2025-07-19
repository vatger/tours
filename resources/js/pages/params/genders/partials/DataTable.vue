<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { ChevronUp, ChevronDown, MoreHorizontal } from 'lucide-vue-next'
import Pagination from '@/components/Pagination.vue'
import { computed } from 'vue'
import { trans } from 'laravel-vue-i18n';
import { Badge } from '@/components/ui/badge'
import { useAuth } from '@/composables/useAuth'

const { hasPermission } = useAuth()
const props = defineProps<{
    genders: any
    sort?: string | null
    search?: string | null
    per_page?: number | null
    only_trashed?: boolean | null
}>()
const hasDeleted = computed(() => props.genders.data.some(gender => gender.deleted_at?.short !== null));
const emit = defineEmits(['edit', 'view', 'delete', 'restore', 'force-delete'])
interface Column {
    key: string;
    label: string;
    sortable?: boolean;
    class?: string;
}
const tableColumns = computed<Column[]>(() => [
    {
        key: 'name',
        label: trans('Name'),
        sortable: true
    },

    {
        key: 'is_active',
        label: trans('Status'),
        sortable: true,
        class: 'hidden lg:table-cell'
    },

    {
        key: 'created_at',
        label: trans('Created'),
        sortable: true,
        class: 'hidden lg:table-cell'
    },
    {
        key: 'updated_at',
        label: trans('Updated'),
        sortable: true,
        class: 'hidden xl:table-cell'
    },
    ...(hasDeleted.value ? [{
        key: 'deleted_at',
        label: trans('Deleted'),
        sortable: true,
        class: 'hidden xl:table-cell'
    }] : []),
]);
const currentSorts = computed(() => {
    if (!props.sort || typeof props.sort !== 'string') return []
    return props.sort.split(',')
})
// Check if a column is sorted and its direction
const getSortDirection = (column: string) => {
    const sort = currentSorts.value.find(s =>
        s === column || s === `-${column}`
    )
    if (!sort) return null
    return sort.startsWith('-') ? 'desc' : 'asc'
}
const handleSort = (column: string) => {
    const sorts = [...currentSorts.value];
    const idx = sorts.findIndex(s => s === column || s === `-${column}`);
    if (idx === -1) {
        // Adiciona novo sort ASC no início (prioridade máxima)
        sorts.push(column);
    } else if (sorts[idx] === column) {
        // ASC -> DESC
        sorts[idx] = `-${column}`;
    } else {
        // DESC -> remove
        sorts.splice(idx, 1);
    }
    const sortParam = sorts.length ? sorts.join(',') : undefined;
    router.get(route('genders.index'), {
        filter: {
            Search: props.search,
            ...(props.only_trashed ? { OnlyTrashed: true } : {})
        },
        sort: sortParam, // Keep current sort
        per_page: props.per_page
    }, {
        preserveState: true,
        replace: true
    });
};
const getSortIcon = (column: string) => {
    const direction = getSortDirection(column)
    return direction === 'asc' ? ChevronUp
        : direction === 'desc' ? ChevronDown
            : null
}
const toggleIsActive = (genderId: number | string) => {
    router.post(
        route('genders.toggle.is.active', { gender_id: genderId }),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                // Opcional: mostrar um toast ou feedback visual
            },
            onError: () => {
                // Opcional: tratar erro
            }
        }
    );
};
</script>

<template>
    <div class="space-y-4 p-2">
        <!-- Tabela com cabeçalho fixo -->
        <div class="rounded-md border relative overflow-hidden">
            <div class="overflow-x-auto">
                <div class="min-w-full p-4">
                    <Table>
                        <TableHeader class="sticky top-0 z-10 bg-background">
                            <TableRow class="flex">
                                <TableHead class="flex-1 min-w-[50px]">{{ $t('Icon') }}</TableHead>
                                <TableHead v-for="column in tableColumns" :key="column.key" class="flex-1 min-w-[150px]"
                                    :class="[column.class, column.sortable ? 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700' : '']"
                                    @click="column.sortable ? handleSort(column.key) : null">
                                    <div class="flex items-center space-x-1">
                                        {{ column.label }}
                                        <component :is="getSortIcon(column.key)" v-if="getSortIcon(column.key)"
                                            class="h-4 w-4 ml-1" />
                                    </div>
                                </TableHead>
                                <TableHead class="text-right flex-1 min-w-[100px]">{{ $t('Actions') }}</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody class="block overflow-y-auto max-h-[calc(100vh-480px)]">
                            <div v-if="genders.data.length === 0" class="p-4 text-center text-muted-foreground">
                                {{ $t('No data found') }}
                            </div>
                            <div v-else class="block">
                                <TableRow v-for="gender in genders.data" :key="gender.id" class="flex">
                                    <TableCell class="bg-background flex-1 min-w-[50px]">
                                        <Avatar class="h-8 w-8">
                                            <AvatarImage :src="gender.icon_url" />
                                            <AvatarFallback>{{ gender.name.charAt(0) }}</AvatarFallback>
                                        </Avatar>
                                    </TableCell>
                                    <TableCell class="flex-1 min-w-[150px] whitespace-normal break-words">
                                        <div class="flex items-center gap-2">
                                            {{ gender.name_translated }}
                                            <Badge v-if="gender.deleted_at?.short" variant="destructive"
                                                class="text-xs">
                                                {{ $t('Deleted') }}
                                            </Badge>
                                        </div>
                                    </TableCell>
                                    <TableCell
                                        v-if="!gender.deleted_at?.short && hasPermission('genders.toggle.is.active')"
                                        class="hidden lg:table-cell flex-1 min-w-[150px] whitespace-normal break-words">
                                        <Badge :variant="gender.is_active ? 'default' : 'destructive'"
                                            class="text-xs cursor-pointer select-none"
                                            @click="toggleIsActive(gender.id)" :title="$t('Toggle status')">
                                            {{ gender.is_active_text }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell v-else
                                        class="hidden lg:table-cell flex-1 min-w-[150px] whitespace-normal break-words">
                                        <Badge :variant="gender.is_active ? 'default' : 'destructive'" class="text-xs">
                                            {{ gender.is_active_text }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell
                                        class="hidden lg:table-cell flex-1 min-w-[150px] whitespace-normal break-words">
                                        {{ gender.created_at.short_with_time }}
                                    </TableCell>
                                    <TableCell
                                        class="hidden xl:table-cell flex-1 min-w-[150px] whitespace-normal break-words">
                                        {{ gender.updated_at.short_with_time }}
                                    </TableCell>
                                    <TableCell v-if="hasDeleted"
                                        class="hidden xl:table-cell flex-1 min-w-[150px] whitespace-normal break-words">
                                        {{ gender.deleted_at?.short || '-' }}
                                    </TableCell>
                                    <TableCell
                                        class="text-right bg-background flex-1 min-w-[100px] whitespace-normal break-words">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" class="h-8 w-8 p-0">
                                                    <MoreHorizontal class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem v-if="hasPermission('genders.show')"
                                                    @click="emit('view', gender)">
                                                    {{ $t('View') }}
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    v-if="!gender.deleted_at?.short && hasPermission('genders.edit')"
                                                    @click="emit('edit', gender)">
                                                    {{ $t('Edit') }}
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    v-if="gender.deleted_at?.short && hasPermission('genders.trashed.restore')"
                                                    @click="emit('restore', gender)">
                                                    {{ $t('Restore') }}
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator></DropdownMenuSeparator>
                                                <DropdownMenuItem
                                                    v-if="gender.deleted_at?.short && hasPermission('genders.trashed.delete')"
                                                    class="text-destructive" @click="emit('force-delete', gender)">
                                                    {{ $t('Delete Permanently') }}
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    v-if="!gender.deleted_at?.short && hasPermission('genders.delete')"
                                                    class="text-destructive" @click="emit('delete', gender)">
                                                    {{ $t('Delete') }}
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                            </div>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>

        <div class="sticky bottom-0 px-4 py-1 border-t bg-background dark:background mt-1">
            <div class="flex items-center justify-between">
                <div class="text-sm text-muted-foreground">
                    {{ $t('Showing') }} {{ genders.meta.from }} {{ $t('to') }} {{ genders.meta.to }} {{ $t('of') }} {{
                        genders.meta.total }} {{ $t('results') }}
                </div>
                <Pagination :links="genders.meta.links" />
            </div>
        </div>


    </div>
</template>

<style scoped>
/* Estilos para garantir alinhamento perfeito */
.min-w-full {
    min-width: 100%;
}

.Table {
    display: flex;
    flex-direction: column;
    width: 100%;
}

.TableHeader {
    display: flex;
    width: 100%;
}

.TableBody {
    display: flex;
    flex-direction: column;
    width: 100%;
}

.TableRow {
    display: flex;
    width: 100%;
}

.TableHead,
.TableCell {
    flex: 1;
    padding: 0.75rem 1rem;
    text-align: left;
    border-bottom: 1px solid hsl(var(--border));
}

.TableHead.text-right,
.TableCell.text-right {
    text-align: right;
}

/* Garante que o conteúdo não quebre linha */
.TableCell {
    /* white-space: nowrap; */
    /* overflow: hidden; */
    /* text-overflow: ellipsis; */
    white-space: normal !important;
    word-break: break-word;
    overflow: visible;
    text-overflow: unset;
}

/* Ajuste para o avatar */
.TableCell:first-child {
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
