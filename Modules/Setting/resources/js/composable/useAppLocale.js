import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useLocale } from 'vuetify'
import {
  applyDocumentDirection,
  STORAGE_KEY,
  SUPPORTED_LOCALES,
} from '@modules/Setting/resources/js/plugins/i18n'

/**
 * Switching language has to move three things at once, or the page ends up
 * half-flipped: the translations (vue-i18n), Vuetify's own RTL flag, and the
 * <html lang/dir> attributes that drive the template's RTL stylesheet.
 */
export const useAppLocale = () => {
  const { locale } = useI18n({ useScope: 'global' })

  /*
   * Drive Vuetify's *global* locale rather than useRtl()'s isRtl ref: isRtl is
   * scoped to the calling component, so writing to it leaves the app-level
   * VLocaleProvider untouched and the layout never flips. Vuetify derives RTL
   * from this locale via the `rtl` map configured in plugins/vuetify.
   */
  const vuetifyLocale = useLocale()

  /*
   * Read from shared props rather than the module-load snapshot: enabling a
   * language on the settings screen updates the props on the next visit, and
   * the switcher has to reflect that without a full page reload.
   */
  const page = usePage()

  const locales = computed(() => {
    const fromServer = page.props.languages ?? []

    return fromServer.length
      ? fromServer.map(l => ({ code: l.locale, label: l.name, dir: l.direction }))
      : SUPPORTED_LOCALES
  })

  const currentLocale = computed(() =>
    locales.value.find(l => l.code === locale.value) ?? locales.value[0])

  const setLocale = code => {
    if (!locales.value.some(l => l.code === code))
      return

    locale.value = code
    vuetifyLocale.current.value = code
    applyDocumentDirection(code)
    localStorage.setItem(STORAGE_KEY, code)
  }

  return {
    locale,
    currentLocale,
    locales,
    setLocale,
  }
}
