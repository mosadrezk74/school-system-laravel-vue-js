<script setup>
import { computed, ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { isNavLinkActive } from '@layouts/utils'

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
})

const page = usePage()

/**
 * Destinations owned by this group, including nested groups, so a match at any
 * depth counts as "my subtree is active".
 *
 * @param {object} item
 * @returns {string[]}
 */
const collectPaths = item => (item.children ?? []).flatMap(child =>
  (child.children ? collectPaths(child) : (child.to ? [child.to] : [])))

/** True when the current URL lives anywhere inside this group. */
const isGroupActive = computed(() =>
  collectPaths(props.item).some(to => isNavLinkActive(to, page.url)))

/*
 * Opened by default when it owns the current page. Previously this was a bare
 * `ref(false)`, so landing on a nested route — a direct link, a refresh, a
 * back button — left the parent collapsed and the active child invisible.
 */
const isOpen = ref(isGroupActive.value)

/*
 * Re-open on navigation into the subtree, but never force it *closed*: a user
 * who deliberately expanded a group should keep it open while they browse
 * elsewhere.
 */
watch(isGroupActive, active => {
  if (active)
    isOpen.value = true
})
</script>

<template>
  <li
    class="nav-group"
    :class="[isOpen && 'open', isGroupActive && 'active']"
  >
    <div
      class="nav-group-label"
      role="button"
      tabindex="0"
      :aria-expanded="isOpen"
      @click="isOpen = !isOpen"
      @keydown.enter.prevent="isOpen = !isOpen"
      @keydown.space.prevent="isOpen = !isOpen"
    >
      <VIcon
        :icon="item.icon || 'bxs-circle'"
        class="nav-item-icon"
      />
      <span class="nav-item-title">{{ item.title }}</span>
      <span
        class="nav-item-badge"
        :class="item.badgeClass"
      >
        {{ item.badgeContent }}
      </span>
      <VIcon
        icon="bx-chevron-right"
        class="nav-group-arrow"
      />
    </div>
    <div class="nav-group-children-wrapper">
      <ul class="nav-group-children">
        <slot />
      </ul>
    </div>
  </li>
</template>

<style lang="scss">
.layout-vertical-nav {
  .nav-group {
    &-label {
      display: flex;
      align-items: center;
      cursor: pointer;
    }

    .nav-group-children-wrapper {
      display: grid;
      grid-template-rows: 0fr;
      transition: grid-template-rows 0.3s ease-in-out;

      .nav-group-children {
        overflow: hidden;
      }
    }

    &.open {
      .nav-group-children-wrapper {
        grid-template-rows: 1fr;
      }
    }
  }
}
</style>
