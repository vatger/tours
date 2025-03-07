<script setup lang="ts">
const props = defineProps();
</script>

<template>
    <VSheet v-bind="props">
        <slot />
    </VSheet>
</template>
