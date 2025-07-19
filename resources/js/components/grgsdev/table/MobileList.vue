<template>
    <div class="space-y-4 lg:hidden">
        <div v-for="item in items" :key="item.id" class="p-4 border rounded-lg shadow-sm">
            <div class="flex items-start gap-4">
                <slot name="avatar" :item="item"></slot>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <slot name="title" :item="item"></slot>
                        <slot name="meta" :item="item"></slot>
                    </div>
                    <slot name="content" :item="item"></slot>

                    <div class="grid grid-cols-2 gap-2 mt-2 text-sm">
                        <slot name="additional-info" :item="item"></slot>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <slot name="actions" :item="item"></slot>
            </div>
        </div>

        <div v-if="items.length === 0" class="p-4 text-center border rounded-lg">
            {{ emptyMessage || $t('No results found') }}
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    items: any[];
    emptyMessage?: string;
}>();
</script>
