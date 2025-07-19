<script setup lang="ts">
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
    DropdownMenuSeparator // Adicione separadores se necessário
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { MoreHorizontal } from 'lucide-vue-next';

// Interface para tipagem das ações
interface Action {
    label: string;
    icon?: any;
    color?: string;
    emitEvent: string;
    separator?: boolean; // Adicione suporte a separadores
}

defineProps<{
    actions: Action[];
}>();

// Declare os eventos explicitamente
const emit = defineEmits([
    'view',
    'edit',
    'delete',
    'restore',
    'forceDelete'
    // Adicione outros eventos necessários
]);
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" class="w-8 h-8 p-0">
                <span class="sr-only">Open menu</span>
                <MoreHorizontal class="w-4 h-4" />
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end">
            <template v-for="(action, index) in actions" :key="index">
                <!-- Separador entre grupos -->
                <DropdownMenuSeparator v-if="action.separator" />

                <!-- Item do menu -->
                <DropdownMenuItem :class="action.color" @click="emit(action.emitEvent)">
                    <component v-if="action.icon" :is="action.icon" class="w-4 h-4 mr-2" />
                    {{ action.label }}
                </DropdownMenuItem>
            </template>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
