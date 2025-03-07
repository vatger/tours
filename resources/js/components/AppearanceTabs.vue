<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Monitor, Moon, Sun } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    class?: string;
}

const { class: containerClass = '' } = defineProps<Props>();

const { appearance, updateAppearance } = useAppearance();

const tabs = [
    { value: 'light', Icon: Sun, label: 'Light' },
    { value: 'dark', Icon: Moon, label: 'Dark' },
    { value: 'system', Icon: Monitor, label: 'System' },
] as const;

const activeTab = ref(appearance);
</script>

<template>
    <v-tabs v-model="activeTab" background-color="neutral-100" dark-background-color="neutral-800" class="rounded-lg p-1" :class="containerClass">
        <v-tab v-for="{ value, Icon, label } in tabs" :key="value" @click="updateAppearance(value)">
            <v-icon :icon="Icon" class="-ml-1 h-4 w-4" />
            <span class="ml-1.5 text-sm">{{ label }}</span>
        </v-tab>
    </v-tabs>
</template>
