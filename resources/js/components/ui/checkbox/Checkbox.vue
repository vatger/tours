<script setup lang="ts">
import { Check } from 'lucide-vue-next';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <VCheckbox
        v-bind="props"
        :class="[
            'peer size-5 shrink-0 rounded-sm border border-input ring-offset-background focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground data-[state=checked]:border-accent-foreground',
            props.class,
        ]"
    >
        <template #default="{ isChecked }">
            <VIcon v-if="isChecked" class="flex h-full w-full items-center justify-center text-current">
                <Check class="size-3.5 stroke-[3]" />
            </VIcon>
        </template>
    </VCheckbox>
</template>
