<script setup lang="ts">
import {
    Select,
    SelectContent,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem } from '@/components/ui/command'
import { CheckIcon, XIcon, Loader2 } from 'lucide-vue-next'
import { computed, nextTick, ref } from 'vue'
import { Button } from '@/components/ui/button'

interface Value {
    id: number | string
    name: string
    [key: string]: any
}

interface Props {
    values: Value[]
    modelValue?: number | number[] | string | null
    placeholder?: string
    multiple?: boolean
    searchPlaceholder?: string
    emptyText?: string
    loading?: boolean
    clearable?: boolean
    selectAll?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Select an item',
    searchPlaceholder: 'Search...',
    emptyText: 'No items found.',
    multiple: false,
    loading: false,
    clearable: false,
    selectAll: false,
})

const emit = defineEmits(['update:modelValue'])

const searchQuery = ref('')
const open = ref(false)

const itemSlot = defineSlots<{
    item?: (props: { item: Value }) => any
    selected?: (props: { item: Value }) => any
}>()

const filteredValues = computed(() => {
    if (!searchQuery.value) return props.values
    return props.values.filter(item =>
        item.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
})

const selectedItems = computed(() => {
    if (!props.modelValue) return []
    if (Array.isArray(props.modelValue)) {
        return props.values.filter(item => (props.modelValue as number[]).includes(item.id))
    } else {
        return props.values.filter(item => item.id === props.modelValue)
    }
})

const displayValue = computed(() => {
    if (props.multiple) {
        const count = selectedItems.value.length
        if (count === 0) return props.placeholder
        if (count === 1) return selectedItems.value[0].name
        return `${count} selected`
    }
    return selectedItems.value[0]?.name || props.placeholder
})

function handleSelect(id: number) {
    if (props.multiple) {
        const currentValue = Array.isArray(props.modelValue) ? [...props.modelValue] : []
        const newValue = currentValue.includes(id)
            ? currentValue.filter(item => item !== id)
            : [...currentValue, id]
        emit('update:modelValue', newValue)
    } else {
        emit('update:modelValue', id)
        open.value = false
    }
}

function isSelected(id: number) {
    if (!props.modelValue) return false
    if (Array.isArray(props.modelValue)) {
        return (props.modelValue as number[]).includes(id)
    }
    return props.modelValue === id
}

function clearSelection(e: Event) {
    e.preventDefault()
    e.stopPropagation()
    e.stopImmediatePropagation() // Adicione esta linha para garantir que o evento não propague

    // Fecha o dropdown
    open.value = false

    // Limpa a seleção
    const newValue = props.multiple ? [] : null
    emit('update:modelValue', newValue)

    // Limpa a busca
    searchQuery.value = ''

    // Força uma atualização imediata
    nextTick(() => {
        if (open.value) {
            open.value = false
        }
    })
}
// function clearSelection() {
//     emit('update:modelValue', props.multiple ? [] : null)
// }

function toggleSelectAll() {
    if (!props.multiple) return

    const allSelected = filteredValues.value.every(item =>
        Array.isArray(props.modelValue) && (props.modelValue as number[]).includes(item.id)
    )

    if (allSelected) {
        const visibleIds = filteredValues.value.map(item => item.id)
        const newValue = Array.isArray(props.modelValue)
            ? (props.modelValue as number[]).filter(id => !visibleIds.includes(id))
            : []
        emit('update:modelValue', newValue)
    } else {
        const visibleIds = filteredValues.value.map(item => item.id)
        const currentValue = Array.isArray(props.modelValue) ? [...props.modelValue] : []
        const newIds = visibleIds.filter(id => !currentValue.includes(id))
        emit('update:modelValue', [...currentValue, ...newIds])
    }
}

const allSelected = computed(() => {
    if (!props.multiple || !filteredValues.value.length) return false
    return filteredValues.value.every(item =>
        Array.isArray(props.modelValue) && (props.modelValue as number[]).includes(item.id)
    )
})
</script>


<template>
    <Select v-model:open="open">
        <SelectTrigger class="relative"
            @click="(e) => { if (props.clearable && selectedItems.length > 0) e.preventDefault() }">
            <SelectValue>
                <div class="flex items-center gap-2 overflow-hidden">
                    <span class="truncate">{{ displayValue }}</span>
                    <div v-if="props.loading" class="ml-2">
                        <Loader2 class="w-4 h-4 opacity-50 animate-spin" />
                    </div>
                </div>
            </SelectValue>

            <!-- Botão para limpar seleção - modificado -->
            <Button v-if="props.clearable && (selectedItems.length > 0 || (modelValue !== null && !multiple))"
                variant="ghost" size="sm" class="absolute w-4 h-4 p-0 opacity-50 right-8 hover:opacity-100"
                @click.stop="clearSelection" @mousedown.stop @pointerdown.stop>
                <XIcon class="w-3 h-3" />
            </Button>
        </SelectTrigger>

        <SelectContent>
            <Command>
                <CommandInput v-model="searchQuery" :placeholder="searchPlaceholder" class="h-9" />

                <CommandEmpty v-if="!props.loading">
                    {{ emptyText }}
                </CommandEmpty>

                <CommandGroup>
                    <!-- Opção "Select All" para multiselect -->
                    <CommandItem v-if="props.multiple && props.selectAll && filteredValues.length > 0"
                        value="select-all" @select="toggleSelectAll">
                        <div class="flex items-center justify-between w-full">
                            <span>Select {{ allSelected ? 'None' : 'All' }}</span>
                            <CheckIcon v-if="allSelected" class="w-4 h-4 text-primary" />
                        </div>
                    </CommandItem>

                    <!-- Itens da lista -->
                    <CommandItem v-for="item in filteredValues" :key="item.id" :value="item.id.toString()"
                        @select="handleSelect(item.id)">
                        <!-- Slot personalizado para o item ou visualização padrão -->
                        <template v-if="itemSlot.item">
                            <component :is="itemSlot.item({ item })" />
                        </template>
                        <template v-else>
                            <div class="flex items-center justify-between w-full">
                                <div>
                                    <span class="font-medium">{{ item.name }}</span>
                                </div>
                                <CheckIcon v-if="isSelected(item.id)" class="w-4 h-4 text-primary" />
                            </div>
                        </template>
                    </CommandItem>

                    <!-- Loading state -->
                    <div v-if="props.loading" class="flex items-center justify-center p-4">
                        <Loader2 class="w-4 h-4 animate-spin" />
                    </div>
                </CommandGroup>
            </Command>
        </SelectContent>
    </Select>
</template>

<style scoped>
[cmdk-item] {
    padding: 0.5rem 1rem;
    cursor: pointer;
}

[cmdk-item][aria-selected='true'] {
    background-color: hsl(var(--accent));
    color: hsl(var(--accent-foreground));
}

[cmdk-item]:hover {
    background-color: hsl(var(--accent));
    color: hsl(var(--accent-foreground));
}
</style>
