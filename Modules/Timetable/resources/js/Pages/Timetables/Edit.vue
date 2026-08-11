<script setup>
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import CellDialog from './_CellDialog.vue'
import TimetableGrid from './_Grid.vue'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  timetable: { type: Object, required: true },
  periods: { type: Array, default: () => [] },
  weekdays: { type: Array, default: () => [] },
  subjects: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] },
  classrooms: { type: Array, default: () => [] },
})

const { t } = useI18n()

function cellKey(day, periodId) {
  return `${day}-${periodId}`
}

const entries = reactive({})
for (const entry of props.timetable.entries ?? []) {
  const periodId = entry.period_id ?? entry.period?.id

  if (!Number.isInteger(periodId))
    continue

  entries[cellKey(entry.day_of_week, periodId)] = {
    day_of_week: entry.day_of_week,
    period_id: periodId,
    subject_id: entry.subject?.id ?? null,
    teacher_id: entry.teacher?.id ?? null,
    classroom_id: entry.classroom?.id ?? null,
    is_online: entry.is_online ?? false,
  }
}

const dialogOpen = ref(false)
const activeCell = ref(null)

function openCell(day, period) {
  const dayLabel = props.weekdays.find(w => w.value === day)?.label ?? ''
  activeCell.value = {
    day,
    period,
    dayLabel,
    entry: entries[cellKey(day, period.id)] ?? null,
  }
  dialogOpen.value = true
}

function saveCell(payload) {
  const { day, period } = activeCell.value

  if (!Number.isInteger(period?.id))
    return

  entries[cellKey(day, period.id)] = {
    ...payload,
    day_of_week: day,
    period_id: period.id,
  }
}

const deleteDialogOpen = ref(false)
const pendingDelete = ref(null)

function confirmDelete({ day, period }) {
  if (!Number.isInteger(period?.id))
    return

  pendingDelete.value = { day, period }
  deleteDialogOpen.value = true
}

function toggleOnline({ day, period }) {
  const cell = entries[cellKey(day, period.id)]

  if (cell)
    cell.is_online = !cell.is_online
}

function removeCell() {
  const target = pendingDelete.value

  if (target)
    delete entries[cellKey(target.day, target.period.id)]

  pendingDelete.value = null
}

function clearCell() {
  const { day, period } = activeCell.value
  delete entries[cellKey(day, period.id)]
}

const conflictCells = ref([])

const form = useForm({ entries: [] })

function submit() {
  form.entries = Object.values(entries)
    .filter(value => Number.isInteger(value.period_id) && Number.isInteger(value.day_of_week))
    .map(value => ({
      day_of_week: value.day_of_week,
      period_id: value.period_id,
      subject_id: value.subject_id,
      teacher_id: value.teacher_id,
      classroom_id: value.classroom_id,
      is_break: false,
      is_online: value.is_online ?? false,
    }))

  conflictCells.value = []

  form.put(`/schedule/timetables/${props.timetable.id}/entries`, {
    preserveScroll: true,
  })
}

const conflictMessages = computed(() => {
  const errors = form.errors
  return Object.values(errors).filter(Boolean)
})

const subtitleLine = computed(() => {
  const parts = []

  if (props.timetable.class?.name)
    parts.push(`${t('timetables.grid.classLabel')}: ${props.timetable.class.name}`)

  if (props.timetable.academic_year?.name)
    parts.push(`${t('timetables.grid.academicYearLabel')}: ${props.timetable.academic_year.name}`)

  if (props.timetable.semester?.name)
    parts.push(props.timetable.semester.name)

  return parts.join(' — ')
})

const timetablesIndexUrl = '/schedule/timetables'

function goBack() {
  router.visit(timetablesIndexUrl)
}
</script>

<template>
  <Head :title="t('timetables.grid.title')" />

  <!--
    The timetable grid is the page, so it gets the full content width rather
    than the narrower `app-form-card` measure used by field-based forms.
  -->
  <AppPageHeader
    :title="t('timetables.grid.title')"
    :subtitle="subtitleLine"
  >
    <template #actions>
      <VBtn
        color="error"
        variant="tonal"
        prepend-icon="bx-arrow-back"
        :href="timetablesIndexUrl"
        @click.prevent="goBack"
      >
        {{ t('common.back') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <VAlert
    v-if="conflictMessages.length"
    type="error"
    class="mb-4"
  >
    <div
      v-for="(message, index) in conflictMessages"
      :key="index"
    >
      {{ message }}
    </div>
  </VAlert>

  <VCard>
    <VCardText>
      <TimetableGrid
        :periods="periods"
        :weekdays="weekdays"
        :entries="entries"
        :subjects="subjects"
        :teachers="teachers"
        :conflict-cells="conflictCells"
        editable
        @cell-click="openCell($event.day, $event.period)"
        @cell-delete="confirmDelete"
        @cell-toggle-online="toggleOnline"
      />
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        :loading="form.processing"
        @click="submit"
      >
        {{ t('timetables.grid.save') }}
      </VBtn>
    </VCardActions>
  </VCard>

  <CellDialog
    v-model="dialogOpen"
    :cell="activeCell"
    :subjects="subjects"
    :teachers="teachers"
    :classrooms="classrooms"
    @save="saveCell"
    @clear="clearCell"
  />

  <AppConfirmDialog
    v-model="deleteDialogOpen"
    :message="t('timetables.grid.confirmDelete')"
    @confirm="removeCell"
  />
</template>
