<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import StudyMaterialForm from './_Form.vue'

const props = defineProps({
  studyMaterial: { type: Object, required: true },
  academicYears: { type: Array, default: () => [] },
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
  academic_year_id: props.studyMaterial.academic_year_id,
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

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('studyMaterials.edit') }}</VCardTitle>
      <VCardSubtitle>{{ studyMaterial.title }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <StudyMaterialForm
          :form="form"
          :academic-years="academicYears"
          :semesters="semesters"
          :classes="classes"
          :subjects="subjects"
          :teachers="teachers"
          is-edit
        />
      </form>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
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
    </VCardText>
  </VCard>
</template>
