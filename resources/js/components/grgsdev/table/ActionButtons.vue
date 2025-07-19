<template>
    <div class="flex items-center space-x-2">
        <template v-if="!isDeleted">
            <Tooltip v-if="canView">
                <TooltipTrigger as-child>
                    <Button variant="ghost" size="icon" @click="$emit('view')" class="text-blue-600">
                        <Eye class="w-4 h-4" />
                    </Button>
                </TooltipTrigger>
                <TooltipContent>
                    <p>{{ $t('View') }}</p>
                </TooltipContent>
            </Tooltip>

            <Tooltip v-if="canEdit">
                <TooltipTrigger as-child>
                    <Button variant="ghost" size="icon" @click="$emit('edit')" class="text-amber-600" :preserve-scroll="true">
                        <Pencil class="w-4 h-4" />
                    </Button>
                </TooltipTrigger>
                <TooltipContent>
                    <p>{{ $t('Edit') }}</p>
                </TooltipContent>
            </Tooltip>

            <Tooltip v-if="canDelete">
                <TooltipTrigger as-child>
                    <Button variant="ghost" size="icon" @click="$emit('delete')" class="text-red-600">
                        <Trash2 class="w-4 h-4" />
                    </Button>
                </TooltipTrigger>
                <TooltipContent>
                    <p>{{ $t('Delete') }}</p>
                </TooltipContent>
            </Tooltip>
        </template>

        <template v-else>
            <Tooltip v-if="canView">
                <TooltipTrigger as-child>
                    <Button variant="ghost" size="icon" @click="$emit('view')" class="text-blue-600">
                        <Eye class="w-4 h-4" />
                    </Button>
                </TooltipTrigger>
                <TooltipContent>
                    <p>{{ $t('View') }}</p>
                </TooltipContent>
            </Tooltip>

            <Tooltip v-if="canRestore">
                <TooltipTrigger as-child>
                    <Button variant="ghost" size="icon" @click="$emit('restore')" class="text-amber-600">
                        <RotateCcw class="w-4 h-4" />
                    </Button>
                </TooltipTrigger>
                <TooltipContent>
                    <p>{{ $t('Restore') }}</p>
                </TooltipContent>
            </Tooltip>

            <Tooltip v-if="canForceDelete">
                <TooltipTrigger as-child>
                    <Button variant="ghost" size="icon" @click="$emit('forceDelete')" class="text-red-600">
                        <Trash2 class="w-4 h-4" />
                    </Button>
                </TooltipTrigger>
                <TooltipContent>
                    <p>{{ $t('Delete Permanently') }}</p>
                </TooltipContent>
            </Tooltip>
        </template>
    </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Eye, Pencil, Trash2, RotateCcw } from 'lucide-vue-next';
import { Tooltip, TooltipTrigger, TooltipContent } from '@/components/ui/tooltip';
defineProps<{
    isDeleted?: boolean;
    canView?: boolean;
    canEdit?: boolean;
    canDelete?: boolean;
    canRestore?: boolean;
    canForceDelete?: boolean;
}>();

defineEmits(['view', 'edit', 'delete', 'restore', 'forceDelete']);
</script>
