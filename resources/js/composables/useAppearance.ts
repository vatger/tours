import { ref, watch, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

type Appearance = 'light' | 'dark' | 'system'

export function updateTheme(value: Appearance) {
  if (value === 'system') {
    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
    document.documentElement.classList.toggle('dark', systemTheme === 'dark')
  } else {
    document.documentElement.classList.toggle('dark', value === 'dark')
  }
}

export function initializeTheme() {
  // Initialize theme from saved preference or default to system
  const savedAppearance = localStorage.getItem('appearance') as Appearance | null
  if (savedAppearance) {
    updateTheme(savedAppearance)
  } else {
    updateTheme('system')
  }

  // Set up system theme change listener
  const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
  mediaQuery.addEventListener('change', () => {
    const currentAppearance = localStorage.getItem('appearance') as Appearance | null
    if (!currentAppearance || currentAppearance === 'system') {
      updateTheme('system')
    }
  })
}

export function useAppearance() {
  const appearance = ref<Appearance>('system')

  onMounted(() => {
    initializeTheme()
    const savedAppearance = localStorage.getItem('appearance') as Appearance | null
    if (savedAppearance) {
      appearance.value = savedAppearance
    }
  })

  function updateAppearance(value: Appearance) {
    appearance.value = value
    localStorage.setItem('appearance', value)
    updateTheme(value)
  }

  return {
    appearance,
    updateAppearance
  }
}
