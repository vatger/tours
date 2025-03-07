<script setup lang="ts">
const props = defineProps();
</script>

<template>
    <VDialog v-bind="props">
        <slot />
    </VDialog>
</template>
