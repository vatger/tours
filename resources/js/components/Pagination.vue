<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { ChevronLeft, ChevronRight, MoreHorizontal } from 'lucide-vue-next'


defineProps<{
    links: Array<{
        url: string | null
        label: string
        active: boolean
    }>
}>()

const getPageNumber = (label: string) => {
    if (label.includes('&laquo')) return -1 //previous
    if (label.includes('&raquo')) return -2 //next
    return parseInt(label)
}
</script>

<template>
    <div class="flex items-center justify-between px-2 py-4">
        <div class="flex flex-1 items-center justify-between">
            <div>
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                    <Link v-for="(link, index) in links" :key="index" :href="link.url ?? '#'" preserve-state :class="[
                        link.active
                            ? 'relative z-10 inline-flex items-center bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary'
                            : 'relative inline-flex items-center px-4 py-2 text-sm font-medium text-muted-foreground ring-1 ring-inset ring-input hover:bg-accent focus:z-20 focus:outline-offset-0',
                        index === 0 ? 'rounded-l-md' : '',
                        index === links.length - 1 ? 'rounded-r-md' : '',
                    ]" :aria-current="link.active ? 'page' : undefined">
                    <template v-if="getPageNumber(link.label) === -1">
                        <span class="sr-only">Anterior</span>
                        <ChevronLeft class="h-5 w-5" aria-hidden="true" />
                    </template>
                    <template v-else-if="getPageNumber(link.label) === -2">
                        <span class="sr-only">Próximo</span>
                        <ChevronRight class="h-5 w-5" aria-hidden="true" />
                    </template>
                    <template v-else-if="link.label.includes('...')">
                        <MoreHorizontal class="h-5 w-5" aria-hidden="true" />
                    </template>
                    <template v-else>
                        {{ link.label }}
                    </template>
                    </Link>
                </nav>
            </div>
        </div>
    </div>
</template>
