<script setup>
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  periods: { type: Array, required: true },
  weekdays: { type: Array, required: true },
  entries: { type: Object, required: true },
  subjects: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] },
  editable: { type: Boolean, default: false },
  conflictCells: { type: Array, default: () => [] },
  showMeetingActions: { type: Boolean, default: false },
})

const emit = defineEmits(['cell-click'])

const { t } = useI18n()

function startMeeting(entry) {
  router.patch(`/meetings/timetable-entries/${entry.id}/start-meeting`)
}

function cellKey(day, periodId) {
  return `${day}-${periodId}`
}

function subjectName(subjectId) {
  return props.subjects.find(s => s.id === subjectId)?.name ?? ''
}

function teacherName(teacherId) {
  return props.teachers.find(tch => tch.id === teacherId)?.name ?? ''
}

function isConflict(day, periodId) {
  return props.conflictCells.includes(cellKey(day, periodId))
}

function onCellClick(day, period) {
  if (!props.editable || period.is_break)
    return

  emit('cell-click', { day, period })
}
</script>

<template>
  <div class="timetable-grid-wrapper">
    <table class="timetable-grid">
      <thead>
        <tr>
          <th class="period-col">
            {{ t('timetables.grid.period') }}
          </th>
          <th v-for="day in weekdays" :key="day.value">
            {{ day.label }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="period in periods" :key="period.id" :class="{ 'break-row': period.is_break }">
          <td class="period-col">
            <div class="text-body-2 font-weight-medium">
              {{ period.name }}
            </div>
            <div class="text-caption text-medium-emphasis">
              {{ period.start_time }} - {{ period.end_time }}
            </div>
          </td>

          <td
            v-for="day in weekdays"
            :key="day.value"
            class="grid-cell"
            :class="{
              editable: editable && !period.is_break,
              conflict: isConflict(day.value, period.id),
            }"
            @click="onCellClick(day.value, period)"
          >
            <div v-if="period.is_break" class="text-caption text-medium-emphasis text-center">
              {{ t('timetables.periods.isBreak') }}
            </div>

            <template v-else-if="entries[cellKey(day.value, period.id)]">
              <div class="d-flex align-center justify-center gap-1">
                <div class="text-body-2 font-weight-medium">
                  {{ subjectName(entries[cellKey(day.value, period.id)].subject_id) }}
                </div>
                <VIcon
                  v-if="entries[cellKey(day.value, period.id)].is_online"
                  icon="bx-video"
                  size="14"
                  color="success"
                />
              </div>
              <div class="text-caption text-medium-emphasis">
                {{ teacherName(entries[cellKey(day.value, period.id)].teacher_id) }}
              </div>
              <VBtn
                v-if="showMeetingActions && entries[cellKey(day.value, period.id)].is_online"
                size="x-small"
                variant="tonal"
                color="success"
                class="mt-1"
                @click.stop="startMeeting(entries[cellKey(day.value, period.id)])"
              >
                {{ t('timetables.grid.startMeeting') }}
              </VBtn>
            </template>

            <div v-else-if="editable" class="text-disabled text-center">
              <VIcon icon="bx-plus" size="18" />
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.timetable-grid-wrapper {
  overflow-x: auto;
}

.timetable-grid {
  border-collapse: collapse;
  inline-size: 100%;
  min-inline-size: 720px;
}

.timetable-grid th,
.timetable-grid td {
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  padding: 8px 12px;
  text-align: center;
  vertical-align: middle;
}

.timetable-grid th {
  background: rgba(var(--v-theme-surface-light));
  font-size: 0.8125rem;
  font-weight: 600;
}

.period-col {
  inline-size: 140px;
  text-align: start;
}

.break-row {
  background: rgba(var(--v-theme-warning), 0.08);
}

.grid-cell.editable {
  cursor: pointer;
  min-block-size: 56px;
}

.grid-cell.editable:hover {
  background: rgba(var(--v-theme-primary), 0.06);
}

.grid-cell.conflict {
  background: rgba(var(--v-theme-error), 0.12);
  outline: 1px solid rgb(var(--v-theme-error));
}
</style>
