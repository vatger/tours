<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog'

defineProps({
    modelValue: {
        type: Boolean,
        required: true
    },
    title: {
        type: String,
        default: 'Alterações não salvas'
    },
    description: {
        type: String,
        default: 'Você tem alterações não salvas. Deseja realmente sair desta página?'
    },
    confirmText: {
        type: String,
        default: 'Sair sem salvar'
    },
    cancelText: {
        type: String,
        default: 'Continuar editando'
    }
})

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel'])
</script>

<template>
    <Dialog :open="modelValue" @update:open="value => emit('update:modelValue', value)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>
                    {{ description }}
                </DialogDescription>
            </DialogHeader>

            <DialogFooter>
                <Button variant="outline" @click="emit('cancel')">
                    {{ cancelText }}
                </Button>

                <Button variant="destructive" @click="emit('confirm')">
                    {{ confirmText }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
