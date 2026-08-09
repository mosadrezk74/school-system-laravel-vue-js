import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

/**
 * The filter/paginate behaviour every index page had rewritten.
 *
 * 33 pages each declared their own `searchTimer`, their own debounce, and
 * their own `router.get` with the same three Inertia options — differing only
 * in the URL and which filter keys they sent. Centralising it fixes the
 * debounce timer leaking on unmount (none of the copies cleared it) without
 * changing a single query-string key.
 *
 * @param {object} options
 * @param {string} options.url            Index route to visit.
 * @param {object} [options.initial]      Server-supplied `filters` prop.
 * @param {number} [options.debounce]     Delay for text filters, ms.
 * @param {string[]} [options.debounced]  Filter keys that debounce rather
 *                                        than fire immediately.
 * @returns {{filters: object, goToPage: (page: number) => void, apply: () => void, reset: () => void}}
 */
export function useIndexFilters({ url, initial = {}, debounce = 350, debounced = ['search'] }) {
  /*
   * Each filter is its own ref so a page can bind `v-model` directly, and the
   * initial value comes from the server so a shared/bookmarked URL restores
   * the exact list the user was looking at.
   */
  const filters = Object.fromEntries(
    Object.entries(initial).map(([key, value]) => [key, ref(value ?? null)]),
  )

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
    })
  }

  const apply = () => visit()

  // Paging is a real navigation, so it pushes a history entry rather than
  // replacing one — the back button should return to the previous page.
  const goToPage = page => visit({ page }, false)

  let timer

  Object.entries(filters).forEach(([key, source]) => {
    watch(source, () => {
      if (!debounced.includes(key)) {
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

  return { filters, apply, goToPage, reset, params }
}
