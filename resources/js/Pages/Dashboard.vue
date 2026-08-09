<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppStatCard from '@/components/AppStatCard.vue'
import AppEmptyState from '@/components/AppEmptyState.vue'

const props = defineProps({
  stats: { type: Object, required: true },
  currentYear: { type: Object, default: null },
})

const { t } = useI18n()
const page = usePage()

const enabledModules = computed(() => page.props.enabledModules ?? [])

const isModuleEnabled = name => !name || enabledModules.value.includes(name)

/*
 * Tiles are still derived from the `stats` prop exactly as before — the same
 * keys, the same module gating. Only the presentation changed.
 */
const tiles = computed(() => [
  { key: 'students', icon: 'bx-user-voice', value: props.stats.students, hint: t('dashboard.studentsHint'), module: 'Student' },
  { key: 'teachers', icon: 'bx-chalkboard', value: props.stats.teachers, hint: null, module: 'Teacher' },
  { key: 'attendanceToday', icon: 'bx-check-square', value: props.stats.attendance, hint: null, module: 'Attendance' },
  { key: 'outstandingFees', icon: 'bx-receipt', value: props.stats.outstandingFees, hint: null, module: 'Finance' },
].filter(tile => isModuleEnabled(tile.module)))
</script>

<template>
  <Head :title="t('dashboard.title')" />

  <AppPageHeader
    :title="t('dashboard.greeting')"
    :subtitle="currentYear
      ? `${t('dashboard.currentYear')}: ${currentYear.name}`
      : t('dashboard.noCurrentYear')"
  >
    <template #actions>
      <VBtn
        variant="tonal"
        color="secondary"
        prepend-icon="bx-calendar"
        href="/academic/academic-years"
        :as="Link"
      >
        {{ t('nav.academicYears') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <!--
    An auto-fitting grid rather than a computed column span: the previous
    `Math.floor(12 / tiles.length)` produced a 4-column span for 3 tiles,
    leaving a ragged gap, and broke entirely when a module was disabled.
  -->
  <div class="dashboard-metrics">
    <AppStatCard
      v-for="tile in tiles"
      :key="tile.key"
      :title="t(`dashboard.${tile.key}`)"
      :value="tile.value"
      :icon="tile.icon"
      :hint="tile.hint"
    />
  </div>

  <VCard class="mt-6">
    <VCardItem>
      <VCardTitle>{{ t('dashboard.needsAttention') }}</VCardTitle>
    </VCardItem>

    <AppEmptyState
      icon="bx-check-circle"
      :title="t('dashboard.noData')"
    />
  </VCard>
</template>

<style lang="scss" scoped>
.dashboard-metrics {
  display: grid;
  gap: var(--app-space-4);

  /*
    One column on a phone, then as many ~15rem columns as fit. Tiles stay the
    same width whether the school has two modules enabled or six.
  */
  grid-template-columns: 1fr;

  @media (min-width: 600px) {
    grid-template-columns: repeat(auto-fit, minmax(15rem, 1fr));
  }
}
</style>
