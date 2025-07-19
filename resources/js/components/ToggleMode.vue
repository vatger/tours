<script setup lang="ts">
import { Sun, Moon, Monitor } from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';
import { ref } from 'vue';
import vClickOutside from 'v-click-outside'

const { appearance, updateAppearance } = useAppearance();

const modes = [
    { value: 'light', icon: Sun, label: 'Light' },
    { value: 'dark', icon: Moon, label: 'Dark' },
    { value: 'system', icon: Monitor, label: 'System' },
] as const;

const isOpen = ref(false);
const toggleRef = ref<HTMLButtonElement | null>(null);

// Fechar dropdown ao clicar fora
const onClickOutside = () => {
    isOpen.value = false;
};

// Alternar visibilidade do dropdown
const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

// Atualizar aparência e fechar dropdown
const handleAppearanceChange = (mode: typeof appearance.value) => {
    updateAppearance(mode);
    isOpen.value = false;
};
</script>

<template>
    <div class="relative">
        <!-- Botão de alternância -->
        <button ref="toggleRef" @click="toggleDropdown"
            class="flex items-center justify-center p-2 transition-colors rounded-full hover:bg-neutral-100 dark:hover:bg-neutral-800"
            aria-label="Toggle theme">
            <Sun v-if="appearance === 'light'" class="w-5 h-5 text-yellow-500" />
            <Moon v-else-if="appearance === 'dark'" class="w-5 h-5 text-indigo-400" />
            <Monitor v-else class="w-5 h-5 text-neutral-500" />
        </button>

        <!-- Dropdown de opções -->
        <Transition enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0">
            <div v-if="isOpen" v-click-outside="onClickOutside"
                class="absolute right-0 z-50 w-48 mt-2 origin-top-right bg-white rounded-md shadow-lg dark:bg-neutral-800 ring-1 ring-black ring-opacity-5 focus:outline-none">
                <div class="p-1">
                    <button v-for="mode in modes" :key="mode.value" @click="handleAppearanceChange(mode.value)"
                        class="flex items-center w-full gap-3 px-3 py-2 text-sm transition-colors rounded hover:bg-neutral-100 dark:hover:bg-neutral-700"
                        :class="{
                            'bg-neutral-100 dark:bg-neutral-700': appearance === mode.value
                        }">
                        <component :is="mode.icon" class="w-4 h-4" />
                        <span>{{ mode.label }}</span>
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
/* Estilo para o ícone ativo */
button[aria-label="Toggle theme"] {
    transition: all 0.2s ease;
}

button[aria-label="Toggle theme"]:hover {
    transform: scale(1.1);
}
</style>
