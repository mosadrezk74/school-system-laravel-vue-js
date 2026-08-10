<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import StudyMaterialForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  studyMaterial: { type: Object, required: true },
  semesters: { type: Array, default: () => [] },
  classes: { type: Array, default: () => [] },
  subjects: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  title: props.studyMaterial.title || '',
  description: props.studyMaterial.description || '',
  class_id: props.studyMaterial.class_id,
  subject_id: props.studyMaterial.subject_id,
  teacher_id: props.studyMaterial.teacher_id,
  semester_id: props.studyMaterial.semester_id,
  material_type: props.studyMaterial.material_type,
  is_published: props.studyMaterial.is_published,
})

function submit() {
  form.put(`/assignments/study-materials/${props.studyMaterial.id}`)
}
</script>

<template>
  <Head :title="t('studyMaterials.edit')" />

  <AppPageHeader
    :title="t('studyMaterials.edit')"
    :subtitle="studyMaterial.title"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <StudyMaterialForm
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
        :href="'/assignments/study-materials'"
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
