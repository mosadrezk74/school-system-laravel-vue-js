<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ExamForm from './_Form.vue'
import ExamSubjectsSection from './_SubjectsSection.vue'

const props = defineProps({
  exam: { type: Object, required: true },
  semesters: { type: Array, default: () => [] },
  classes: { type: Array, default: () => [] },
  subjects: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  name: props.exam.name || '',
  code: props.exam.code,
  exam_type: props.exam.exam_type,
  semester_id: props.exam.semester_id,
  class_id: props.exam.class_id,
  start_date: props.exam.start_date,
  end_date: props.exam.end_date,
  passing_grade: props.exam.passing_grade,
  total_marks: props.exam.total_marks,
  weightage: props.exam.weightage,
  is_published: props.exam.is_published,
})

function submit() {
  form.put(`/examination/exams/${props.exam.id}`)
}
</script>

<template>
  <Head :title="t('exams.edit')" />

  <VCard class="mb-6">
    <VCardItem>
      <VCardTitle>{{ t('exams.edit') }}</VCardTitle>
      <VCardSubtitle>{{ exam.name }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <ExamForm
          :form="form"
          :semesters="semesters"
          :classes="classes"
        />
      </form>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
      <VBtn
        variant="tonal"
        color="secondary"
        :href="'/examination/exams'"
        :as="Link"
      >
        {{ t('common.cancel') }}
      </VBtn>
      <VBtn
        :loading="form.processing"
        @click="submit"
      >
        {{ t('common.update') }}
      </VBtn>
    </VCardText>
  </VCard>

  <ExamSubjectsSection :exam="exam" :subjects="subjects" :classes="classes" />
</template>
