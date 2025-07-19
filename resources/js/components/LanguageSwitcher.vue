<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ChevronDown } from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger
} from '@/components/ui/dropdown-menu';
import { loadLanguageAsync } from 'laravel-vue-i18n';

// Estado reativo para o idioma atual


const languages = [
    { value: 'pt_BR', label: 'Português (BR)', flag: '🇧🇷' },
    { value: 'en', label: 'English', flag: '🇺🇸' },
    { value: 'es', label: 'Español', flag: '🇪🇸' },
    { value: 'fr', label: 'Français', flag: '🇫🇷' },
    { value: 'de', label: 'Deutsch', flag: '🇩🇪' },
    { value: 'it', label: 'Italiano', flag: '🇮🇹' } // Italiano adicionado aqui
];
const currentLocale = ref(localStorage.getItem('preferred_language') as string);

// Carrega o idioma inicial
loadLanguageAsync(currentLocale.value);
const currentLanguage = ref(languages.find(lang => lang.value === currentLocale.value) || languages[0]);

// Observa mudanças no localStorage
watch(currentLocale, async (newLocale) => {
    await loadLanguageAsync(newLocale);
    localStorage.setItem('preferred_language', newLocale);
});

const changeLanguage = async (lang: string) => {
    if (currentLocale.value === lang) return;

    currentLocale.value = lang;
    currentLanguage.value = languages.find(l => l.value === lang) || languages[0];
    setCookie('preferred_language', lang);
    localStorage.setItem('preferred_language', lang);
    await router.patch(route('locale.set'), { locale: lang }, {
        preserveState: true,
        preserveScroll: true,
        headers: { 'X-Locale': lang }
    });

    // Recarrega os dados da página com Inertia
    await router.reload({
        preserveState: false, preserveScroll: true,
        headers: { 'X-Locale': lang }
    });
    // router.reload({
    //     preserveState: true,
    //     preserveScroll: true,
    //     headers: { 'X-Locale': lang }
    // });
};
const setCookie = (name: string, value: string, days = 365) => {

    const maxAge = days * 24 * 60 * 60;

    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="outline" size="sm" class="gap-2 pl-3">
                <span class="text-lg">{{ currentLanguage.flag }}</span>
                <span class="truncate max-w-[100px]">{{ currentLanguage.label }}</span>
                <ChevronDown class="w-4 h-4 opacity-50" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-[180px]">
            <DropdownMenuItem v-for="lang in languages" :key="lang.value" @click="changeLanguage(lang.value)"
                class="cursor-pointer hover:bg-accent" :class="{ 'bg-accent': currentLocale === lang.value }">
                <span class="mr-2 text-lg">{{ lang.flag }}</span>
                <span>{{ lang.label }}</span>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
