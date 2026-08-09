<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  student: { type: Object, required: true },
  summary: { type: Object, required: true },
  timeline: { type: Object, default: () => ({ data: [] }) },
  semesters: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const semesterId = ref(props.filters.semester_id ?? null)
const dateFrom = ref(props.filters.date_from ?? null)
const dateTo = ref(props.filters.date_to ?? null)

let filterTimer

function applyFilters() {
  router.get(`/attendance/students/${props.student.id}`, {
    semester_id: semesterId.value || undefined,
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
  }, { preserveState: true, preserveScroll: true, replace: true })
}

watch([semesterId], applyFilters)
watch([dateFrom, dateTo], () => {
  clearTimeout(filterTimer)
  filterTimer = setTimeout(applyFilters, 350)
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
            v-model="semesterId"
            :label="t('attendanceRecords.semester')"
            :items="semesterOptions"
            density="compact"
            clearable
            style="max-inline-size: 220px;"
          />

          <VTextField
            v-model="dateFrom"
            type="date"
            :label="t('attendanceRecords.dateFrom')"
            density="compact"
            clearable
            style="max-inline-size: 190px;"
          />

          <VTextField
            v-model="dateTo"
            type="date"
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
      <VCard>
        <VCardItem>
          <VCardTitle>{{ t('attendanceStudent.timelineTitle') }}</VCardTitle>
        </VCardItem>

        <VDivider />

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
