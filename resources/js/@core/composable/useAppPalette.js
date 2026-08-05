import { computed, ref } from 'vue'
import { useTheme } from 'vuetify'
import {
  applyPalette,
  findPalette,
  PALETTE_STORAGE_KEY,
  PALETTES,
  resolveInitialPalette,
} from '@/plugins/vuetify/palettes'

// Module-level so every component that calls this composable observes the
// same selection, rather than each holding its own copy.
const current = ref(resolveInitialPalette())

export const useAppPalette = () => {
  const vuetifyTheme = useTheme()

  const currentPalette = computed(() => findPalette(current.value))

  const setPalette = key => {
    if (!PALETTES.some(p => p.key === key))
      return

    current.value = key
    applyPalette(vuetifyTheme, key)
    localStorage.setItem(PALETTE_STORAGE_KEY, key)
  }

  return {
    current,
    currentPalette,
    palettes: PALETTES,
    setPalette,
  }
}
