<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ExamForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

defineProps({
  semesters: { type: Array, default: () => [] },
  classes: { type: Array, default: () => [] },
  examTypes: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  name: '',
  exam_type: 'midterm',
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

  <AppPageHeader
    :title="t('exams.addNew')"
    :subtitle="t('exams.subtitle')"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <ExamForm
          :form="form"
          :semesters="semesters"
          :classes="classes"
          :exam-types="examTypes"
        />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
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
    </VCardActions>
  </VCard>
</template>
