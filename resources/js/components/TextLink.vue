<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface Props {
    href: string;
    tabindex?: number;
    method?: string;
    as?: string;
}

defineProps<Props>();
</script>

<template>
    <Link
        :href="href"
        :tabindex="tabindex"
        :method="method"
        :as="as"
        class="underline decoration-neutral-400 underline-offset-2 duration-300 ease-out hover:decoration-neutral-700"
    >
        <slot />
    </Link>
</template>
