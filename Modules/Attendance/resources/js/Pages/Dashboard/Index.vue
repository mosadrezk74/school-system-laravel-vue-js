<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useTheme } from 'vuetify'

const props = defineProps({
  classes: { type: Array, default: () => [] },
  semesters: { type: Array, default: () => [] },
  dashboard: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()
const theme = useTheme()

const classId = ref(props.filters.class_id ?? null)
const semesterId = ref(props.filters.semester_id ?? null)

watch([classId, semesterId], () => {
  router.get('/attendance/dashboard', {
    class_id: classId.value || undefined,
    semester_id: semesterId.value || undefined,
  }, { preserveState: true, preserveScroll: true, replace: true })
})

const classOptions = computed(() =>
  props.classes.map(c => ({
    title: [c.name, c.section_code].filter(Boolean).join(' · '),
    value: c.id,
  })))

const semesterOptions = computed(() =>
  props.semesters
    .map(s => ({ title: s.name, value: s.id })))

const tiles = computed(() => [
  { key: 'todayAttendance', value: `${props.dashboard.today.attendance_percentage}%`, icon: 'bx-check-square', color: 'primary' },
  { key: 'todayAbsent', value: props.dashboard.today.absent_count, icon: 'bx-x-circle', color: 'error' },
  { key: 'todayLate', value: props.dashboard.today.late_count, icon: 'bx-time-five', color: 'warning' },
  { key: 'todayExcused', value: props.dashboard.today.excused_count, icon: 'bx-note', color: 'info' },
  { key: 'todayOther', value: props.dashboard.today.other_count, icon: 'bx-dots-horizontal-rounded', color: 'secondary' },
  { key: 'weekAttendance', value: `${props.dashboard.week_attendance_percentage}%`, icon: 'bx-calendar-week', color: 'success' },
  { key: 'monthAttendance', value: `${props.dashboard.month_attendance_percentage}%`, icon: 'bx-calendar', color: 'success' },
])

const trendSeries = computed(() => [{
  name: t('attendanceDashboard.attendancePercentage'),
  data: props.dashboard.trend.map(point => point.attendance_percentage),
}])

const trendOptions = computed(() => ({
  chart: { type: 'area', toolbar: { show: false }, zoom: { enabled: false } },
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth', width: 2 },
  colors: [theme.current.value.colors.primary],
  xaxis: {
    categories: props.dashboard.trend.map(point => point.date),
    labels: { rotate: -45, hideOverlappingLabels: true },
  },
  yaxis: { min: 0, max: 100, labels: { formatter: value => `${Math.round(value)}%` } },
  tooltip: { y: { formatter: value => `${value}%` } },
  noData: { text: t('attendanceDashboard.noTrendData') },
}))

const distributionSeries = computed(() =>
  props.dashboard.distribution.map(slice => slice.total))

const distributionOptions = computed(() => ({
  chart: { type: 'donut' },
  labels: props.dashboard.distribution.map(slice => slice.label),
  colors: [
    theme.current.value.colors.success,
    theme.current.value.colors.error,
    theme.current.value.colors.warning,
    theme.current.value.colors.info,
    theme.current.value.colors.secondary,
  ],
  legend: { position: 'bottom' },
  noData: { text: t('attendanceDashboard.noTrendData') },
}))

const hasDistribution = computed(() =>
  distributionSeries.value.some(value => value > 0))
</script>

<template>
  <Head :title="t('attendanceDashboard.title')" />

  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardItem>
          <VCardTitle>{{ t('attendanceDashboard.title') }}</VCardTitle>
          <VCardSubtitle>{{ t('attendanceDashboard.subtitle') }}</VCardSubtitle>
        </VCardItem>

        <VDivider />

        <VCardText class="d-flex flex-wrap gap-4">

          <VSelect
            v-model="semesterId"
            :label="t('attendanceRecords.semester')"
            :items="semesterOptions"
            density="compact"
            clearable
            style="max-inline-size: 220px;"
          />

          <VSelect
            v-model="classId"
            :label="t('attendanceRecords.class')"
            :items="classOptions"
            density="compact"
            clearable
            style="max-inline-size: 240px;"
          />
        </VCardText>
      </VCard>
    </VCol>

    <VCol
      v-for="tile in tiles"
      :key="tile.key"
      cols="12"
      sm="6"
      lg="3"
    >
      <VCard>
        <VCardText>
          <div class="d-flex align-center gap-3 mb-3">
            <VAvatar
              rounded
              size="40"
              :color="tile.color"
              variant="tonal"
            >
              <VIcon
                :icon="tile.icon"
                size="22"
              />
            </VAvatar>
            <span class="text-body-1 text-medium-emphasis">
              {{ t(`attendanceDashboard.${tile.key}`) }}
            </span>
          </div>

          <h4 class="text-h4">
            {{ tile.value }}
          </h4>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="8">
      <VCard>
        <VCardItem>
          <VCardTitle>{{ t('attendanceDashboard.trendTitle') }}</VCardTitle>
          <VCardSubtitle>{{ t('attendanceDashboard.trendSubtitle') }}</VCardSubtitle>
        </VCardItem>
        <VCardText>
          <VueApexCharts
            type="area"
            height="320"
            :options="trendOptions"
            :series="trendSeries"
          />
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="4">
      <VCard>
        <VCardItem>
          <VCardTitle>{{ t('attendanceDashboard.distributionTitle') }}</VCardTitle>
          <VCardSubtitle>{{ t('attendanceDashboard.distributionSubtitle') }}</VCardSubtitle>
        </VCardItem>
        <VCardText>
          <VueApexCharts
            v-if="hasDistribution"
            type="donut"
            height="320"
            :options="distributionOptions"
            :series="distributionSeries"
          />
          <div
            v-else
            class="text-medium-emphasis"
          >
            {{ t('attendanceDashboard.noTrendData') }}
          </div>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
