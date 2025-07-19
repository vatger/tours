<template>
    <Pagination :total="total" :items-per-page="perPage" :default-page="currentPage" :sibling-count="1" show-edges>
        <PaginationList v-slot="{ items }" class="flex items-center gap-1">
            <PaginationFirst @click="$emit('first')" />
            <PaginationPrev @click="$emit('prev')" />

            <template v-for="(item, index) in items">
                <PaginationListItem v-if="item.type === 'page'" :key="index" :value="item.value" as-child>
                    <Button class="w-10 h-10 p-0" :variant="item.value === currentPage ? 'default' : 'outline'"
                        @click="$emit('page', item.value)">
                        {{ item.value }}
                    </Button>
                </PaginationListItem>
                <PaginationEllipsis v-else :key="item.type" :index="index" />
            </template>

            <PaginationNext @click="$emit('next')" />
            <PaginationLast @click="$emit('last')" />
        </PaginationList>
    </Pagination>
</template>

<script setup lang="ts">
import {
    Pagination,
    PaginationEllipsis,
    PaginationFirst,
    PaginationLast,
    PaginationList,
    PaginationListItem,
    PaginationNext,
    PaginationPrev,
} from '@/components/ui/pagination';
import { Button } from '@/components/ui/button';

defineProps<{
    total: number;
    perPage: number;
    currentPage: number;
}>();

defineEmits(['first', 'prev', 'page', 'next', 'last']);
</script>
