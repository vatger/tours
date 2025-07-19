<!-- components/ConfirmDialog.vue -->
<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { trans } from 'laravel-vue-i18n';

// Definir os tipos de variantes permitidas
type ButtonVariant = 'destructive' | 'default' | 'outline' | 'secondary' | 'ghost' | 'link';

const props = defineProps({
    open: {
        type: Boolean,
        required: true
    },
    title: {
        type: String,
        default: () => trans('Confirm action')
    },
    description: {
        type: String,
        required: true
    },
    cancelText: {
        type: String,
        default: () => trans('Cancel')
    },
    confirmText: {
        type: String,
        default: () => trans('Confirm')
    },
    variant: {
        type: String as () => ButtonVariant,
        default: 'destructive',
        validator: (value: string) => {
            return ['destructive', 'default', 'outline', 'secondary', 'ghost', 'link'].includes(value);
        }
    }
});

const emit = defineEmits(['update:open', 'confirm', 'cancel']);

const handleCancel = () => {
    emit('cancel');
    emit('update:open', false);
};

const handleConfirm = () => {
    emit('confirm');
    emit('update:open', false);
};
</script>

<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>
                    {{ description }}
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="handleCancel">
                    {{ cancelText }}
                </Button>
                <Button :variant="variant" @click="handleConfirm">
                    {{ confirmText }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
