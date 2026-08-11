<script setup>
/**
 * Project-wide searchable dropdown.
 *
 * This component is deliberately named `VSelect`. `vite-plugin-vuetify` is
 * configured in vite.config.js with `autoImport: { ignore: ['VSelect'] }`, so
 * it leaves the tag alone and `unplugin-vue-components` — which scans
 * `resources/js/components` — resolves it here instead. Every `<VSelect>`
 * already written across the application therefore picks this up with no page
 * edit and no new prop to remember.
 *
 * Search is a real `VTextField` pinned to the top of the open menu via the
 * `prepend-item` slot, not typing into the closed activator. The user asked to
 * *see* a search box when the dropdown opens, and an autocomplete's activator
 * typing is invisible until you already know it is there.
 *
 * Everything else — v-model, items, item-title/value, clearable, multiple,
 * chips, rules, error-messages, return-object, slots, icons — is forwarded
 * untouched via `v-bind="$attrs"` and a slot passthrough.
 *
 * This is UI filtering only. `items` is whatever the page decided the user may
 * see; search narrows that list and never widens it, so business rules and
 * server-side authorization remain authoritative.
 */
import { computed, nextTick, ref, useAttrs, watch } from 'vue'
import { useI18n } from 'vue-i18n'

// Imported under another name on purpose: the bare `VSelect` tag resolves back
// to this file, which would recurse.
import { VSelect as VuetifySelect } from 'vuetify/components/VSelect'
import { VTextField } from 'vuetify/components/VTextField'
import { VListItem } from 'vuetify/components/VList'
import { VDivider } from 'vuetify/components/VDivider'
import { matchesSearchQuery } from '@/utils/searchNormalize'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  /**
   * Tri-state opt-out. `null` (the default) lets the item count decide;
   * `true` forces the search field on a short list; `false` pins the plain
   * select for a control where typing makes no sense — a per-page size, a
   * yes/no toggle.
   *
   * `String` is in the type list to defeat Vue's Boolean casting, which would
   * otherwise turn "prop absent" into `false` and disable search everywhere.
   */
  searchable: { type: [Boolean, String], default: null },

  /**
   * Only genuinely trivial lists stay plain. The default of 5 covers the
   * yes/no, status and per-page controls where a search box is pure noise;
   * anything above that gets search.
   */
  searchThreshold: { type: Number, default: 5 },

  /** Mirrors Vuetify's own props so we can read titles off each item. */
  items: { type: Array, default: () => [] },
  itemTitle: { type: [String, Function], default: 'title' },
})

const attrs = useAttrs()
const { t } = useI18n()
const slots = defineSlots()

const query = ref('')
const searchInput = ref(null)

const isSearchable = computed(() => {
  // A bare `searchable` attribute arrives as '' — treat it as an opt-in.
  if (props.searchable === '')
    return true

  if (props.searchable !== null)
    return props.searchable

  return props.items.length >= props.searchThreshold
})

/**
 * Resolves the string a given item displays, honouring `item-title` in both
 * its string-key and function forms so search always compares against exactly
 * what the user is reading in the active locale. No locale is named here.
 */
const titleOf = item => {
  if (typeof props.itemTitle === 'function')
    return props.itemTitle(item)

  if (item !== null && typeof item === 'object')
    return item[props.itemTitle]

  return item
}

/*
 * Filtering happens here rather than in a watcher, so nothing recomputes while
 * the menu is shut or the query is empty — in that case the original array is
 * passed straight through by reference, keeping large lists cheap.
 */
const filteredItems = computed(() => {
  if (!isSearchable.value || !query.value)
    return props.items

  return props.items.filter(item => matchesSearchQuery(titleOf(item), query.value))
})

const hasNoResults = computed(() => Boolean(query.value) && filteredItems.value.length === 0)

/** A stale query would silently hide options the next time the menu opens. */
const onMenuToggle = isOpen => {
  if (isOpen) {
    // Focus the field once the menu has actually rendered.
    nextTick(() => searchInput.value?.focus())

    return
  }

  query.value = ''
}

// Swapping the option set out from under a live query would show a list that
// does not match what is typed — happens whenever a parent select changes.
watch(() => props.items, () => {
  query.value = ''
})
</script>

<template>
  <VuetifySelect
    v-bind="attrs"
    :items="filteredItems"
    :item-title="itemTitle"
    :menu-props="{ contentClass: 'app-searchable-select__content' }"
    @update:menu="onMenuToggle"
  >
    <!--
      The search field lives inside the menu, above the options, and is only
      rendered for lists that earned it.
    -->
    <template
      v-if="isSearchable"
      #prepend-item
    >
      <div class="app-searchable-select__search">
        <VTextField
          ref="searchInput"
          v-model="query"
          :placeholder="t('common.searchPlaceholder')"
          density="compact"
          variant="outlined"
          hide-details
          clearable
          autofocus
          prepend-inner-icon="bx-search"
          @keydown.stop
          @click.stop
        />
      </div>

      <VDivider />

      <VListItem
        v-if="hasNoResults"
        :title="t('common.noResultsTitle')"
        disabled
      />
    </template>

    <!-- Everything the page itself passed through. -->
    <template
      v-for="(_, name) in slots"
      #[name]="slotProps"
    >
      <slot
        :name="name"
        v-bind="slotProps ?? {}"
      />
    </template>
  </VuetifySelect>
</template>

<style lang="scss">
.app-searchable-select__search {
  position: sticky;
  z-index: 1;
  padding: 8px;
  background: rgb(var(--v-theme-surface));
  inset-block-start: 0;
}
</style>
