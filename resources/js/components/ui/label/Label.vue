<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;

    return delegated;
});
</script>

<template>
    <VLabel
        v-bind="delegatedProps"
        :class="['text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70', props.class]"
    >
        <slot />
    </VLabel>
</template>
