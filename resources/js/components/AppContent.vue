<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    variant?: 'header' | 'sidebar';
    class?: string;
}

const props = defineProps<Props>();
const className = computed(() => props.class);
</script>

<template>
    <v-main v-if="props.variant === 'sidebar'" :class="className">
        <slot />
    </v-main>
    <v-main v-else class="mx-auto flex h-full w-full max-w-7xl flex-1 flex-col gap-4 rounded-xl" :class="className">
        <slot />
    </v-main>
</template>
