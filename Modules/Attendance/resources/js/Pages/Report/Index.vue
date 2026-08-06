<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  classes: { type: Array, default: () => [] },
  academicYears: { type: Array, default: () => [] },
  semesters: { type: Array, default: () => [] },
  rows: { type: Object, default: () => ({ data: [] }) },
  statistics: { type: Object, default: null },
  filters: { type: Object, default: () => ({}) },
  generated: { type: Boolean, default: false },
})

const { t } = useI18n()

const form = useForm({
  class_id: props.filters.class_id ?? null,
  academic_year_id: props.filters.academic_year_id ?? null,
  semester_id: props.filters.semester_id ?? null,
  date_from: props.filters.date_from ?? null,
  date_to: props.filters.date_to ?? null,
})

const classOptions = computed(() =>
  props.classes.map(c => ({
    title: [c.name, c.section_code].filter(Boolean).join(' · '),
    value: c.id,
  })))

const academicYearOptions = computed(() =>
  props.academicYears.map(y => ({ title: y.name, value: y.id })))

const semesterOptions = computed(() =>
  props.semesters
    .filter(s => !form.academic_year_id || s.academic_year_id === form.academic_year_id)
    .map(s => ({ title: s.name, value: s.id })))

const reportRows = computed(() => props.rows?.data ?? [])

const statCards = computed(() => {
  if (!props.statistics)
    return []

  return [
    { key: 'totalStudents', value: props.statistics.total_students, icon: 'bx-group', color: 'primary' },
    { key: 'averageAttendance', value: `${props.statistics.average_attendance_percentage}%`, icon: 'bx-trending-up', color: 'success' },
    { key: 'averageAbsence', value: `${props.statistics.average_absence_percentage}%`, icon: 'bx-trending-down', color: 'error' },
    { key: 'totalPresent', value: props.statistics.total_present, icon: 'bx-check', color: 'success' },
    { key: 'totalAbsent', value: props.statistics.total_absent, icon: 'bx-x', color: 'error' },
    { key: 'totalLate', value: props.statistics.total_late, icon: 'bx-time-five', color: 'warning' },
    { key: 'totalExcused', value: props.statistics.total_excused, icon: 'bx-note', color: 'info' },
    { key: 'totalOther', value: props.statistics.total_other, icon: 'bx-dots-horizontal-rounded', color: 'secondary' },
  ]
})

const headers = computed(() => [
  { title: t('attendanceReport.student'), key: 'student_name' },
  { title: t('attendanceReport.present'), key: 'present_count' },
  { title: t('attendanceReport.absent'), key: 'absent_count' },
  { title: t('attendanceReport.late'), key: 'late_count' },
  { title: t('attendanceReport.excused'), key: 'excused_count' },
  { title: t('attendanceReport.other'), key: 'other_count' },
  { title: t('attendanceReport.attendancePercentage'), key: 'attendance_percentage' },
  { title: t('attendanceReport.absencePercentage'), key: 'absence_percentage' },
])

function generate() {
  form.post('/attendance/report', { preserveScroll: true, preserveState: true })
}
</script>

<template>
  <Head :title="t('attendanceReport.title')" />

  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardItem>
          <VCardTitle>{{ t('attendanceReport.title') }}</VCardTitle>
          <VCardSubtitle>{{ t('attendanceReport.subtitle') }}</VCardSubtitle>
        </VCardItem>

        <VDivider />

        <VCardText class="d-flex flex-wrap gap-4">
          <VSelect
            v-model="form.academic_year_id"
            :label="t('attendanceRecords.academicYear')"
            :items="academicYearOptions"
            :error-messages="form.errors.academic_year_id"
            density="compact"
            clearable
            style="max-inline-size: 220px;"
          />

          <VSelect
            v-model="form.semester_id"
            :label="t('attendanceRecords.semester')"
            :items="semesterOptions"
            :error-messages="form.errors.semester_id"
            density="compact"
            clearable
            style="max-inline-size: 220px;"
          />

          <VSelect
            v-model="form.class_id"
            :label="t('attendanceRecords.class')"
            :items="classOptions"
            :error-messages="form.errors.class_id"
            density="compact"
            style="max-inline-size: 240px;"
          />

          <VTextField
            v-model="form.date_from"
            type="date"
            :label="t('attendanceRecords.dateFrom')"
            :error-messages="form.errors.date_from"
            density="compact"
            clearable
            style="max-inline-size: 190px;"
          />

          <VTextField
            v-model="form.date_to"
            type="date"
            :label="t('attendanceRecords.dateTo')"
            :error-messages="form.errors.date_to"
            density="compact"
            clearable
            style="max-inline-size: 190px;"
          />
        </VCardText>

        <VDivider />

        <VCardText class="d-flex justify-end">
          <VBtn
            prepend-icon="bx-bar-chart-alt-2"
            :loading="form.processing"
            :disabled="form.processing || !form.class_id"
            @click="generate"
          >
            {{ t('attendanceReport.generate') }}
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>

    <template v-if="generated">
      <VCol
        v-for="card in statCards"
        :key="card.key"
        cols="12"
        sm="6"
        md="3"
      >
        <VCard>
          <VCardText class="d-flex align-center gap-4">
            <VAvatar
              :color="card.color"
              variant="tonal"
              rounded
              size="42"
            >
              <VIcon :icon="card.icon" />
            </VAvatar>

            <div>
              <div class="text-caption text-medium-emphasis">
                {{ t(`attendanceReport.stats.${card.key}`) }}
              </div>
              <div class="text-h6">
                {{ card.value }}
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12">
        <VCard>
          <VCardItem>
            <VCardTitle>{{ t('attendanceReport.resultsTitle') }}</VCardTitle>
          </VCardItem>

          <VDivider />

          <VCardText v-if="!reportRows.length" class="text-medium-emphasis">
            {{ t('attendanceReport.empty') }}
          </VCardText>

          <VDataTable
            v-else
            :headers="headers"
            :items="reportRows"
            :items-per-page="-1"
            hide-default-footer
          >
            <template #item.student_name="{ item }">
              {{ item.student_name ?? '—' }}
            </template>

            <template #item.attendance_percentage="{ item }">
              <VChip
                :color="item.attendance_percentage >= 75 ? 'success' : item.attendance_percentage >= 50 ? 'warning' : 'error'"
                size="small"
                label
              >
                {{ item.attendance_percentage }}%
              </VChip>
            </template>

            <template #item.absence_percentage="{ item }">
              {{ item.absence_percentage }}%
            </template>
          </VDataTable>
        </VCard>
      </VCol>
    </template>
  </VRow>
</template>
