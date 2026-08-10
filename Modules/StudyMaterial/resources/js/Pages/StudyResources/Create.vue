<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import StudyResourceForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

defineProps({
  subjects: { type: Array, default: () => [] },
  gradeLevels: { type: Array, default: () => [] },
  classrooms: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] },
  types: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  title: '',
  description: '',
  subject_id: null,
  grade_level_id: null,
  classroom_id: null,
  teacher_id: null,
  type: null,
  external_url: '',
  file: null,
})

function submit() {
  form.post('/academic/study-resources')
}
</script>

<template>
  <Head :title="t('studyResources.addNew')" />

  <AppPageHeader
    :title="t('studyResources.addNew')"
    :subtitle="t('studyResources.subtitle')"
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
        {{ t('common.save') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
