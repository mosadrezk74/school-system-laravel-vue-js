/**
 * Whether a nav item's destination matches the URL currently being displayed.
 *
 * The template's stylesheets were written for Vue Router and key the active
 * look off `.router-link-exact-active`; Inertia's <Link> never emits that
 * class, so the class is applied manually from this check.
 *
 * A prefix match (not equality) is what makes nested routes light up their
 * parent entry — `/academic/grade-levels/10/edit` keeps "Grade Levels"
 * active. The boundary test on the next character stops `/reports` from also
 * matching `/reports-archive`, and the query/hash is discarded so
 * `?page=2` still counts as the same page.
 *
 * @param {string|undefined} to Nav item destination, e.g. `/academic/semesters`
 * @param {string|undefined} currentUrl Inertia's `usePage().url`
 * @returns {boolean}
 */
export const isNavLinkActive = (to, currentUrl) => {
  if (!to || !currentUrl) {
    return false
  }

  const path = currentUrl.split(/[?#]/)[0].replace(/\/+$/, '') || '/'
  const target = to.split(/[?#]/)[0].replace(/\/+$/, '') || '/'

  // "/" would prefix-match every route, so it only ever matches exactly.
  if (target === '/') {
    return path === '/'
  }

  return path === target || path.startsWith(`${target}/`)
}

export const hexToRgb = hex => {
  // Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF")
  const shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i

  hex = hex.replace(shorthandRegex, (m, r, g, b) => {
    return r + r + g + g + b + b
  })

  const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex)
  
  return result ? `${Number.parseInt(result[1], 16)},${Number.parseInt(result[2], 16)},${Number.parseInt(result[3], 16)}` : null
}
