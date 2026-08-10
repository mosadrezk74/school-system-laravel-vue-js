<script setup>
import { Link, router, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import AppDateField from '@/components/AppDateField.vue'

const props = defineProps({
  exam: { type: Object, required: true },
  subjects: { type: Array, default: () => [] },
  classes: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

const dialog = ref(false)
const editingSubject = ref(null)

const classOptions = computed(() => props.classes.map(c => ({
  title: c.name,
  value: c.id,
})))

const subjectOptions = computed(() => props.subjects.map(s => ({
  title: s.name,
  value: s.id,
})))

const form = useForm({
  subject_id: null,
  class_id: null,
  total_marks: 100,
  passing_marks: 60,
  exam_date: null,
  exam_time: null,
  room: '',
})

function openCreate() {
  editingSubject.value = null
  form.reset()
  form.total_marks = 100
  form.passing_marks = 60
  dialog.value = true
}

function openEdit(examSubject) {
  editingSubject.value = examSubject
  form.subject_id = examSubject.subject_id
  form.class_id = examSubject.class_id
  form.total_marks = examSubject.total_marks
  form.passing_marks = examSubject.passing_marks
  form.exam_date = examSubject.exam_date
  form.exam_time = examSubject.exam_time
  form.room = examSubject.room
  dialog.value = true
}

function submit() {
  const options = {
    preserveScroll: true,
    onSuccess: () => {
      dialog.value = false
    },
  }

  if (editingSubject.value)
    form.put(`/examination/exams/${props.exam.id}/subjects/${editingSubject.value.id}`, options)
  else
    form.post(`/examination/exams/${props.exam.id}/subjects`, options)
}

function remove(examSubject) {
  askConfirm({
    message: t('exams.confirmRemoveSubject'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/examination/exams/${props.exam.id}/subjects/${examSubject.id}`, { preserveScroll: true })
    },
  })
}
</script>

<template>
  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('exams.subjectsSection') }}</VCardTitle>
      <VCardSubtitle>{{ t('exams.subjectsSectionDesc') }}</VCardSubtitle>

      <template #append>
        <VBtn prepend-icon="bx-plus" @click="openCreate">
          {{ t('exams.addSubject') }}
        </VBtn>
      </template>
    </VCardItem>

    <VDivider />

    <VCardText v-if="!exam.exam_subjects || exam.exam_subjects.length === 0" class="text-disabled">
      {{ t('exams.noSubjects') }}
    </VCardText>

    <VTable v-else>
      <thead>
        <tr>
          <th>{{ t('exams.subject') }}</th>
          <th>{{ t('exams.class') }}</th>
          <th>{{ t('exams.subjectTotalMarks') }}</th>
          <th>{{ t('exams.passingMarks') }}</th>
          <th>{{ t('exams.examDate') }}</th>
          <th>{{ t('exams.room') }}</th>
          <th />
        </tr>
      </thead>
      <tbody>
        <tr v-for="examSubject in exam.exam_subjects" :key="examSubject.id">
          <td>{{ examSubject.subject_name ?? '—' }}</td>
          <td>{{ examSubject.class_name ?? '—' }}</td>
          <td>{{ examSubject.total_marks }}</td>
          <td>{{ examSubject.passing_marks }}</td>
          <td>{{ examSubject.exam_date ?? '—' }}</td>
          <td>{{ examSubject.room ?? '—' }}</td>
          <td class="text-end">
            <IconBtn
              :href="`/examination/exams/${exam.id}/subjects/${examSubject.id}/results`"
              :as="Link"
            >
              <VIcon icon="bx-edit-alt" />
            </IconBtn>
            <IconBtn @click="openEdit(examSubject)">
              <VIcon icon="bx-edit" />
            </IconBtn>
            <IconBtn @click="remove(examSubject)">
              <VIcon icon="bx-trash" />
            </IconBtn>
          </td>
        </tr>
      </tbody>
    </VTable>
  </VCard>

  <VDialog v-model="dialog" max-width="560">
    <VCard>
      <VCardItem>
        <VCardTitle>{{ editingSubject ? t('exams.editSubject') : t('exams.addSubject') }}</VCardTitle>
      </VCardItem>
      <VCardText>
        <VRow>
          <VCol cols="12" md="6">
            <VSelect
              v-model="form.subject_id"
              :items="subjectOptions"
              :label="t('exams.subject')"
              :error-messages="form.errors.subject_id"
            />
          </VCol>
          <VCol cols="12" md="6">
            <VSelect
              v-model="form.class_id"
              :items="classOptions"
              :label="t('exams.class')"
              :error-messages="form.errors.class_id"
            />
          </VCol>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.total_marks"
              type="number"
              min="0"
              :label="t('exams.subjectTotalMarks')"
              :error-messages="form.errors.total_marks"
            />
          </VCol>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.passing_marks"
              type="number"
              min="0"
              :label="t('exams.passingMarks')"
              :error-messages="form.errors.passing_marks"
            />
          </VCol>
          <VCol cols="12" md="4">
            <AppDateField
              v-model="form.exam_date"
              :label="t('exams.examDate')"
              :error-messages="form.errors.exam_date"
            />
          </VCol>
          <VCol cols="12" md="4">
            <VTextField
              v-model="form.exam_time"
              type="time"
              :label="t('exams.examTime')"
              :error-messages="form.errors.exam_time"
            />
          </VCol>
          <VCol cols="12" md="4">
            <VTextField
              v-model="form.room"
              :label="t('exams.room')"
              :error-messages="form.errors.room"
            />
          </VCol>
        </VRow>
      </VCardText>
      <VCardText class="d-flex justify-end gap-3">
        <VBtn variant="tonal" color="secondary" @click="dialog = false">
          {{ t('common.cancel') }}
        </VBtn>
        <VBtn :loading="form.processing" @click="submit">
          {{ t('common.save') }}
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>

  <AppConfirmDialog
    v-model="isConfirmOpen"
    :title="confirmDialog.title"
    :message="confirmDialog.message"
    :confirm-text="confirmDialog.confirmText"
    :color="confirmDialog.color ?? 'error'"
    @confirm="runConfirm"
  />
</template>
