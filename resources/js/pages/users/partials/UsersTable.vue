<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3'
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
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { ChevronUp, ChevronDown, MoreHorizontal } from 'lucide-vue-next'
import Pagination from '@/components/Pagination.vue'
import { computed } from 'vue'
import { trans } from 'laravel-vue-i18n';
import { Badge } from '@/components/ui/badge'
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

import { useAuth } from '@/composables/useAuth'

const { hasPermission } = useAuth()
const props = defineProps<{
    users: any
    sort?: string | null
    search?: string | null
    per_page?: number | null
    only_trashed?: boolean | null
}>()

// const props = defineProps<{
//     users: any
//     sort?: string | null // Format: 'name' or '-name' or 'name,-email' etc.
// }>()
// Permissões


const hasDeleted = computed(() => props.users.data.some(user => user.deleted_at?.short !== null));

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
        label: trans('validation.attributes.name'),
        sortable: true
    },
    {
        key: 'email',
        label: trans('validation.attributes.email'),
        sortable: true,
        class: 'hidden lg:table-cell'
    },
    {
        key: 'username',
        label: trans('validation.attributes.username'),
        sortable: true,
        class: 'hidden lg:table-cell'
    },
    {
        key: 'nickname',
        label: trans('validation.attributes.nickname'),
        sortable: true,
        class: 'hidden lg:table-cell'
    },
    {
        key: 'is_active',
        label: trans('validation.attributes.status'),
        sortable: true,
        class: 'hidden lg:table-cell'
    },
    {
        key: 'approved_status',
        label: trans('validation.attributes.situation'),
        sortable: true,
        class: 'hidden lg:table-cell'
    },
    {
        key: 'created_at',
        label: trans('validation.attributes.created_at'),
        sortable: true,
        class: 'hidden lg:table-cell'
    },
    {
        key: 'updated_at',
        label: trans('validation.attributes.updated_at'),
        sortable: true,
        class: 'hidden xl:table-cell'
    },
    ...(hasDeleted.value ? [{
        key: 'deleted_at',
        label: trans('validation.attributes.deleted_at'),
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
    router.get(route('users.index'), {
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
const toggleIsActive = (userId: number | string) => {
    router.post(
        route('users.toggle.is.active', { user_id: userId }),
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

const showApproveDialog = ref(false);
const approveDialogUser = ref<any>(null);

const approveForm = useForm({
    status: '',
    reason: ''
});
const approvedStatusOptions = [
    { value: '1', label: 'ANALISYS' },
    { value: '2', label: 'APPROVED' },
    { value: '3', label: 'UNAPPROVED' },
    { value: '4', label: 'BLOCKED' },
    { value: '5', label: 'CANCELED' },
];

// Abrir o dialog e preencher o usuário e status atual
function openApproveDialog(user: any) {
    approveDialogUser.value = user;
    approveForm.status = user.approved_status;
    approveForm.reason = '';
    approveForm.clearErrors();
    showApproveDialog.value = true;
}

// Submeter a alteração
function submitApproveStatus() {
    approveForm.post(
        route('users.toggle.approve.status', {
            user_id: approveDialogUser.value.id,
            approve_status: approveForm.status
        }),
        {
            preserveScroll: true,
            onSuccess: () => {
                showApproveDialog.value = false;
                approveForm.reset();
            }
        }
    );
}

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
                                <TableHead class="flex-1 min-w-[50px]">{{ $t('Avatar') }}</TableHead>
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
                            <div v-if="users.data.length === 0" class="p-4 text-center text-muted-foreground">
                                {{ $t('No users found') }}
                            </div>
                            <div v-else class="block">

                                <TableRow v-for="user in users.data" :key="user.id" class="flex">
                                    <TableCell class="bg-background flex-1 min-w-[50px]">
                                        <Avatar class="h-8 w-8">
                                            <AvatarImage :src="user.avatar_url" />
                                            <AvatarFallback>{{ user.name.charAt(0) }}</AvatarFallback>
                                        </Avatar>
                                    </TableCell>
                                    <TableCell class="flex-1 min-w-[150px] whitespace-normal break-words">
                                        <div class="flex items-center gap-2">
                                            {{ user.name }}
                                            <Badge v-if="user.deleted_at?.short" variant="destructive" class="text-xs">
                                                {{ $t('Deleted') }}
                                            </Badge>
                                        </div>
                                    </TableCell>

                                    <TableCell
                                        class="hidden lg:table-cell flex-1 min-w-[150px] max-w-[220px] truncate overflow-ellipsis overflow-hidden whitespace-nowrap"
                                        style="overflow-wrap: anywhere;" :title="user.email">
                                        {{ user.email }}
                                    </TableCell>
                                    <TableCell
                                        class="hidden lg:table-cell flex-1 min-w-[120px] whitespace-normal break-words">
                                        {{ user.username }}
                                    </TableCell>
                                    <TableCell
                                        class="hidden lg:table-cell flex-1 min-w-[150px] whitespace-normal break-words">
                                        {{ user.nickname }}
                                    </TableCell>
                                    <TableCell v-if="!user.deleted_at?.short && hasPermission('users.toggle.is.active')"
                                        class="hidden lg:table-cell flex-1 min-w-[150px] whitespace-normal break-words">
                                        <Badge :variant="user.is_active ? 'default' : 'destructive'"
                                            class="text-xs cursor-pointer select-none" @click="toggleIsActive(user.id)"
                                            :title="$t('Toggle status')">
                                            {{ user.is_active_text }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell v-else
                                        class="hidden lg:table-cell flex-1 min-w-[150px] whitespace-normal break-words">
                                        <Badge :variant="user.is_active ? 'default' : 'destructive'" class="text-xs">
                                            {{ user.is_active_text }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell
                                        v-if="!user.deleted_at?.short && hasPermission('users.toggle.approve.status')"
                                        class="hidden lg:table-cell flex-1 min-w-[150px] whitespace-normal break-words">
                                        <Badge :variant="user.approved_status === '2' ? 'default' : 'secondary'"
                                            class="text-xs cursor-pointer select-none"
                                            :title="$t('Change approved status')" @click="openApproveDialog(user)">
                                            {{ user.approved_status_text }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell v-else
                                        class="hidden lg:table-cell flex-1 min-w-[150px] whitespace-normal break-words">
                                        <Badge :variant="user.approved_status === '2' ? 'default' : 'secondary'"
                                            class="text-xs">
                                            {{ user.approved_status_text }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell
                                        class="hidden lg:table-cell flex-1 min-w-[150px] whitespace-normal break-words">
                                        {{ user.created_at.short_with_time }}
                                    </TableCell>
                                    <TableCell
                                        class="hidden xl:table-cell flex-1 min-w-[150px] whitespace-normal break-words">
                                        {{ user.updated_at.short_with_time }}
                                    </TableCell>
                                    <TableCell v-if="hasDeleted"
                                        class="hidden xl:table-cell flex-1 min-w-[150px] whitespace-normal break-words">
                                        {{ user.deleted_at?.short || '-' }}
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
                                                <DropdownMenuItem v-if="hasPermission('users.show')"
                                                    @click="emit('view', user)">
                                                    {{ $t('actions.view') }}
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    v-if="!user.deleted_at?.short && hasPermission('users.edit')"
                                                    @click="emit('edit', user)">
                                                    {{ $t('actions.edit') }}
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    v-if="user.deleted_at?.short && hasPermission('users.trashed.restore')"
                                                    @click="emit('restore', user)">
                                                    {{ $t('actions.restore') }}
                                                </DropdownMenuItem>

                                                <DropdownMenuSeparator></DropdownMenuSeparator>
                                                <DropdownMenuItem
                                                    v-if="user.deleted_at?.short && hasPermission('users.trashed.delete')"
                                                    class="text-destructive" @click="emit('force-delete', user)">
                                                    {{ $t('actions.delete_permanently') }}
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    v-if="!user.deleted_at?.short && hasPermission('users.delete')"
                                                    class="text-destructive" @click="emit('delete', user)">
                                                    {{ $t('actions.delete') }}
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
                    {{ $t('Showing') }} {{ users.meta.from }} {{ $t('to') }} {{ users.meta.to }} {{ $t('of') }} {{
                        users.meta.total }} {{ $t('results') }}
                </div>
                <Pagination :links="users.meta.links" />
            </div>
        </div>

        <Dialog v-model:open="showApproveDialog">
            <DialogContent class="max-w-md w-full">
                <DialogHeader>
                    <DialogTitle>{{ $t('Change Approved Status') }}</DialogTitle>
                </DialogHeader>
                <!-- Nome do usuário -->
                <div class="mb-2 flex items-center gap-2">
                    <Avatar v-if="approveDialogUser?.avatar_url" class="h-8 w-8">
                        <AvatarImage :src="approveDialogUser.avatar_url" />
                        <AvatarFallback>{{ approveDialogUser.name?.charAt(0) }}</AvatarFallback>
                    </Avatar>
                    <span class="font-semibold text-lg">{{ approveDialogUser?.name }}</span>
                    <span v-if="approveDialogUser?.email" class="text-muted-foreground text-xs">({{
                        approveDialogUser.email }})</span>
                    <span>
                        <Badge :variant="approveDialogUser.is_active ? 'default' : 'destructive'" class="text-xs">
                            {{ approveDialogUser.is_active_text }}
                        </Badge>
                    </span>
                    <span>
                        <Badge :variant="approveDialogUser.approved_status === '2' ? 'default' : 'secondary'"
                            class="text-xs" :title="$t('Change approved status')">
                            {{ approveDialogUser.approved_status_text }}
                        </Badge>
                    </span>

                </div>

                <form @submit.prevent="submitApproveStatus" class="space-y-4">
                    <div>
                        <label class="block mb-1 font-medium">{{ $t('New Status') }}</label>
                        <select v-model="approveForm.status" class="w-full border rounded p-2">
                            <option
                                v-for="option in approvedStatusOptions.filter(opt => opt.value !== approveDialogUser?.approved_status)"
                                :key="option.value" :value="option.value">
                                {{ $t(option.label) }}
                            </option>
                        </select>
                        <div v-if="approveForm.errors.status" class="text-red-500 text-xs mt-1">
                            {{ approveForm.errors.status }}
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">{{ $t('Reason') }}</label>
                        <textarea v-model="approveForm.reason" class="w-full border rounded p-2" rows="2" required />
                        <div v-if="approveForm.errors.reason" class="text-red-500 text-xs mt-1">
                            {{ approveForm.errors.reason }}
                        </div>
                    </div>
                    <div class="flex justify-end gap-2">
                        <Button type="button" variant="outline" @click="showApproveDialog = false">{{
                            $t('actions.cancel')
                        }}</Button>
                        <Button v-if="hasPermission('users.toggle.approve.status')" type="submit" variant="default"
                            :disabled="approveForm.processing">{{ $t('actions.confirm')
                            }}</Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
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
