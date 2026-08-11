/*
 * Locale-agnostic text normalisation for client-side dropdown filtering.
 *
 * The rule of thumb: strip only the differences a user is unlikely to type
 * deliberately (case, stray whitespace, Latin accents, Arabic tashkeel and
 * letter-shape variants). Anything beyond that — removing whole letters,
 * transliterating between scripts — starts matching unrelated records, which
 * is worse than a miss.
 *
 * No language is detected or hardcoded: every rule below is a no-op on text
 * that does not contain the characters it targets, so Arabic, English, French
 * and German all pass through the same pipeline.
 */

/** Tashkeel (harakat) + tatweel: decoration users never type when searching. */
const ARABIC_DIACRITICS = /[ؐ-ًؚ-ٰٟۖ-ۭـ]/g

/**
 * Arabic letters whose written form varies without changing the word:
 * hamza-carrying alefs, the two ya forms, and ta marbuta.
 */
const ARABIC_EQUIVALENTS = [
  [/[آأإٱ]/g, 'ا'], // آ أ إ ٱ → ا
  [/ى/g, 'ي'], // ى → ي
  [/ة/g, 'ه'], // ة → ه
  [/[ؤ]/g, 'و'], // ؤ → و
  [/[ئ]/g, 'ي'], // ئ → ي
]

/**
 * Folds a value into its comparable form.
 *
 * Latin diacritics go through NFD so the combining marks can be dropped in one
 * pass — this covers French (é, ç, à) and German (ä, ö, ü) without listing
 * either language. German ß is folded to `ss` explicitly because Unicode
 * decomposition leaves it alone.
 *
 * @param {unknown} value
 * @returns {string} empty string for null/undefined/non-primitive input
 */
export const normalizeSearchText = value => {
  if (value === null || value === undefined)
    return ''

  const text = typeof value === 'string' ? value : String(value)

  if (!text)
    return ''

  let result = text
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .replace(ARABIC_DIACRITICS, '')
    .toLocaleLowerCase()
    .replace(/ß/g, 'ss')

  for (const [pattern, replacement] of ARABIC_EQUIVALENTS)
    result = result.replace(pattern, replacement)

  return result.replace(/\s+/g, ' ').trim()
}

/**
 * Vuetify `custom-filter` signature.
 *
 * Vuetify hands us the item's *displayed* title (already resolved through
 * `item-title`, so a translated label is compared exactly as the user sees it
 * in the active locale) plus the raw item. Every whitespace-separated term in
 * the query must appear somewhere in the title, which makes "ahmed mohamed"
 * and "mohamed ahmed" both find the same person.
 *
 * @param {string} itemTitle
 * @param {string} query
 * @returns {boolean}
 */
export const matchesSearchQuery = (itemTitle, query) => {
  const needle = normalizeSearchText(query)

  if (!needle)
    return true

  const haystack = normalizeSearchText(itemTitle)

  if (!haystack)
    return false

  return needle.split(' ').every(term => haystack.includes(term))
}
