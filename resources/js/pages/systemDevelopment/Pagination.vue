<template>
    <div v-if="computedLinks.length > 3">
        <div class="flex flex-wrap justify-center -mb-1">
            <template v-for="(link, key) in computedLinks" :key="key">
                <div v-if="link.url === null"
                    class="px-3 py-2 mb-1 mr-1 text-sm leading-4 text-gray-400 border rounded select-none sm:px-4 sm:py-3"
                    v-html="link.label" />

                <Link v-else
                    class="px-3 py-2 mb-1 mr-1 text-sm leading-4 border rounded cursor-pointer sm:px-4 sm:py-3 hover:bg-gray-100 dark:hover:bg-gray-700 focus:border-indigo-500 focus:text-indigo-500"
                    :class="{
                        'bg-blue-700 text-white dark:bg-blue-600': link.active,
                        'text-gray-400 cursor-not-allowed': (link.label.includes('Previous') || link.label.includes('Anterior')) && !hasPreviousPage,
                        'text-gray-400 cursor-not-allowed': (link.label.includes('Next') || link.label.includes('Próxima')) && !hasNextPage,
                    }" :href="link.url ?? '#'" v-html="link.label" />
            </template>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';

type PaginatorLink = {
    url: string | null;
    label: string;
    active: boolean;
};

const props = defineProps<{
    links: PaginatorLink[];
}>();

const hasPreviousPage = computed(() => props.links[0]?.url !== null);
const hasNextPage = computed(() => props.links[props.links.length - 1]?.url !== null);

// --- LÓGICA DE RESPONSIVIDADE ---
const isMobile = ref(false);

const onResize = () => {
    isMobile.value = window.innerWidth < 768; // O breakpoint 'md' do Tailwind é 768px
};

onMounted(() => {
    onResize(); // Verifica o tamanho inicial
    window.addEventListener('resize', onResize);
});

onUnmounted(() => {
    window.removeEventListener('resize', onResize);
});
// --- FIM DA LÓGICA DE RESPONSIVIDADE ---


const computedLinks = computed(() => {
    // Define a "janela" de links baseada no tamanho da tela
    const window = isMobile.value ? 0 : 1;

    const totalPageLinks = props.links.length;
    if (totalPageLinks <= 7) {
        return props.links;
    }

    const currentPage = parseInt(props.links.find(link => link.active)?.label ?? '1');
    const lastPage = parseInt(props.links[totalPageLinks - 2].label);

    const numericLinks: PaginatorLink[] = [];
    let lastAddedPage = 0;

    for (let i = 1; i <= lastPage; i++) {
        const isFirstPage = i === 1;
        const isLastPage = i === lastPage;
        const inWindow = i >= currentPage - window && i <= currentPage + window;

        if (isFirstPage || isLastPage || inWindow) {
            if (lastAddedPage > 0 && i - lastAddedPage > 1) {
                numericLinks.push({ url: null, label: '...', active: false });
            }
            numericLinks.push(props.links[i]);
            lastAddedPage = i;
        }
    }

    return [
        props.links[0],
        ...numericLinks,
        props.links[totalPageLinks - 1]
    ];
});
</script>
