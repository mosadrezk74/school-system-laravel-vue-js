import { computed, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

/** Filter types whose control is free text, and so should debounce. */
const TEXT_TYPES = ['text', 'number']

/** Filter types that send `{key}_from` / `{key}_to` instead of `{key}`. */
const RANGE_TYPES = ['date-range', 'number-range']

/**
 * Every query-string key a backend filter definition can occupy.
 *
 * This mirrors the flat contract the server parses — `name`, `name_op`,
 * `amount_from`, `amount_to` — so the composable never has to guess which
 * parameters a field owns.
 *
 * @param {{key: string, type: string, operators?: string[]}} definition
 * @returns {string[]}
 */
const keysFor = definition => {
  if (RANGE_TYPES.includes(definition.type))
    return [`${definition.key}_from`, `${definition.key}_to`]

  const keys = [definition.key]

  // An operator select is only worth carrying when there is a choice to make.
  if (definition.operators?.length > 1 && TEXT_TYPES.includes(definition.type))
    keys.push(`${definition.key}_op`)

  return keys
}

/**
 * The filter/paginate behaviour every index page had rewritten.
 *
 * 33 pages each declared their own `searchTimer`, their own debounce, and
 * their own `router.get` with the same three Inertia options — differing only
 * in the URL and which filter keys they sent. Centralising it fixes the
 * debounce timer leaking on unmount (none of the copies cleared it) without
 * changing a single query-string key.
 *
 * Pass `definitions` (the schema the Query published) and the state is derived
 * from it, so adding a filter on the server adds it here without the page
 * listing the key a second time. `initial` still works on its own for pages
 * with no schema.
 *
 * @param {object} options
 * @param {string} options.url            Index route to visit.
 * @param {object} [options.initial]      Server-supplied `filters` prop.
 * @param {Array}  [options.definitions]  Server-supplied filter schema.
 * @param {number} [options.debounce]     Delay for text filters, ms.
 * @param {string[]} [options.debounced]  Filter keys that debounce rather
 *                                        than fire immediately.
 * @param {string[]} [options.only]       Props to reload, for a picker that
 *                                        refreshes one part of a larger page
 *                                        instead of the whole list.
 * @returns {{filters: object, definitions: Array, activeKeys: object, goToPage: (page: number) => void, apply: () => void, reset: () => void, clear: (key: string) => void, params: (extra?: object) => object}}
 */
export function useIndexFilters({
  url,
  initial = {},
  definitions = [],
  debounce = 350,
  debounced = ['search'],
  only = [],
}) {
  /*
   * Keys come from the schema where there is one, and from `initial` for
   * anything the page passes by hand — a page mid-migration can use both.
   */
  const keys = new Set(Object.keys(initial))

  definitions.forEach(definition => {
    keysFor(definition).forEach(key => keys.add(key))
  })

  /*
   * Each filter is its own ref so a page can bind `v-model` directly, and the
   * initial value comes from the server so a shared/bookmarked URL restores
   * the exact list the user was looking at.
   */
  const filters = Object.fromEntries(
    [...keys].map(key => [key, ref(initial[key] ?? null)]),
  )

  /** Text and number controls debounce; selects and dates fire at once. */
  const debouncedKeys = new Set(debounced)

  definitions
    .filter(definition => TEXT_TYPES.includes(definition.type))
    .forEach(definition => debouncedKeys.add(definition.key))

  /**
   * Drops empty values so the URL carries only the filters actually in use.
   * `null`, `undefined` and `''` are all omitted — an empty string would make
   * `$request->has()` true on the backend and read as an explicit filter.
   */
  const params = (extra = {}) => {
    const query = {}

    Object.entries(filters).forEach(([key, source]) => {
      const value = source.value

      if (value !== null && value !== undefined && value !== '')
        query[key] = value
    })

    return { ...query, ...extra }
  }

  const visit = (extra = {}, replace = true) => {
    router.get(url, params(extra), {
      preserveState: true,
      preserveScroll: true,
      replace,
      // A partial reload when the page only needs one prop refreshed — a
      // student picker inside a detail page, rather than a whole list.
      ...(only.length ? { only } : {}),
    })
  }

  // Omitting `page` is what resets paging: a narrowed list whose page 4 no
  // longer exists would otherwise come back empty.
  const apply = () => visit()

  // Paging is a real navigation, so it pushes a history entry rather than
  // replacing one — the back button should return to the previous page.
  const goToPage = page => visit({ page }, false)

  let timer

  Object.entries(filters).forEach(([key, source]) => {
    watch(source, () => {
      if (!debouncedKeys.has(key)) {
        apply()

        return
      }

      clearTimeout(timer)
      timer = setTimeout(apply, debounce)
    })
  })

  const reset = () => {
    Object.values(filters).forEach(source => { source.value = null })
  }

  /** Clears one field, including the `_from`/`_to`/`_op` keys it owns. */
  const clear = key => {
    const definition = definitions.find(item => item.key === key)
    const owned = definition ? keysFor(definition) : [key]

    owned.forEach(ownedKey => {
      if (filters[ownedKey])
        filters[ownedKey].value = null
    })
  }

  /** Keys currently carrying a value, for rendering removable chips. */
  const activeKeys = computed(() =>
    Object.entries(filters)
      .filter(([, source]) => source.value !== null && source.value !== undefined && source.value !== '')
      .map(([key]) => key))

  return { filters, definitions, activeKeys, apply, goToPage, reset, clear, params }
}
