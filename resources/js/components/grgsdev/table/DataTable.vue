<template>
    <div class="max-h-[300px] sm:max-h-[400px] lg:max-h-[670px] overflow-y-auto">
        <Table class="border-separate border-spacing-0">
            <TableHeader class="sticky top-0 z-20 bg-white shadow-sm dark:bg-gray-800">
                <TableRow>
                    <TableHead v-for="column in columns" :key="column.key"
                        :class="[column.class, column.sortable ? 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700' : '']"
                        @click="column.sortable ? $emit('sort', column.key) : null">
                        <div class="flex items-center space-x-1">
                            <span>{{ column.label }}</span>
                            <span v-if="sorting?.key === column.key">{{ sorting.direction === 'asc' ? '↑' : '↓'
                            }}</span>
                        </div>
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <slot></slot>
            </TableBody>
        </Table>
    </div>
</template>

<script setup lang="ts">
import {
    Table,
    TableBody,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

interface Column {
    readonly key: string;
    readonly label: string;
    readonly sortable?: boolean;
    readonly class?: string;
}

interface Sorting {
    readonly key: string;
    readonly direction: 'asc' | 'desc';
}

defineProps<{
    columns: readonly Column[];
    sorting?: Sorting | null;
}>();
defineEmits(['sort']);
</script>
