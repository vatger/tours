<script setup lang="ts">
const props = defineProps<{
    src: string;
    alt?: string;
}>();
</script>

<template>
    <VImg :src="props.src" :alt="props.alt" class="h-full w-full object-cover" />
</template>
