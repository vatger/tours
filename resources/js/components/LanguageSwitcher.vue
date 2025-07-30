<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useLocale } from '@/composables/useLocale';
import { router, usePage } from '@inertiajs/vue3';
import { Globe } from 'lucide-vue-next';
import { computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';

interface Props {
    display?: 'dropdown' | 'select' | 'cards';
}

withDefaults(defineProps<Props>(), {
    display: 'dropdown',
});

const { t, locale } = useI18n();
const page = usePage();
const { initializeLocale } = useLocale();

const availableLocales = page.props.locale.available;

const capitalize = (s: string) => (s ? s.charAt(0).toUpperCase() + s.slice(1) : s);

const languages = availableLocales.map((code: string) => {
    const name = new Intl.DisplayNames([code], { type: 'language' }).of(code);
    return { code, name: capitalize(name || code) };
});

const currentLanguage = computed(() => {
    return languages.find((lang) => lang.code === locale.value) || languages[0];
});

watch(locale, async (newLocale, oldLocale) => {
    if (newLocale !== oldLocale && oldLocale) {
        await syncLocaleWithBackend(newLocale);
    }
});

const syncLocaleWithBackend = async (langCode: string) => {
    const isAuthenticated = (page.props as any)?.auth?.user;
    const routeName = isAuthenticated ? 'language.update' : 'language.update.guest';

    try {
        await router.patch(
            route(routeName),
            { locale: langCode },
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {},
                onError: () => {
                    initializeLocale();
                },
            },
        );
    } catch (error) {
        initializeLocale();
    }
};

const switchLanguage = async (langCode: string) => {
    locale.value = langCode;
};
</script>

<template>
    <!-- Dropdown Display (Default) -->
    <DropdownMenu v-if="display === 'dropdown'">
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" class="h-8 w-8 p-0">
                <Globe class="h-4 w-4 text-foreground" />
                <span class="sr-only">{{ t('common.switchLanguage') }}</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuItem
                v-for="language in languages"
                :key="language.code"
                @click="switchLanguage(language.code)"
                :class="{ 'bg-accent': language.code === currentLanguage.code }"
            >
                {{ language.name }}
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>

    <!-- Select Display -->
    <Select v-else-if="display === 'select'" v-model="locale">
        <SelectTrigger class="w-[180px]">
            <SelectValue class="text-foreground">
                <span class="flex items-center gap-2">
                    <span class="text-foreground">{{ currentLanguage.name }}</span>
                </span>
            </SelectValue>
        </SelectTrigger>
        <SelectContent>
            <SelectItem v-for="language in languages" :key="language.code" :value="language.code">
                <span class="flex items-center gap-2">
                    <span>{{ language.name }}</span>
                </span>
            </SelectItem>
        </SelectContent>
    </Select>

    <!-- Card Display -->
    <Card v-else-if="display === 'cards'">
        <CardHeader>
            <CardTitle class="flex items-center gap-2">
                <Globe class="h-5 w-5" />
                {{ t('settings.language.title') }}
            </CardTitle>
            <CardDescription>
                {{ t('settings.language.description') }}
            </CardDescription>
        </CardHeader>
        <CardContent>
            <div class="grid gap-3">
                <Button
                    v-for="language in languages"
                    :key="language.code"
                    @click="switchLanguage(language.code)"
                    variant="outline"
                    :class="['justify-start', language.code === currentLanguage.code ? 'border-primary bg-primary/5' : 'hover:bg-muted']"
                >
                    <div class="flex w-full items-center justify-between">
                        <span class="font-medium">{{ language.name }}</span>
                        <span v-if="language.code === currentLanguage.code" class="text-xs text-muted-foreground">
                            {{ t('settings.language.currentLanguage') }}
                        </span>
                    </div>
                </Button>
            </div>
        </CardContent>
    </Card>
</template>
