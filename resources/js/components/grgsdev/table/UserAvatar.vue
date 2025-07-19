<template>
    <div class="flex justify-center">
        <img v-if="src" :src="src" :alt="alt"
            class="object-cover w-10 h-10 border border-gray-200 rounded-full dark:border-gray-600">
        <div v-else
            class="flex items-center justify-center w-10 h-10 text-lg font-medium text-gray-600 bg-gray-200 rounded-full dark:bg-gray-700 dark:text-gray-300">
            {{ getInitials(props.name) }}
        </div>
    </div>
</template>

<script setup lang="ts">
import { getInitials } from '@/composables/useInitials';

const props = defineProps<{
    src?: string;
    alt?: string;
    name?: string;
}>();

</script>
