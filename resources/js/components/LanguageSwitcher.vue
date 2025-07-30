<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Globe } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'
import { useLocale } from '@/composables/useLocale'

const { locale, t } = useI18n()
const page = usePage()
const { initializeLocale } = useLocale()

const languages = [
  { code: 'en', name: 'English', flag: '🇺🇸' },
  { code: 'fr', name: 'Français', flag: '🇫🇷' },
]

const currentLanguage = languages.find(lang => lang.code === locale.value) || languages[0]

const switchLanguage = async (langCode: string) => {
  // Update the locale in Vue i18n immediately for UI responsiveness
  locale.value = langCode
  
  // Sync with the backend
  try {
    await router.patch(route('language.update'), { locale: langCode }, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        // The page will reload automatically with the new locale
        // No need to manually reload since we're using a redirect response
      },
      onError: () => {
        // Revert to the previous locale if the update failed
        initializeLocale()
      }
    })
  } catch (error) {
    // Revert to the previous locale if the update failed
    initializeLocale()
  }
}
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button variant="ghost" size="icon" class="h-8 w-8">
        <Globe class="h-4 w-4" />
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
        <span class="mr-2">{{ language.flag }}</span>
        {{ language.name }}
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template> 