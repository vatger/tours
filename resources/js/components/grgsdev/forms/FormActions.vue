<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { LoaderCircle } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
const props = defineProps<{
    isDisabled: boolean
    processing: boolean
    disableReason: string
    cancelRoute?: string
}>()

const back = (visit: string) => {
    router.visit(visit);
};
</script>

<template>
    <div class="p-4 border-t">
        <div class="flex items-center w-full gap-4">
            <TooltipProvider>
                <Tooltip>
                    <TooltipTrigger as-child>
                        <span tabindex="0" class="inline-flex" :class="{ 'cursor-not-allowed': props.isDisabled }">
                            <Button type="submit" :disabled="props.isDisabled">
                                <span v-if="props.processing">
                                    <LoaderCircle class="w-4 h-4 mr-2 animate-spin" />
                                </span>
                                {{ $t('Save') }}
                            </Button>
                        </span>
                    </TooltipTrigger>
                    <TooltipContent v-if="props.isDisabled">
                        <p class="text-xs">{{ props.disableReason }}</p>
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>

            <Button v-if="props.cancelRoute" type="button" variant="secondary" :disabled="props.processing"
                @click="back(props.cancelRoute)" class="ml-auto">
                {{ $t('Cancel') }}
            </Button>
        </div>
    </div>
</template>
