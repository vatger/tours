<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import NavigationMenuViewport from './NavigationMenuViewport.vue';

const props = defineProps<{ class?: HTMLAttributes['class'] }>();
</script>

<template>
    <VNavigationDrawer :class="['relative z-10 flex max-w-max flex-1 items-center justify-center', props.class]">
        <slot />
        <NavigationMenuViewport />
    </VNavigationDrawer>
</template>
