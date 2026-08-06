<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import StudyMaterialForm from './_Form.vue'

defineProps({
  academicYears: { type: Array, default: () => [] },
  semesters: { type: Array, default: () => [] },
  classes: { type: Array, default: () => [] },
  subjects: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  title: {},
  description: {},
  class_id: null,
  subject_id: null,
  teacher_id: null,
  academic_year_id: null,
  semester_id: null,
  material_type: '',
  is_published: true,
})

function submit() {
  form.post('/assignments/study-materials')
}
</script>

<template>
  <Head :title="t('studyMaterials.addNew')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('studyMaterials.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('studyMaterials.subtitle') }}</VCardSubtitle>
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
        {{ t('common.save') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
