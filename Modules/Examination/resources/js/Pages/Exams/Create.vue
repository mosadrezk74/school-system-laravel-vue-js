<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ExamForm from './_Form.vue'

defineProps({
  academicYears: { type: Array, default: () => [] },
  semesters: { type: Array, default: () => [] },
  classes: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  name: {},
  code: '',
  exam_type: 'midterm',
  academic_year_id: null,
  semester_id: null,
  class_id: null,
  start_date: null,
  end_date: null,
  passing_grade: 60,
  total_marks: 100,
  weightage: 1,
  is_published: false,
})

function submit() {
  form.post('/examination/exams')
}
</script>

<template>
  <Head :title="t('exams.addNew')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('exams.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('exams.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <ExamForm
          :form="form"
          :academic-years="academicYears"
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
        {{ t('common.save') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
