<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { useDisplay } from 'vuetify'
import logo from '@images/logo.svg?raw'

const props = defineProps({
  tag: {
    type: null,
    required: false,
    default: 'aside',
  },
  isOverlayNavActive: {
    type: Boolean,
    required: true,
  },
  toggleIsOverlayNavActive: {
    type: Function,
    required: true,
  },
})

const { mdAndDown } = useDisplay()
const refNav = ref()

/*ℹ️ Close the overlay nav whenever the visit changes, so tapping a link on
mobile dismisses the drawer. page.url is Inertia's equivalent of route.path. */
const page = usePage()

watch(() => page.url, () => {
  props.toggleIsOverlayNavActive(false)
})

const isVerticalNavScrolled = ref(false)
const updateIsVerticalNavScrolled = val => isVerticalNavScrolled.value = val

/*
 * Sidebar scroll survives a full page load.
 *
 * Inertia visits keep the sidebar mounted, so its scrollTop is naturally
 * preserved; a hard reload (F5, a direct URL, a redirect after a form post)
 * rebuilds it from scratch and would otherwise snap back to the top —
 * stranding someone working in Finance or Settings, which sit near the
 * bottom of a long nav.
 *
 * sessionStorage rather than localStorage: this is per-tab UI state, and it
 * should not follow the user into a new session weeks later.
 */
const SCROLL_KEY = 'vertical-nav-scroll'

const scrollContainer = ref(null)

const handleNavScroll = evt => {
  isVerticalNavScrolled.value = evt.target.scrollTop > 0

  try {
    sessionStorage.setItem(SCROLL_KEY, String(evt.target.scrollTop))
  } catch {
    // Private browsing / storage disabled — scroll memory is a nicety, so
    // failing to persist it must never break the nav.
  }
}

onMounted(async () => {
  await nextTick()

  const container = refNav.value?.querySelector?.('.nav-items')

  if (!container)
    return

  scrollContainer.value = container

  let saved = null

  try {
    saved = sessionStorage.getItem(SCROLL_KEY)
  } catch {
    return
  }

  if (saved === null)
    return

  /*
   * Restored before the active link's own scroll-into-view runs. That handler
   * is a no-op whenever the item is already visible, so the two cooperate:
   * the saved position wins when it already shows the active item, and the
   * link corrects it when it does not.
   */
  container.scrollTop = Number(saved)
  isVerticalNavScrolled.value = container.scrollTop > 0
})
</script>

<template>
  <Component
    :is="props.tag"
    ref="refNav"
    data-allow-mismatch
    class="layout-vertical-nav"
    :class="[
      {
        'visible': isOverlayNavActive,
        'scrolled': isVerticalNavScrolled,
        'overlay-nav': mdAndDown,
      },
    ]"
  >
    <!-- 👉 Header -->
    <div class="nav-header">
      <slot name="nav-header">
        <Link
          href="/"
          class="app-logo app-title-wrapper"
        >
          <div
            class="d-flex"
            v-html="logo"
          />

          <h1 class="leading-normal">
            sneat
          </h1>
        </Link>
      </slot>
    </div>
    <slot name="before-nav-items">
      <div class="vertical-nav-items-shadow" />
    </slot>
    <slot
      name="nav-items"
      :update-is-vertical-nav-scrolled="updateIsVerticalNavScrolled"
    >
      <PerfectScrollbar
        tag="ul"
        class="nav-items"
        :options="{ wheelPropagation: false }"
        @ps-scroll-y="handleNavScroll"
      >
        <slot />
      </PerfectScrollbar>
    </slot>
    <slot name="after-nav-items" />
  </Component>
</template>

<style lang="scss" scoped>
.app-logo {
  display: flex;
  align-items: center;
  column-gap: 0.75rem;

  .app-logo-title {
    font-size: 1.25rem;
    font-weight: 500;
    line-height: 1.75rem;
    text-transform: uppercase;
  }
}
</style>

<style lang="scss">
@use "@configured-variables" as variables;
@use "@layouts/styles/mixins";

// 👉 Vertical Nav
.layout-vertical-nav {
  position: fixed;
  z-index: variables.$layout-vertical-nav-z-index;
  display: flex;
  flex-direction: column;
  block-size: 100%;
  inline-size: variables.$layout-vertical-nav-width;
  inset-block-start: 0;
  inset-inline-start: 0;
  transition: inline-size 0.25s ease-in-out, box-shadow 0.25s ease-in-out;
  will-change: transform, inline-size;

  .nav-header {
    display: flex;
    align-items: center;

    .header-action {
      cursor: pointer;

      @at-root {
        #{variables.$selector-vertical-nav-mini} .nav-header .header-action {
          &.nav-pin,
          &.nav-unpin {
            display: none !important;
          }
        }
      }
    }
  }

  .app-title-wrapper {
    margin-inline-end: auto;
  }

  .nav-items {
    block-size: 100%;

    // ℹ️ We no loner needs this overflow styles as perfect scrollbar applies it
    // overflow-x: hidden;

    // // ℹ️ We used `overflow-y` instead of `overflow` to mitigate overflow x. Revert back if any issue found.
    // overflow-y: auto;
  }

  .nav-item-title {
    overflow: hidden;
    margin-inline-end: auto;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  // 👉 Collapsed
  .layout-vertical-nav-collapsed & {
    &:not(.hovered) {
      inline-size: variables.$layout-vertical-nav-collapsed-width;
    }
  }
}

// Small screen vertical nav transition
@media (max-width: 1279px) {
  .layout-vertical-nav {
    &:not(.visible) {
      transform: translateX(-#{variables.$layout-vertical-nav-width});

      @include mixins.rtl {
        transform: translateX(variables.$layout-vertical-nav-width);
      }
    }

    transition: transform 0.25s ease-in-out;
  }
}
</style>
