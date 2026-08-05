<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import SubjectForm from './_Form.vue'

defineProps({
  branches: { type: Array, default: () => [] },
  subjectTypes: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  name_en: '',
  name_ar: '',
  code: '',
  description_en: '',
  description_ar: '',
  short_name: '',
  branch_id: null,
  subject_type: 'core',
  pass_grade: 60,
  credit_hours: 1,
  max_marks: 100,
  color: '#2563eb',
  icon: '',
  is_active: true,
})

function submit() {
  form.post('/academic/subjects')
}
</script>

<template>
  <Head :title="t('subject.addNew')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('subject.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('subject.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <SubjectForm
          :form="form"
          :branches="branches"
          :subject-types="subjectTypes"
        />
      </form>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
      <VBtn
        variant="tonal"
        color="secondary"
        :href="'/academic/subjects'"
        :as="Link"
      >
        {{ t('common.cancel') }}
      </VBtn>
      <VBtn
        type="submit"
        :loading="form.processing"
        color="primary"
        @click="submit"
      >
        {{ t('common.save') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
