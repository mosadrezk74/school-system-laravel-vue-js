<script setup>
/**
 * The list-page shell: filter toolbar, table, states and paginator in one
 * component.
 *
 * Every index page in the app had rebuilt this by hand — 36 copies of the
 * same VCard/VDataTable/VPagination arrangement, each with slightly different
 * padding, a different empty state and its own idea of what a loading list
 * looks like. This owns the arrangement; pages supply only the headers, the
 * rows and their own `item.*` cell templates, which pass straight through.
 *
 * Column and slot contracts are unchanged, so migrating a page is a markup
 * swap — no header key, prop name or response key is renamed.
 */
import { computed, useSlots } from 'vue'
import { useI18n } from 'vue-i18n'
import { useDisplay } from 'vuetify'
import AppEmptyState from '@/components/AppEmptyState.vue'
import AppPagination from '@/components/AppPagination.vue'
import TruncatedText from '@/components/TruncatedText.vue'

const props = defineProps({
  headers: { type: Array, required: true },
  items: { type: Array, default: () => [] },

  /** Laravel paginator `meta`; omit for a non-paginated list. */
  meta: { type: Object, default: null },
  loading: { type: Boolean, default: false },

  emptyTitle: { type: String, default: null },
  emptyDescription: { type: String, default: null },
  emptyIcon: { type: String, default: 'bx-folder-open' },

  /**
   * Below `md`, render each row as a stacked card instead of forcing the
   * table into a horizontal scroll. The brief explicitly rejects "blindly
   * force every table into horizontal scrolling".
   */
  mobileCards: { type: Boolean, default: true },

  /** Header key whose cell is the row's title in mobile card mode. */
  mobileTitleKey: { type: String, default: null },

  itemKey: { type: String, default: 'id' },

  /**
   * Visible length for text cells the table renders itself, before they are
   * truncated and given a hover tooltip.
   */
  truncateAt: { type: Number, default: 30 },
})

defineEmits(['page'])

const { t } = useI18n()
const { mdAndUp } = useDisplay()
const slots = useSlots()

const isEmpty = computed(() => !props.loading && props.items.length === 0)

const useCards = computed(() => props.mobileCards && !mdAndUp.value)

/** Columns rendered as label/value pairs on a mobile card. */
const dataHeaders = computed(() =>
  props.headers.filter(h => h.key !== 'actions' && h.key !== props.mobileTitleKey))

const titleHeader = computed(() =>
  props.headers.find(h => h.key === props.mobileTitleKey) ?? props.headers[0])

/**
 * Cell slots are named `item.<key>` by VDataTable. Re-using the exact same
 * names in card mode means a page writes its cell markup once and gets both
 * layouts, with no per-page branching.
 *
 * @param {string} key
 * @returns {string}
 */
const cellSlot = key => `item.${key}`

const hasSlot = name => Boolean(slots[name])

/**
 * Text columns the table renders itself get truncated automatically.
 *
 * A page that supplies its own `#item.<key>` template is rendering something
 * the table cannot reason about — a chip, a link, a date — so it is left
 * alone; those cells opt in by using TruncatedText inside their own template.
 * A header can also force the decision either way with `truncate: true|false`.
 *
 * @param {{ key: string, truncate?: boolean }} header
 * @returns {boolean}
 */
function shouldTruncate(header) {
  if (typeof header.truncate === 'boolean') {
    return header.truncate
  }

  return header.key !== 'actions' && !hasSlot(cellSlot(header.key))
}

/**
 * Headers this component renders a truncating cell for. A key the page already
 * templated is excluded, so the page's own slot stays the single definition
 * and is never shadowed by a duplicate.
 */
const truncatedHeaders = computed(() =>
  props.headers.filter(header => !hasSlot(cellSlot(header.key)) && shouldTruncate(header)))
</script>

