import { router, usePage } from '@inertiajs/vue3'
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

  /*
   * Last-resort entry so the switcher always has something to render even if
   * both the live props and the module-load snapshot come back empty (e.g.
   * an unseeded `languages` table). Language::active() now guarantees a
   * non-empty `languages` prop server-side, so this should never be reached
   * in practice — it exists to fail gracefully, not to paper over the real
   * fix.
   */
  const FALLBACK_LOCALE = { code: locale.value || 'en', label: 'EN', dir: 'ltr' }

  const locales = computed(() => {
    const fromServer = page.props.languages ?? []

    const list = fromServer.length
      ? fromServer.map(l => ({ code: l.locale, label: l.name, dir: l.direction }))
      : SUPPORTED_LOCALES

    return list.length ? list : [FALLBACK_LOCALE]
  })

  const currentLocale = computed(() =>
    locales.value.find(l => l.code === locale.value) ?? locales.value[0])

  /*
   * Every translated attribute in the app (Subject.name, Period.name, ...)
   * is resolved server-side from app()->getLocale(), which SetLocaleMiddleware
   * only re-evaluates on an actual request. Flipping the client-side i18n
   * locale alone would relabel the chrome but leave every table/dropdown/card
   * showing whatever locale the last full page load resolved — so this has to
   * round-trip through the server (query param the middleware reads and
   * persists to the session/user), then reload every prop on the current
   * page, not just swap strings client-side.
   */
  const setLocale = code => {
    if (!locales.value.some(l => l.code === code))
      return

    locale.value = code
    vuetifyLocale.current.value = code
    applyDocumentDirection(code)
    localStorage.setItem(STORAGE_KEY, code)

    const url = new URL(window.location.href)

    url.searchParams.set('lang', code)

    router.get(url.pathname + url.search, {}, {
      preserveScroll: true,
      preserveState: false,
    })
  }

  return {
    locale,
    currentLocale,
    locales,
    setLocale,
  }
}
