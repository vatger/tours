<template>
    <Popover v-model:open="popoverOpen">
        <PopoverTrigger>
            <Button variant="outline" class="justify-start w-full font-normal text-left sm:w-48"
                :class="!modelValue && 'text-muted-foreground'">
                <CalendarIcon class="w-4 h-4 mr-2" />
                {{ displayValue || placeholder }}
            </Button>
        </PopoverTrigger>
        <PopoverContent class="z-50 w-auto p-0">
            <Calendar mode="single" :model-value="modelValue" @update:model-value="handleDateSelect"
                :locale="localeString" class="border rounded-md" />
            <div class="p-3 border-t">
                <Button variant="ghost" size="sm" class="w-full" @click="clear" v-if="modelValue">
                    Limpar
                </Button>
            </div>
        </PopoverContent>
    </Popover>
</template>

<script setup lang="ts">
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Button } from '@/components/ui/button';
import { CalendarIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    modelValue: Date | undefined;
    placeholder: string;
    locale?: any; // Pode ser um objeto locale ou string
    displayValue?: string;
}>();

const emit = defineEmits(['update:modelValue']);

const popoverOpen = ref(false);

// Converte o objeto locale para string se necessário
const localeString = computed(() => {
    if (!props.locale) return undefined;
    return typeof props.locale === 'string' ? props.locale : props.locale.code || 'en-US';
});

const handleDateSelect = (date: Date | undefined) => {
    emit('update:modelValue', date);
    popoverOpen.value = false; // Fecha o popover após seleção
};

const clear = () => {
    emit('update:modelValue', undefined);
    popoverOpen.value = false; // Fecha o popover ao limpar
};
</script>