<template>
  <VCard class="app-data-table">
    <!-- Filter toolbar -->
    <div
      v-if="hasSlot('filters')"
      class="app-data-table__filters"
    >
      <slot name="filters" />
    </div>

    <VDivider v-if="hasSlot('filters')" />

    <!--
      Loading: a skeleton shaped like the table it replaces, so the layout
      does not jump when the rows arrive.
    -->
    <div
      v-if="loading"
      class="pa-4"
    >
      <VSkeletonLoader
        v-for="n in 5"
        :key="n"
        type="list-item-two-line"
        class="mb-1"
      />
    </div>

    <!-- Empty -->
    <AppEmptyState
      v-else-if="isEmpty"
      :icon="emptyIcon"
      :title="emptyTitle ?? t('common.noResultsTitle')"
      :description="emptyDescription ?? t('common.noResultsHint')"
    >
      <template
        v-if="hasSlot('empty-action')"
        #action
      >
        <slot name="empty-action" />
      </template>
    </AppEmptyState>

    <!-- Mobile: one card per row -->
    <div
      v-else-if="useCards"
      class="app-data-table__cards"
    >
      <div
        v-for="item in items"
        :key="item[itemKey]"
        class="app-data-table__card"
      >
        <div class="d-flex align-start justify-space-between gap-3">
          <div class="font-weight-medium">
            <slot
              :name="cellSlot(titleHeader.key)"
              :item="item"
            >
              <TruncatedText
                :text="item[titleHeader.key]"
                :length="truncateAt"
              />
            </slot>
          </div>

          <div
            v-if="hasSlot('item.actions')"
            class="d-flex align-center flex-shrink-0"
          >
            <slot
              name="item.actions"
              :item="item"
            />
          </div>
        </div>

        <dl class="app-data-table__pairs">
          <template
            v-for="header in dataHeaders"
            :key="header.key"
          >
            <dt class="text-body-2 text-medium-emphasis">
              {{ header.title }}
            </dt>
            <dd class="text-body-2">
              <slot
                :name="cellSlot(header.key)"
                :item="item"
              >
                <TruncatedText
                  :text="item[header.key]"
                  :length="truncateAt"
                />
              </slot>
            </dd>
          </template>
        </dl>
      </div>
    </div>

    <!-- Desktop / tablet: the real table -->
    <div
      v-else
      class="app-table-scroll"
    >
      <VDataTable
        :headers="headers"
        :items="items"
        :items-per-page="-1"
        hide-default-footer
        :item-value="itemKey"
      >
        <!--
          Forward every cell slot the page defined straight through to
          VDataTable, so existing `#item.name` templates keep working
          untouched.
        -->
        <template
          v-for="(_, name) in $slots"
          :key="name"
          #[name]="slotProps"
        >
          <slot
            :name="name"
            v-bind="slotProps ?? {}"
          />
        </template>

        <!--
          Columns the page did not template itself. VDataTable would print the
          raw value here; truncating instead keeps one long cell from setting
          the width of the whole column.
        -->
        <template
          v-for="header in truncatedHeaders"
          :key="`truncate-${header.key}`"
          #[cellSlot(header.key)]="{ item }"
        >
          <TruncatedText
            :text="item[header.key]"
            :length="truncateAt"
          />
        </template>
      </VDataTable>
    </div>

    <AppPagination
      v-if="!loading && !isEmpty"
      :meta="meta"
      @change="$emit('page', $event)"
    />
  </VCard>
</template>

<style lang="scss" scoped>
.app-data-table {
  &__filters {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    padding: var(--app-space-4);
    gap: var(--app-space-3);

    :deep(> .v-row) {
      flex: 1 1 100%;
      margin: 0;
    }

    :deep(> .v-input) {
      flex: 0 0 auto;
      inline-size: 100%;
    }

    @media (min-width: 600px) {
      :deep(> .v-input) {
        inline-size: 14rem;
      }

      :deep(> .v-input:first-child) {
        flex: 1 1 18rem;
        inline-size: auto;
        max-inline-size: 24rem;
      }
    }
  }

  &__cards {
    display: flex;
    flex-direction: column;
  }

  &__card {
    padding: var(--app-space-4);

    & + & {
      border-block-start: var(--app-border);
    }
  }

  /*
    Label/value pairs. `auto` for the label column lets a long Arabic label
    take the room it needs instead of wrapping every value onto its own line.
  */
  &__pairs {
    display: grid;
    align-items: baseline;
    margin-block-start: var(--app-space-3);
    gap: var(--app-space-1) var(--app-space-4);
    grid-template-columns: auto minmax(0, 1fr);

    dd {
      margin: 0;

      /* dd inherits no direction reset from dl in RTL; text-align must follow
         the reading direction rather than being pinned left. */
      text-align: end;
    }
  }
}
</style>
