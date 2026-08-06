<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { computed, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import CellDialog from './_CellDialog.vue'
import TimetableGrid from './_Grid.vue'

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
  entries[cellKey(entry.day_of_week, entry.period_id)] = {
    subject_id: entry.subject?.id ?? null,
    teacher_id: entry.teacher?.id ?? null,
    classroom_id: entry.classroom?.id ?? null,
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
  entries[cellKey(day, period.id)] = payload
}

function clearCell() {
  const { day, period } = activeCell.value
  delete entries[cellKey(day, period.id)]
}

const conflictCells = ref([])

const form = useForm({ entries: [] })

function submit() {
  form.entries = Object.entries(entries).map(([key, value]) => {
    const [day, periodId] = key.split('-').map(Number)

    return {
      day_of_week: day,
      period_id: periodId,
      subject_id: value.subject_id,
      teacher_id: value.teacher_id,
      classroom_id: value.classroom_id,
      is_break: false,
    }
  })

  conflictCells.value = []

  form.put(`/schedule/timetables/${props.timetable.id}/entries`, {
    preserveScroll: true,
  })
}

const conflictMessages = computed(() => {
  const errors = form.errors
  return Object.values(errors).filter(Boolean)
})
</script>

<template>
  <Head :title="t('timetables.grid.title')" />

  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardItem>
          <VCardTitle>
            {{ timetable.class?.name }} {{ timetable.class?.section_code }}
          </VCardTitle>
          <VCardSubtitle>
            {{ timetable.academic_year?.name }}
            <template v-if="timetable.semester?.name">
              — {{ timetable.semester.name }}
            </template>
          </VCardSubtitle>
        </VCardItem>
      </VCard>
    </VCol>

    <VCol v-if="conflictMessages.length" cols="12">
      <VAlert type="error" variant="tonal">
        <div v-for="(message, index) in conflictMessages" :key="index">
          {{ message }}
        </div>
      </VAlert>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardItem>
          <VCardTitle>{{ t('timetables.grid.title') }}</VCardTitle>
          <VCardSubtitle>{{ t('timetables.grid.subtitle') }}</VCardSubtitle>
        </VCardItem>

        <VDivider />

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
          />
        </VCardText>

        <VDivider />

        <VCardText class="d-flex justify-end">
          <VBtn :loading="form.processing" @click="submit">
            {{ t('timetables.grid.save') }}
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>

  <CellDialog
    v-model="dialogOpen"
    :cell="activeCell"
    :subjects="subjects"
    :teachers="teachers"
    :classrooms="classrooms"
    @save="saveCell"
    @clear="clearCell"
  />
</template>
