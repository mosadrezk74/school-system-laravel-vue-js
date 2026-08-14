<script setup>
/**
 * Renders an index page's filter controls from the schema its Query published.
 *
 * The page supplies no widget decisions: type, operators and option lists all
 * arrive from the backend, which is also the only thing that decides what may
 * be filtered on. That leaves one definition per field instead of the two — a
 * Vue control and a server filter — that had drifted apart across the modules.
 *
 * Drops into AppDataTable's existing `#filters` slot; nothing about the table
 * changes.
 */
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppFilterControl from '@/components/AppFilterControl.vue'

const props = defineProps({
  /** Filter schema from `Query::filterSchema()`. */
  definitions: { type: Array, required: true },

  /** The `filters` object of refs from `useIndexFilters`. */
  filters: { type: Object, required: true },

  /** Keys currently carrying a value, from `useIndexFilters`. */
  activeKeys: { type: Array, default: () => [] },

  /** Fields past this many fold away behind a "more filters" panel. */
  collapseAfter: { type: Number, default: 4 },

  /**
   * One field per row, for a narrow container.
   *
   * The grid below sizes columns with `md`, and Vuetify's breakpoints follow
   * the viewport rather than the parent — so inside a sidebar or a popover on
   * a desktop screen, `md=3` still resolves to a quarter of that narrow box
   * and every label truncates. A caller that is not full-width opts out of the
   * grid entirely with this.
   */
  stacked: { type: Boolean, default: false },
})

const emit = defineEmits(['clear', 'clear-all'])

const { t, te } = useI18n()

/**
 * A definition's `label` is a translation key chosen by the backend. Falling
 * back to the raw key keeps a newly added filter usable before its string is
 * translated, rather than rendering a blank label.
 */
const labelFor = definition =>
  (definition.label && te(definition.label) ? t(definition.label) : definition.key)

const isRange = definition => ['date-range', 'number-range'].includes(definition.type)

/**
 * Fields that render two controls in one cell — a range's two bounds, or an
 * operator select beside its input — get half a row instead of a quarter.
 *
 * At a quarter row the operator select alone took most of the width and the
 * input next to it was too narrow to read what had been typed into it.
 */
const isWide = definition => isRange(definition)
  || (definition.operators?.length > 1 && ['text', 'number'].includes(definition.type))

/**
 * Grid width for a field. Undefined when stacked, which leaves only `cols=12`
 * on the column so it spans the full row at every breakpoint.
 */
const colWidth = definition => (props.stacked ? undefined : (isWide(definition) ? 6 : 3))

/** Query-string keys a definition owns, so a range clears as one unit. */
const keysOf = definition => (isRange(definition)
  ? [`${definition.key}_from`, `${definition.key}_to`]
  : [definition.key])

const primary = computed(() => props.definitions.slice(0, props.collapseAfter))
const secondary = computed(() => props.definitions.slice(props.collapseAfter))

/**
 * Chips describe the *field*, not each query parameter, so a date range shows
 * as one removable chip rather than two.
 */
const chips = computed(() => props.definitions
  .filter(definition => keysOf(definition).some(key => props.activeKeys.includes(key)))
  .map(definition => ({
    key: definition.key,
    label: labelFor(definition),
    value: chipValue(definition),
  })))

/** The human-readable current value, so a chip reads "Status: Active". */
function chipValue(definition) {
  const read = key => props.filters[key]?.value

  if (isRange(definition))
    return keysOf(definition).map(read).filter(Boolean).join(' — ')

  const value = read(definition.key)

  if (definition.type === 'boolean')
    return value ? t('filters.yes') : t('filters.no')

  if (Array.isArray(value)) {
    return value
      .map(item => definition.options?.find(option => option.value === item)?.title ?? item)
      .join(', ')
  }

  return definition.options?.find(option => option.value === value)?.title ?? value
}
</script>

<template>
  <div class="app-filter-bar">
    <VRow dense>
      <VCol
        v-for="definition in primary"
        :key="definition.key"
        cols="12"
        :md="colWidth(definition)"
      >
        <slot
          :name="`filter.${definition.key}`"
          :definition="definition"
        >
          <AppFilterControl
            :definition="definition"
            :filters="filters"
            :label="labelFor(definition)"
          />
        </slot>
      </VCol>
    </VRow>

    <!--
      Everything past `collapseAfter` folds away. A page with a dozen filters
      should not push its own table below the fold.
    -->
    <VExpansionPanels
      v-if="secondary.length"
      variant="accordion"
      class="app-filter-bar__more"
    >
      <VExpansionPanel :title="t('filters.more')">
        <VExpansionPanelText>
          <VRow dense>
            <VCol
              v-for="definition in secondary"
              :key="definition.key"
              cols="12"
              :md="colWidth(definition)"
            >
              <slot
                :name="`filter.${definition.key}`"
                :definition="definition"
              >
                <AppFilterControl
                  :definition="definition"
                  :filters="filters"
                  :label="labelFor(definition)"
                />
              </slot>
            </VCol>
          </VRow>
        </VExpansionPanelText>
      </VExpansionPanel>
    </VExpansionPanels>

    <!-- Active filters, each removable on its own. -->
    <div
      v-if="chips.length"
      class="app-filter-bar__active"
    >
      <VChip
        v-for="chip in chips"
        :key="chip.key"
        size="small"
        closable
        @click:close="emit('clear', chip.key)"
      >
        {{ chip.label }}: {{ chip.value }}
      </VChip>

      <VBtn
        variant="text"
        size="small"
        @click="emit('clear-all')"
      >
        {{ t('filters.clearAll') }}
      </VBtn>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.app-filter-bar {
  inline-size: 100%;

  &__more {
    margin-block-start: var(--app-space-2);
  }

  &__active {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    margin-block-start: var(--app-space-3);
    gap: var(--app-space-2);
  }
}
</style>
