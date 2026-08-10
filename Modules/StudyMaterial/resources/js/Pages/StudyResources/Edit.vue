<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import StudyResourceForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  studyResource: { type: Object, required: true },
  subjects: { type: Array, default: () => [] },
  gradeLevels: { type: Array, default: () => [] },
  classrooms: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] },
  types: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  _method: 'put',
  title: props.studyResource.title || '',
  description: props.studyResource.description || '',
  subject_id: props.studyResource.subject_id,
  grade_level_id: props.studyResource.grade_level_id,
  classroom_id: props.studyResource.classroom_id,
  teacher_id: props.studyResource.teacher_id,
  type: props.studyResource.type,
  external_url: props.studyResource.external_url || '',
  file: null,
})

function submit() {
  form.post(`/academic/study-resources/${props.studyResource.id}`, { forceFormData: true })
}
</script>

<template>
  <Head :title="t('studyResources.edit')" />

  <AppPageHeader
    :title="t('studyResources.edit')"
    :subtitle="studyResource.title"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <StudyResourceForm
          :form="form"
          :subjects="subjects"
          :grade-levels="gradeLevels"
          :classrooms="classrooms"
          :teachers="teachers"
          :types="types"
          :current-file-name="studyResource.file_name"
        />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        :href="'/academic/study-resources'"
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
