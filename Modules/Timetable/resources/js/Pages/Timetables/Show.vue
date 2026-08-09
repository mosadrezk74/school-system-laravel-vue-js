<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import TimetableGrid from './_Grid.vue'

const props = defineProps({
  timetable: { type: Object, required: true },
  periods: { type: Array, default: () => [] },
  weekdays: { type: Array, default: () => [] },
})

const { t } = useI18n()

function cellKey(day, periodId) {
  return `${day}-${periodId}`
}

const entries = {}
for (const entry of props.timetable.entries ?? []) {
  entries[cellKey(entry.day_of_week, entry.period_id)] = {
    id: entry.id,
    subject_id: entry.subject?.id ?? null,
    teacher_id: entry.teacher?.id ?? null,
    classroom_id: entry.classroom?.id ?? null,
    is_online: entry.is_online ?? false,
  }
}

const subjects = (props.timetable.entries ?? [])
  .filter(entry => entry.subject)
  .map(entry => entry.subject)

const teachers = (props.timetable.entries ?? [])
  .filter(entry => entry.teacher)
  .map(entry => entry.teacher)
</script>

<template>
  <Head :title="t('timetables.grid.title')" />

  <VCard>
    <VCardItem>
      <VCardTitle>
        {{ timetable.class?.name }}
      </VCardTitle>
      <VCardSubtitle>
        {{ timetable.academic_year?.name }}
        <template v-if="timetable.semester?.name">
          — {{ timetable.semester.name }}
        </template>
      </VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <TimetableGrid
        :periods="periods"
        :weekdays="weekdays"
        :entries="entries"
        :subjects="subjects"
        :teachers="teachers"
        :editable="false"
        show-meeting-actions
      />
    </VCardText>
  </VCard>
</template>
