<script setup lang="ts">
const props = defineProps();
</script>

<template>
    <VMenu v-bind="props">
        <slot />
    </VMenu>
</template>
