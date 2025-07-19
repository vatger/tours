<script setup lang="ts">
import { Label } from '@/components/ui/label';
import LabelRequired from '../label/LabelRequired.vue';
import InputError from '@/components/InputError.vue';

defineProps<{
    label: string
    required?: boolean
    error?: string
    description?: string
}>()
</script>

<template>
    <div class="grid gap-2">
        <!-- Container do label -->
        <div v-if="required" class="flex items-center justify-between">
            <LabelRequired :required="required">{{ label }}</LabelRequired>
        </div>
        <div v-else class="flex items-center justify-between">
            <Label>{{ label }}</Label>
        </div>
        <slot />

        <span v-if="description" class="text-xs text-muted-foreground -mt-1.5">
            {{ description }}
        </span>

        <!-- Mensagem de erro -->
        <InputError v-if="error" :message="error" />
    </div>
</template>
