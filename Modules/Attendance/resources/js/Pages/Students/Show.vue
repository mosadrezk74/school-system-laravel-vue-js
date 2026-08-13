<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDateField from '@/components/AppDateField.vue'

const props = defineProps({
  student: { type: Object, required: true },
  summary: { type: Object, required: true },
  timeline: { type: Object, default: () => ({ data: [] }) },
  semesters: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

/*
 * These parameters define the report rather than narrow a list, but the URL
 * state, debounce and paging behaviour are the same — so they come from the
 * shared composable instead of a per-page copy.
 */
const { filters, goToPage } = useIndexFilters({
  url: `/attendance/students/${props.student.id}`,
  initial: {
    semester_id: props.filters.semester_id ?? null,
    date_from: props.filters.date_from ?? null,
    date_to: props.filters.date_to ?? null,
  },
})

const semesterOptions = computed(() =>
  props.semesters
    .map(s => ({ title: s.name, value: s.id })))

const rows = computed(() => props.timeline?.data ?? [])

const summaryTiles = computed(() => [
  { key: 'present', value: props.summary.present_count, color: 'success' },
  { key: 'absent', value: props.summary.absent_count, color: 'error' },
  { key: 'late', value: props.summary.late_count, color: 'warning' },
  { key: 'excused', value: props.summary.excused_count, color: 'info' },
  { key: 'other', value: props.summary.other_count, color: 'secondary' },
  { key: 'attendancePercentage', value: `${props.summary.attendance_percentage}%`, color: 'primary' },
])

const headers = computed(() => [
  { title: t('attendanceRecords.date'), key: 'date' },
  { title: t('attendanceRecords.status'), key: 'status' },
  { title: t('attendanceStudent.notes'), key: 'notes' },
  { title: t('attendanceStudent.teacher'), key: 'teacher' },
])

const statusColor = status => ({
  present: 'success',
  absent: 'error',
  late: 'warning',
  excused: 'info',
  other: 'secondary',
}[status] ?? 'secondary')
</script>

<template>
  <Head :title="t('attendanceStudent.title')" />

  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardText class="d-flex flex-wrap align-center gap-4">
          <VAvatar
            size="56"
            color="primary"
            variant="tonal"
          >
            <VIcon
              icon="bx-user"
              size="28"
            />
          </VAvatar>

          <div>
            <h5 class="text-h5 mb-1">
              {{ student.name ?? '—' }}
            </h5>
            <p class="text-body-2 text-medium-emphasis mb-0">
              {{ student.admission_number ?? '—' }}
              <template v-if="student.current_class">
                · {{ student.current_class.name }}
              </template>
              <template v-if="student.academic_year">
                · {{ student.academic_year.name }}
              </template>
            </p>
          </div>
        </VCardText>

        <VDivider />

        <VCardText class="d-flex flex-wrap gap-4">

          <VSelect
            v-model="filters.semester_id.value"
            :label="t('attendanceRecords.semester')"
            :items="semesterOptions"
            density="compact"
            clearable
            style="max-inline-size: 220px;"
          />

          <AppDateField
            v-model="filters.date_from.value"
            :label="t('attendanceRecords.dateFrom')"
            density="compact"
            clearable
            style="max-inline-size: 190px;"
          />

          <AppDateField
            v-model="filters.date_to.value"
            :label="t('attendanceRecords.dateTo')"
            density="compact"
            clearable
            style="max-inline-size: 190px;"
          />
        </VCardText>
      </VCard>
    </VCol>

    <VCol
      v-for="tile in summaryTiles"
      :key="tile.key"
      cols="6"
      sm="4"
      lg="2"
    >
      <VCard>
        <VCardText>
          <div class="text-caption text-medium-emphasis mb-1">
            {{ t(`attendanceReport.${tile.key}`) }}
          </div>
          <h5 :class="`text-h5 text-${tile.color}`">
            {{ tile.value }}
          </h5>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
  <AppPageHeader
    :title="t('attendanceStudent.timelineTitle')"
  />

  <VCard>

        <VCardText
          v-if="!rows.length"
          class="text-medium-emphasis"
        >
          {{ t('attendanceStudent.empty') }}
        </VCardText>

        <VDataTable
          v-else
          :headers="headers"
          :items="rows"
          :items-per-page="-1"
          hide-default-footer
        >
          <template #item.status="{ item }">
            <VChip
              :color="statusColor(item.status)"
              size="small"
              label
            >
              {{ t(`attendanceRecords.statuses.${item.status}`) }}
            </VChip>
          </template>

          <template #item.notes="{ item }">
            {{ item.notes || '—' }}
          </template>

          <template #item.teacher="{ item }">
            {{ item.teacher || '—' }}
          </template>
        </VDataTable>
      </VCard>
    </VCol>
  </VRow>
</template>
