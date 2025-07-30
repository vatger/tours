<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Globe } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'
import { useLocale } from '@/composables/useLocale'
import { computed } from 'vue'

const { locale, t } = useI18n()
const page = usePage()
const { initializeLocale } = useLocale()

const languages = [
  { code: 'en', name: 'English', flag: '🇺🇸' },
  { code: 'fr', name: 'Français', flag: '🇫🇷' },
]

const currentLanguage = computed(() =>
  languages.find(lang => lang.code === locale.value) || languages[0]
)

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
  <Card>
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
          :class="[
            'justify-start',
            language.code === currentLanguage.code
              ? 'border-primary bg-primary/5'
              : 'hover:bg-muted'
          ]"
        >
          <span class="mr-3 text-lg">{{ language.flag }}</span>
          <div class="flex items-center justify-between w-full">
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
