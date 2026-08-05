import { createVuetify } from 'vuetify'
import { VBtn } from 'vuetify/components/VBtn'
import defaults from './defaults'
import { icons } from './icons'
import { themes } from './theme'
import { findPalette, resolveInitialPalette } from './palettes'
import { resolveInitialLocale, SUPPORTED_LOCALES } from '@modules/Setting/resources/js/plugins/i18n'

// Styles
import '@core-scss/template/libs/vuetify/index.scss'
import 'vuetify/styles'

export default function (app) {
  const initialLocale = resolveInitialLocale()

  /*
   * Seed the saved palette into the theme definitions before Vuetify reads
   * them, so the first paint already uses the chosen brand colour instead of
   * flashing the default and correcting afterwards.
   */
  const palette = findPalette(resolveInitialPalette())

  Object.values(themes).forEach(theme => {
    theme.colors.primary = palette.primary
    theme.colors['primary-darken-1'] = palette.darken
    theme.colors['primary-light'] = palette.light
  })

  const vuetify = createVuetify({
    aliases: {
      IconBtn: VBtn,
    },
    defaults,
    icons,
    theme: {
      defaultTheme: 'light',
      themes,
    },

    /*
     * Vuetify owns the RTL flag for its own components (VNavigationDrawer,
     * VMenu placement, etc). Seed it from the saved locale and let the
     * language switcher flip it at runtime via useRtl().
     */
    locale: {
      locale: initialLocale,
      rtl: Object.fromEntries(
        SUPPORTED_LOCALES.map(l => [l.code, l.dir === 'rtl']),
      ),
    },
  })

  app.use(vuetify)
}
