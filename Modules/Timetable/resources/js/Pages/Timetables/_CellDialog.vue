<script setup>
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  cell: { type: Object, default: null },
  subjects: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] },
  classrooms: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:modelValue', 'save', 'clear'])

const { t } = useI18n()

const subjectId = ref(null)
const teacherId = ref(null)
const classroomId = ref(null)
const isOnline = ref(false)

watch(() => props.cell, (cell) => {
  subjectId.value = cell?.entry?.subject_id ?? null
  teacherId.value = cell?.entry?.teacher_id ?? null
  classroomId.value = cell?.entry?.classroom_id ?? null
  isOnline.value = cell?.entry?.is_online ?? false
}, { immediate: true })

/**
 * Only teachers assigned to the chosen subject may be picked. This mirrors the
 * server-side rule; it is a convenience filter, not the enforcement point.
 * Before a subject is chosen there is nothing to narrow by, so show none.
 */
const availableTeachers = computed(() => {
  if (!subjectId.value)
    return []

  return props.teachers.filter(teacher =>
    (teacher.subject_ids ?? []).includes(subjectId.value),
  )
})

// A teacher valid for the previous subject is usually invalid for the new one.
watch(subjectId, () => {
  if (teacherId.value && !availableTeachers.value.some(teacher => teacher.id === teacherId.value))
    teacherId.value = null
})

const isBreak = computed(() => Boolean(props.cell?.period?.is_break))

function close() {
  emit('update:modelValue', false)
}

/** A lesson is only meaningful with both a subject and the teacher giving it. */
const canSave = computed(() => Boolean(subjectId.value) && Boolean(teacherId.value))

function save() {
  if (!canSave.value)
    return

  emit('save', {
    subject_id: subjectId.value,
    teacher_id: teacherId.value,
    classroom_id: classroomId.value,
    is_online: isOnline.value,
  })
  close()
}

function clear() {
  emit('clear')
  close()
}
</script>

<template>
  <VDialog
    :model-value="modelValue"
    max-width="480"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard v-if="cell">
      <VCardItem>
        <VCardTitle>{{ t('timetables.grid.assignCell') }}</VCardTitle>
        <VCardSubtitle>
          {{ cell.dayLabel }} — {{ cell.period?.name }}
        </VCardSubtitle>
      </VCardItem>

      <VDivider />

      <VCardText v-if="isBreak">
        <VAlert type="info" variant="tonal">
          {{ t('timetables.periods.isBreak') }}
        </VAlert>
      </VCardText>

      <VCardText v-else>
        <VRow>
          <VCol cols="12">
            <VSelect
              v-model="subjectId"
              :items="subjects.map(s => ({ title: s.name, value: s.id }))"
              :label="t('timetables.subject')"
              clearable
            />
          </VCol>
          <VCol cols="12">
            <VSelect
              v-model="teacherId"
              :items="availableTeachers.map(tch => ({ title: tch.name, value: tch.id }))"
              :label="t('timetables.teacher')"
              :disabled="!subjectId"
              :hint="!subjectId
                ? t('timetables.grid.selectSubjectFirst')
                : (availableTeachers.length ? undefined : t('timetables.grid.noTeachersForSubject'))"
              persistent-hint
              clearable
            />
          </VCol>
          <VCol cols="12">
            <VSelect
              v-model="classroomId"
              :items="classrooms.map(c => ({ title: c.name, value: c.id }))"
              :label="t('timetables.classroom')"
              clearable
            />
          </VCol>
          <VCol cols="12">
            <VSwitch
              v-model="isOnline"
              :label="t('timetables.grid.isOnline')"
            />
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <VCardText class="d-flex justify-space-between gap-3">
        <VBtn variant="tonal" color="error" @click="clear">
          {{ t('common.clear') }}
        </VBtn>
        <div class="d-flex gap-3">
          <VBtn variant="tonal" color="secondary" @click="close">
            {{ t('common.cancel') }}
          </VBtn>
          <VBtn v-if="!isBreak" :disabled="!canSave" @click="save">
            {{ t('common.save') }}
          </VBtn>
        </div>
      </VCardText>
    </VCard>
  </VDialog>
</template>
