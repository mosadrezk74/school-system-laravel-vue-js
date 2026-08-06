<script setup>
import { ref, watch } from 'vue'
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

function close() {
  emit('update:modelValue', false)
}

function save() {
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

      <VCardText>
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
              :items="teachers.map(tch => ({ title: tch.name, value: tch.id }))"
              :label="t('timetables.teacher')"
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
          <VBtn @click="save">
            {{ t('common.save') }}
          </VBtn>
        </div>
      </VCardText>
    </VCard>
  </VDialog>
</template>
