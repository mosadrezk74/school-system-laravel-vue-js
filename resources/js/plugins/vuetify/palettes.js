/**
 * Selectable brand palettes.
 *
 * Only the primary family changes between palettes — status colours (success,
 * warning, error, info) stay fixed because they carry meaning, not branding.
 * Switching writes Vuetify's primary theme variables at runtime, so no rebuild
 * and no duplicated theme definitions.
 */
export const PALETTES = [
  { key: 'indigo', label: 'Indigo', primary: '#696CFF', darken: '#5E61E6', light: '#8789FF' },
  { key: 'emerald', label: 'Emerald', primary: '#0F9D76', darken: '#0C8A67', light: '#3DB894' },
  { key: 'ocean', label: 'Ocean', primary: '#1273C4', darken: '#0F63A9', light: '#3F92D6' },
  { key: 'amber', label: 'Amber', primary: '#B4741E', darken: '#9C641A', light: '#C99046' },
  { key: 'plum', label: 'Plum', primary: '#8B4A9C', darken: '#774086', light: '#A56BB4' },
]

export const DEFAULT_PALETTE = 'indigo'
export const PALETTE_STORAGE_KEY = 'app-palette'

export const findPalette = key =>
  PALETTES.find(p => p.key === key) ?? PALETTES[0]

export const resolveInitialPalette = () => {
  if (typeof localStorage === 'undefined')
    return DEFAULT_PALETTE

  const stored = localStorage.getItem(PALETTE_STORAGE_KEY)

  return PALETTES.some(p => p.key === stored) ? stored : DEFAULT_PALETTE
}

/**
 * Applies a palette to every registered Vuetify theme (light and dark) by
 * mutating the theme colour objects, which Vuetify re-renders reactively.
 */
export const applyPalette = (vuetifyTheme, key) => {
  const palette = findPalette(key)

  Object.values(vuetifyTheme.themes.value).forEach(theme => {
    theme.colors.primary = palette.primary
    theme.colors['primary-darken-1'] = palette.darken
    theme.colors['primary-light'] = palette.light
  })
}
