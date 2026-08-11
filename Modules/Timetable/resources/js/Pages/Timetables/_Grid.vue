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

const emit = defineEmits(['cell-click', 'cell-delete'])

const { t } = useI18n()

function startMeeting(entry) {
  router.patch(`/meetings/timetable-entries/${entry.id}/start-meeting`)
}

function cellKey(day, periodId) {
  return `${day}-${periodId}`
}

function entryAt(day, period) {
  return props.entries[cellKey(day, period.id)] ?? null
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

/**
 * The real period object is handed straight to the parent, so the dialog
 * always receives an authentic period.id rather than one reconstructed from
 * the reactive cell key.
 */
function onCellClick(day, period) {
  if (!props.editable || period.is_break)
    return

  emit('cell-click', { day, period })
}

function onDelete(day, period) {
  emit('cell-delete', { day, period })
}

const firstLessonPeriod = props.periods.find(period => !period.is_break) ?? null
</script>

<template>
  <VAlert v-if="!periods.length" type="info" variant="tonal">
    {{ t('timetables.grid.noPeriods') }}
  </VAlert>

  <VAlert v-else-if="!weekdays.length" type="info" variant="tonal">
    {{ t('timetables.grid.noWeekdays') }}
  </VAlert>

  <div v-else class="timetable-grid-wrapper">
    <table class="timetable-grid">
      <thead>
        <tr>
          <th class="day-col">
            {{ t('timetables.grid.days') }}
          </th>

          <th
            v-for="period in periods"
            :key="period.id"
            :class="{ 'break-head': period.is_break }"
          >
            <div class="text-body-2 font-weight-bold">
              {{ period.is_break ? t('timetables.periods.isBreak') : period.name }}
            </div>
            <div class="text-caption text-medium-emphasis">
              {{ period.start_time }} - {{ period.end_time }}
            </div>
          </th>

          <th v-if="editable" class="add-col">
            {{ t('timetables.grid.add') }}
          </th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="day in weekdays" :key="day.value">
          <th class="day-col">
            {{ day.label }}
          </th>

          <td
            v-for="period in periods"
            :key="period.id"
            class="grid-cell"
            :class="{
              editable: editable && !period.is_break,
              'break-cell': period.is_break,
              conflict: isConflict(day.value, period.id),
            }"
            @click="onCellClick(day.value, period)"
          >
            <div v-if="period.is_break" class="text-caption text-medium-emphasis">
              {{ t('timetables.periods.isBreak') }}
            </div>

            <div v-else-if="entryAt(day.value, period)" class="lesson-card">
              <div class="lesson-card__body">
                <div class="lesson-card__subject">
                  {{ subjectName(entryAt(day.value, period).subject_id) }}
                  <VIcon
                    v-if="entryAt(day.value, period).is_online"
                    icon="bx-video"
                    size="14"
                  />
                </div>
                <div class="lesson-card__teacher">
                  {{ teacherName(entryAt(day.value, period).teacher_id) }}
                </div>
              </div>

              <VMenu v-if="editable">
                <template #activator="{ props: menuProps }">
                  <VBtn
                    v-bind="menuProps"
                    class="lesson-card__menu"
                    variant="text"
                    size="x-small"
                    icon="bx-chevron-down"
                    :aria-label="t('common.actions')"
                    @click.stop
                  />
                </template>

                <VList density="compact">
                  <VListItem
                    prepend-icon="bx-edit"
                    :title="t('common.edit')"
                    @click="onCellClick(day.value, period)"
                  />
                  <VListItem
                    prepend-icon="bx-trash"
                    :title="t('common.delete')"
                    class="text-error"
                    @click="onDelete(day.value, period)"
                  />
                </VList>
              </VMenu>

              <VBtn
                v-if="showMeetingActions && entryAt(day.value, period).is_online"
                size="x-small"
                variant="tonal"
                color="success"
                class="mt-1"
                @click.stop="startMeeting(entryAt(day.value, period))"
              >
                {{ t('timetables.grid.startMeeting') }}
              </VBtn>
            </div>

            <div v-else-if="editable" class="empty-cell">
              <VIcon icon="bx-plus" size="16" />
            </div>
          </td>

          <td v-if="editable" class="add-col">
            <VBtn
              icon="bx-plus"
              color="primary"
              size="small"
              :disabled="!firstLessonPeriod"
              :aria-label="t('timetables.grid.add')"
              @click="firstLessonPeriod && onCellClick(day.value, firstLessonPeriod)"
            />
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
  border-collapse: separate;
  border-spacing: 0;
  inline-size: 100%;
  min-inline-size: 900px;
}

.timetable-grid th,
.timetable-grid td {
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  padding: 8px;
  text-align: center;
  vertical-align: middle;
}

.timetable-grid thead th {
  background: rgba(var(--v-theme-surface-light));
  font-size: 0.8125rem;
  font-weight: 600;
}

.day-col {
  inline-size: 120px;
  background: rgba(var(--v-theme-surface-light));
  font-weight: 600;
  text-align: start;
}

.add-col {
  inline-size: 84px;
}

.break-head,
.break-cell {
  background: rgba(var(--v-theme-warning), 0.08);
}

.grid-cell {
  min-inline-size: 130px;
  block-size: 76px;
}

.grid-cell.editable {
  cursor: pointer;
}

.grid-cell.editable:hover .empty-cell {
  border-color: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-primary));
}

.grid-cell.conflict {
  outline: 2px solid rgb(var(--v-theme-error));
  outline-offset: -2px;
}

.empty-cell {
  display: flex;
  align-items: center;
  justify-content: center;
  block-size: 100%;
  min-block-size: 56px;
  border: 1px dashed rgba(var(--v-border-color), 0.4);
  border-radius: 8px;
  color: rgba(var(--v-theme-on-surface), 0.35);
}

.lesson-card {
  position: relative;
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 8px 10px;
  border-radius: 10px;
  background: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-on-primary));
  text-align: start;
}

.lesson-card__body {
  min-inline-size: 0;
  flex: 1;
}

.lesson-card__subject {
  font-size: 0.8125rem;
  font-weight: 700;
  line-height: 1.3;
}

.lesson-card__teacher {
  font-size: 0.75rem;
  opacity: 0.85;
  line-height: 1.3;
}

.lesson-card__menu {
  color: rgb(var(--v-theme-on-primary));
}
</style>
