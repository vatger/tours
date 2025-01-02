import { ref, watch, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'

type Appearance = 'light' | 'dark' | 'system'

export function useAppearance() {
  const appearance = ref<Appearance>('system')

  // Initialize appearance from user preferences or system
  onMounted(() => {
    const savedAppearance = localStorage.getItem('appearance') as Appearance | null
    if (savedAppearance) {
      appearance.value = savedAppearance
      updateTheme(savedAppearance)
    }
  })

  // Update theme based on appearance setting
  function updateTheme(value: Appearance) {
    if (value === 'system') {
      const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
      document.documentElement.classList.toggle('dark', systemTheme === 'dark')
    } else {
      document.documentElement.classList.toggle('dark', value === 'dark')
    }
  }

  // Handle system theme changes
  onMounted(() => {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
    mediaQuery.addEventListener('change', () => {
      if (appearance.value === 'system') {
        updateTheme('system')
      }
    })
  })

  // Update appearance preference
  async function updateAppearance(value: Appearance) {
    appearance.value = value
    localStorage.setItem('appearance', value)
    updateTheme(value)

    // Update user preference in backend if authenticated
    const { auth } = usePage().props
    if (auth?.user) {
      await axios.patch(route('settings.appearance'), { appearance: value })
    }
  }

  return {
    appearance,
    updateAppearance
  }
}
