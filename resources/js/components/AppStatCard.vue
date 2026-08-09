<script setup>
/**
 * The metric tile used by every dashboard (admin, finance, attendance and the
 * three portals), replacing three near-identical implementations.
 *
 * Hierarchy is deliberately metric-first: the number is the largest thing in
 * the card, the label sits above it as quiet supporting text, and the icon is
 * small and off to the side. The previous version led with a 48px avatar and
 * rendered the value at `text-h5`, which made the decoration louder than the
 * data.
 */
import { computed } from 'vue'

const props = defineProps({
  title: { type: String, required: true },
  value: { type: [String, Number], default: null },
  icon: { type: String, default: null },
  color: { type: String, default: 'primary' },
  hint: { type: String, default: null },

  /** Signed change vs. the previous period, e.g. `+12.5`. */
  trend: { type: Number, default: null },
  trendLabel: { type: String, default: null },
})

/*
 * A null metric means "not measured" — the owning module may be disabled.
 * An em dash says that honestly; a 0 would claim the school has no students.
 */
const displayValue = computed(() =>
  props.value === null || props.value === undefined ? '—' : props.value)

const trendColor = computed(() => {
  if (!props.trend) return 'medium-emphasis'

  return props.trend > 0 ? 'success' : 'error'
})

const trendIcon = computed(() => (props.trend > 0 ? 'bx-trending-up' : 'bx-trending-down'))
</script>

<template>
  <VCard class="app-stat-card">
    <VCardText>
      <div class="d-flex align-start justify-space-between gap-3">
        <span class="text-body-2 text-medium-emphasis">{{ title }}</span>

        <VIcon
          v-if="icon"
          :icon="icon"
          size="20"
          :color="color"
          class="flex-shrink-0"
        />
      </div>

      <div class="text-h3 app-numeric mt-2">
        {{ displayValue }}
      </div>

      <div
        v-if="trend !== null || hint"
        class="d-flex align-center flex-wrap gap-2 mt-2"
      >
        <span
          v-if="trend !== null"
          class="d-inline-flex align-center text-body-2 font-weight-medium"
          :class="`text-${trendColor}`"
        >
          <VIcon
            :icon="trendIcon"
            size="16"
            class="me-1"
          />
          <span class="app-numeric">{{ Math.abs(trend) }}%</span>
        </span>

        <span
          v-if="hint || trendLabel"
          class="text-body-2 text-medium-emphasis"
        >
          {{ trendLabel ?? hint }}
        </span>
      </div>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.app-stat-card {
  block-size: 100%;
}
</style>
