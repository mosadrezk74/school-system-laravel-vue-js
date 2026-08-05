import { createI18n } from 'vue-i18n'

/**
 * Message bundles. Adding a language means dropping a JSON file in ./locales
 * and enabling the row in the `languages` table — nothing here enumerates
 * locales, so no code change is needed either way.
 *
 * A locale that is enabled in the database but has no bundle yet still works:
 * vue-i18n falls back to `fallbackLocale` for any missing key.
 */
const bundles = import.meta.glob('./locales/*.json', { eager: true })

const messages = Object.fromEntries(
  Object.entries(bundles).map(([path, mod]) => [
    path.match(/\/([\w-]+)\.json$/)[1],
    mod.default ?? mod,
  ]),
)

export const STORAGE_KEY = 'app-locale'

/**
 * The initial page object Inertia embeds in the document.
 *
 * Inertia v3 serialises it into a <script data-page="app"> tag rather than a
 * data-page attribute on the mount div, so read it from there.
 */
const initialPage = () => {
  if (typeof document === 'undefined')
    return {}

  try {
    return JSON.parse(document.querySelector('script[data-page]')?.textContent ?? '{}')
  } catch {
    return {}
  }
}

/** Locales the server says this school has switched on. */
const serverLanguages = () => initialPage().props?.languages ?? []

export const SUPPORTED_LOCALES = serverLanguages().map(l => ({
  code: l.locale,
  label: l.name,
  dir: l.direction,
}))

const fallback = SUPPORTED_LOCALES[0]?.code ?? 'en'

export const isRtlLocale = code =>
  SUPPORTED_LOCALES.find(l => l.code === code)?.dir === 'rtl'

/** Restores the saved locale, ignoring one the school has since disabled. */
export const resolveInitialLocale = () => {
  const stored = typeof localStorage !== 'undefined'
    ? localStorage.getItem(STORAGE_KEY)
    : null

  if (stored && SUPPORTED_LOCALES.some(l => l.code === stored))
    return stored

  // The server already resolved a locale for this request; trust it.
  const serverLocale = initialPage().props?.locale
  if (serverLocale && SUPPORTED_LOCALES.some(l => l.code === serverLocale))
    return serverLocale

  return fallback
}

/** Keeps <html lang/dir> in step with the active locale. */
export const applyDocumentDirection = locale => {
  if (typeof document === 'undefined')
    return

  document.documentElement.setAttribute('lang', locale)
  document.documentElement.setAttribute('dir', isRtlLocale(locale) ? 'rtl' : 'ltr')
}

const initialLocale = resolveInitialLocale()

export const i18n = createI18n({
  legacy: false,
  locale: initialLocale,
  fallbackLocale: fallback,
  messages,
})

export default function (app) {
  applyDocumentDirection(initialLocale)
  app.use(i18n)
}
