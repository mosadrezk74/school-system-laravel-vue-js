<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { isNavLinkActive } from '@layouts/utils'

const props = defineProps({
  item: {
    type: null,
    required: true,
  },

  /**
   * Set by the nav when it has resolved which single entry owns the current
   * URL — needed because some destinations are prefixes of others. Left
   * undefined, the link falls back to matching on its own, so it still
   * behaves correctly when rendered outside that nav.
   */
  isActive: {
    // Not `type: Boolean` — Vue casts an absent Boolean prop to false, which
    // would make "not supplied" indistinguishable from "not active" and kill
    // the fallback below.
    type: null,
    default: undefined,
  },
})

const page = usePage()

const isLinkActive = computed(() => props.isActive ?? isNavLinkActive(props.item.to, page.url))

const refLink = ref()

/**
 * Nearest ancestor that actually scrolls vertically. Stops at <body> so the
 * page itself is never scrolled — moving the window is exactly the behaviour
 * this replaces.
 *
 * @param {HTMLElement} el
 * @returns {HTMLElement|null}
 */
const findScrollableAncestor = el => {
  let node = el.parentElement

  while (node && node !== document.body) {
    const { overflowY } = getComputedStyle(node)

    if ((overflowY === 'auto' || overflowY === 'scroll') && node.scrollHeight > node.clientHeight) {
      return node
    }

    node = node.parentElement
  }

  return null
}

/**
 * Bring the active entry into view when it is off-screen.
 *
 * The sidebar scrolls inside a PerfectScrollbar element, not the window, so
 * `scrollIntoView()` is avoided: with `block: 'nearest'` it can still scroll
 * the page itself when an ancestor is scrollable. Instead the nearest
 * scrollable ancestor is measured directly and only its scrollTop is moved,
 * which also leaves a manually chosen scroll position untouched whenever the
 * item is already visible.
 */
const scrollActiveLinkIntoView = () => {
  const el = refLink.value

  if (!el) {
    return
  }

  /*
   * `.nav-items` is the PerfectScrollbar element that owns the sidebar's
   * overflow. It is resolved by class first, then by walking up for any
   * scrollable ancestor, so a future change to the nav markup degrades into
   * "scroll the right thing anyway" instead of silently doing nothing.
   */
  const container = el.closest('.nav-items') ?? findScrollableAncestor(el)

  if (!container) {
    return
  }

  /*
   * Measured with getBoundingClientRect rather than offsetTop: offsetTop is
   * relative to the nearest positioned ancestor, which is not necessarily the
   * scroll container, so it would drift as soon as anything between the two is
   * positioned. Rects are viewport-relative, so the difference between them is
   * the true offset either way.
   */
  const itemRect = el.getBoundingClientRect()
  const viewRect = container.getBoundingClientRect()

  // Already fully visible — preserve wherever the user had scrolled to.
  if (itemRect.top >= viewRect.top && itemRect.bottom <= viewRect.bottom) {
    return
  }

  // A little breathing room so the item never sits flush against the edge.
  const padding = 16

  const delta = itemRect.top < viewRect.top
    ? itemRect.top - viewRect.top - padding
    : itemRect.bottom - viewRect.bottom + padding

  container.scrollTo({
    top: Math.max(0, container.scrollTop + delta),
    behavior: 'smooth',
  })
}

/*
 * Runs after the DOM reflects the new active state, and only from the active
 * link itself, so exactly one element per visit can move the scroll position.
 * Covers first paint (direct URL / refresh) and every later visit, including
 * browser back and forward, since Inertia updates page.url for all of them.
 */
watch(isLinkActive, active => {
  if (active) {
    nextTick(scrollActiveLinkIntoView)
  }
}, { immediate: true })
</script>

<template>
  <!--
    The ref sits on the <li> rather than the link: <Component> may render
    Inertia's <Link>, whose ref is a component instance, not a DOM node.
  -->
  <li
    ref="refLink"
    class="nav-link"
    :class="{ disabled: item.disable }"
  >
    <!--
      `item.to` is an internal destination and uses Inertia's <Link> so the
      visit stays client-side; `item.href` is an external URL and stays a
      plain anchor for a full page load.
    -->
    <!--
      `router-link-exact-active` is the class the template's stylesheets
      already style the active item with; it is applied by hand because
      Inertia's <Link>, unlike Vue Router's, does not emit it.
    -->
    <Component
      :is="item.to ? Link : 'a'"
      :href="item.to || item.href"
      :target="item.target"
      :class="{ 'router-link-exact-active': isLinkActive }"
      :aria-current="isLinkActive ? 'page' : undefined"
    >
      <VIcon
        :icon="item.icon || 'bxs-circle'"
        class="nav-item-icon"
      />
      <!-- 👉 Title -->
      <span class="nav-item-title">
        {{ item.title }}
      </span>
      <span
        class="nav-item-badge"
        :class="item.badgeClass"
      >
        {{ item.badgeContent }}
      </span>
    </Component>
  </li>
</template>

<style lang="scss">
.layout-vertical-nav {
  .nav-link a {
    display: flex;
    align-items: center;
    cursor: pointer;
  }
}
</style>
