<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import StudyResourceForm from './_Form.vue'

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

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('studyResources.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('studyResources.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

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

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
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
    </VCardText>
  </VCard>
</template>
