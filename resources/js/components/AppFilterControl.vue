<script setup>
/**
 * One filter control, chosen by the `type` its Query declared.
 *
 * Split out of AppFilterBar so the same field renders identically whether it
 * sits in the visible row or inside the collapsed "more filters" panel —
 * otherwise the two would be two copies of the same markup, which is exactly
 * the drift this whole system exists to remove.
 */
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppDateField from '@/components/AppDateField.vue'

const props = defineProps({
  /** One entry from `Query::filterSchema()`. */
  definition: { type: Object, required: true },

  /** The `filters` object of refs from `useIndexFilters`. */
  filters: { type: Object, required: true },

  label: { type: String, required: true },
})

const { t } = useI18n()

const type = computed(() => props.definition.type)

const isRange = computed(() => ['date-range', 'number-range'].includes(type.value))

/** Only worth an operator select when there is more than one choice. */
const showOperator = computed(() =>
  props.definition.operators?.length > 1 && ['text', 'number'].includes(type.value))

const operatorItems = computed(() => (props.definition.operators ?? []).map(operator => ({
  title: t(`filters.operators.${operator}`),
  value: operator,
})))

/**
 * "Any" is prepended so a select can be cleared back to no filter at all —
 * except on fields the server always defaults (the academic year), where an
 * empty choice would fall back to the default rather than widen the list.
 */
const selectItems = computed(() => {
  const options = props.definition.options ?? []

  return props.definition.clearable === false
    ? options
    : [{ title: t('filters.any'), value: null }, ...options]
})

const booleanItems = computed(() => [
  { title: t('filters.any'), value: null },
  { title: t('filters.yes'), value: true },
  { title: t('filters.no'), value: false },
])

/**
 * A field missing from the state means the page passed a schema the composable
 * did not build state for; rendering nothing beats crashing the whole page.
 */
const has = key => Boolean(props.filters[key])

const boundKey = computed(() => props.definition.key)
const fromKey = computed(() => `${props.definition.key}_from`)
const toKey = computed(() => `${props.definition.key}_to`)
const operatorKey = computed(() => `${props.definition.key}_op`)
</script>

<template>
  <div class="app-filter-control">
    <!-- Range: two bounds, either of which may stand alone. -->
    <div
      v-if="isRange"
      class="d-flex align-center gap-2"
    >
      <template v-if="type === 'date-range'">
        <AppDateField
          v-if="has(fromKey)"
          v-model="filters[fromKey].value"
          :label="`${label} — ${t('filters.from')}`"
        />
        <AppDateField
          v-if="has(toKey)"
          v-model="filters[toKey].value"
          :label="`${label} — ${t('filters.to')}`"
        />
      </template>

      <template v-else>
        <VTextField
          v-if="has(fromKey)"
          v-model="filters[fromKey].value"
          type="number"
          :label="`${label} — ${t('filters.from')}`"
          clearable
        />
        <VTextField
          v-if="has(toKey)"
          v-model="filters[toKey].value"
          type="number"
          :label="`${label} — ${t('filters.to')}`"
          clearable
        />
      </template>
    </div>

    <div
      v-else-if="has(boundKey)"
      class="d-flex align-center gap-2"
    >
      <VSelect
        v-if="showOperator && has(operatorKey)"
        v-model="filters[operatorKey].value"
        :items="operatorItems"
        :label="t('filters.operator')"
        density="compact"
        class="app-filter-control__operator"
      />

      <VTextField
        v-if="type === 'text'"
        v-model="filters[boundKey].value"
        maxlength="100"
        :label="label"
        clearable
      />

      <VTextField
        v-else-if="type === 'number'"
        v-model="filters[boundKey].value"
        type="number"
        :label="label"
        clearable
      />

      <AppDateField
        v-else-if="type === 'date'"
        v-model="filters[boundKey].value"
        :label="label"
      />

      <VSelect
        v-else-if="type === 'boolean'"
        v-model="filters[boundKey].value"
        :items="booleanItems"
        :label="label"
      />

      <VSelect
        v-else-if="type === 'multi-select'"
        v-model="filters[boundKey].value"
        :items="definition.options ?? []"
        :label="label"
        multiple
        chips
        clearable
      />

      <VSelect
        v-else
        v-model="filters[boundKey].value"
        :items="selectItems"
        :label="label"
      />
    </div>
  </div>
</template>

<style lang="scss" scoped>
.app-filter-control {
  inline-size: 100%;

  &__operator {
    flex: 0 0 auto;
    inline-size: 8rem;
  }

  /*
    The field beside an operator select has to be allowed to shrink. A flex item
    defaults to `min-width: auto`, so the input keeps its intrinsic width and the
    pair overflows its column instead of sharing it — which left the actual input
    a sliver next to a full-width operator box.
  */
  :deep(.v-input:not(.app-filter-control__operator)) {
    min-inline-size: 0;
  }
}
</style>
