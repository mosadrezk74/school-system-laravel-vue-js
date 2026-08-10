<script setup>
/**
 * Thin wrapper over the application-wide <AppStatCard>.
 *
 * Kept as its own component so the three portals' existing call sites — and
 * their `title`/`value`/`icon`/`color`/`hint` props — continue to work
 * unchanged, while the visual definition of "a metric tile" now lives in one
 * place shared with the admin dashboards.
 */
import AppStatCard from '@/components/AppStatCard.vue'

defineProps({
  title: { type: String, required: true },
  value: { type: [String, Number], default: null },
  icon: { type: String, default: 'bx-bar-chart-alt-2' },
  color: { type: String, default: 'primary' },
  hint: { type: String, default: null },
})
</script>

<template>
  <AppStatCard
    :title="title"
    :value="value"
    :icon="icon"
    :color="color"
    :hint="hint"
  />
</template>
