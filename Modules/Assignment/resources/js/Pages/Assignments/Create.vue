<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AssignmentForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

defineProps({
  classes: { type: Array, default: () => [] },
  classSubjects: { type: Array, default: () => [] },
  currentSemester: { type: Object, default: null },
  assignmentTypes: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  title: '',
  description: '',
  class_id: null,
  subject_id: null,
  teacher_id: null,
  max_score: 100,
  deadline: null,
  assignment_type: '',
  is_group_work: false,
  is_published: false,
})

function submit() {
  form.post('/assignments/assignments')
}
</script>

<template>
  <Head :title="t('assignments.addNew')" />

  <AppPageHeader
    :title="t('assignments.addNew')"
    :subtitle="t('assignments.subtitle')"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <AssignmentForm
          :form="form"
          :classes="classes"
          :class-subjects="classSubjects"
          :current-semester="currentSemester"
          :assignment-types="assignmentTypes"
        />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        :href="'/assignments/assignments'"
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
