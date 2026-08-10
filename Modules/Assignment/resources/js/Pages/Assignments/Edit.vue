<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AssignmentForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  assignment: { type: Object, required: true },
  semesters: { type: Array, default: () => [] },
  classes: { type: Array, default: () => [] },
  subjects: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  title: props.assignment.title || '',
  description: props.assignment.description || '',
  class_id: props.assignment.class_id,
  subject_id: props.assignment.subject_id,
  teacher_id: props.assignment.teacher_id,
  semester_id: props.assignment.semester_id,
  max_score: props.assignment.max_score,
  deadline: props.assignment.deadline ? props.assignment.deadline.slice(0, 16) : null,
  assignment_type: props.assignment.assignment_type,
  is_group_work: props.assignment.is_group_work,
  is_published: props.assignment.is_published,
})

function submit() {
  form.put(`/assignments/assignments/${props.assignment.id}`)
}
</script>

<template>
  <Head :title="t('assignments.edit')" />

  <AppPageHeader
    :title="t('assignments.edit')"
    :subtitle="assignment.title"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <AssignmentForm
          :form="form"
          :semesters="semesters"
          :classes="classes"
          :subjects="subjects"
          :teachers="teachers"
          is-edit
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
        {{ t('common.update') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
