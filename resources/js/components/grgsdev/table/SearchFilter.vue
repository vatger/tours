<template>
    <div class="relative">
        <Input :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)"
            :placeholder="placeholder" class="w-full pr-8 sm:w-72" />
        <button v-if="modelValue" @click="clear"
            class="absolute text-gray-500 -translate-y-1/2 right-2 top-1/2 hover:text-gray-700">
            <span class="sr-only">Limpar</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>
</template>

<script setup lang="ts">
import { Input } from '@/components/ui/input';

defineProps<{
    modelValue: string;
    placeholder: string;
}>();

const emit = defineEmits(['update:modelValue']);

const clear = () => {
    emit('update:modelValue', '');
};
</script>
