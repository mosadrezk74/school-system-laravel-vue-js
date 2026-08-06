<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import SubjectForm from './_Form.vue'

const props = defineProps({
  subject: { type: Object, required: true },
  subjectTypes: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  name: props.subject.name || '',
  code: props.subject.code || '',
  description: props.subject.description || '',
  short_name: props.subject.short_name || '',
  subject_type: props.subject.subject_type || 'core',
  pass_grade: props.subject.pass_grade || 60,
  credit_hours: props.subject.credit_hours || 1,
  max_marks: props.subject.max_marks || 100,
  color: props.subject.color || '#2563eb',
  icon: props.subject.icon || '',
  is_active: props.subject.is_active !== undefined ? props.subject.is_active : true,
})

function submit() {
  form.put(`/academic/subjects/${props.subject.id}`)
}
</script>

<template>
  <Head :title="t('subject.edit')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('subject.edit') }}</VCardTitle>
      <VCardSubtitle>{{ subject.name || subject.code }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <SubjectForm
          :form="form"
          :subject-types="subjectTypes"
          is-edit
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
        {{ t('common.update') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
