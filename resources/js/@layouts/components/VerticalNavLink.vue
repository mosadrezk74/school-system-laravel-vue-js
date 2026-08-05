<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  item: {
    type: null,
    required: true,
  },
})
</script>

<template>
  <li
    class="nav-link"
    :class="{ disabled: item.disable }"
  >
    <!--
      `item.to` is an internal destination and uses Inertia's <Link> so the
      visit stays client-side; `item.href` is an external URL and stays a
      plain anchor for a full page load.
    -->
    <Component
      :is="item.to ? Link : 'a'"
      :href="item.to || item.href"
      :target="item.target"
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
