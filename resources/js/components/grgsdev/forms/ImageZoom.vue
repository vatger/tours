<template>
    <Dialog :open="modelValue" @update:open="$emit('update:modelValue', $event)">
        <!-- Wrapper para herdar atributos -->
        <div :aria-describedby="'image-zoom-desc'">
            <DialogContent class="sm:max-w-[90vw] sm:max-h-[90vh]">
                <DialogHeader>
                    <DialogTitle>{{ title }}</DialogTitle>
                    <p id="image-zoom-desc" class="sr-only">
                        {{ description || `${$t('Zoomed view of')} ${alt || title || $t('the image')}` }}
                    </p>
                </DialogHeader>

                <div class="flex items-center justify-center h-full max-h-[80vh]">
                    <img v-if="src" :src="src" :alt="alt" class="object-contain max-w-full max-h-full rounded-md" />
                    <div v-else class="flex items-center justify-center w-full h-full text-gray-500">
                        {{ $t('No image available') }}
                    </div>
                </div>

                <DialogFooter>
                    <Button @click="$emit('update:modelValue', false)">
                        {{ $t('Close') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </div>
    </Dialog>
</template>

<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

interface Props {
    modelValue: boolean;
    src?: string | null;
    alt?: string;
    title?: string;
    description?: string;
}

const props = withDefaults(defineProps<Props>(), {
    alt: '',
    title: '',
    description: ''
});

defineEmits(['update:modelValue']);
</script>
