<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ClassForm from './_Form.vue'

const props = defineProps({
  schoolClass: {
    type: Object,
    required: true,
  },
  academicYears: {
    type: Array,
    default: () => [],
  },
  gradeLevels: {
    type: Array,
    default: () => [],
  },
  branches: {
    type: Array,
    default: () => [],
  },
  classrooms: {
    type: Array,
    default: () => [],
  },
  teachers: {
    type: Array,
    default: () => [],
  },
})

const { t } = useI18n()

const form = useForm({
  branch_id: props.schoolClass.branch_id,
  grade_level_id: props.schoolClass.grade_level_id,
  academic_year_id: props.schoolClass.academic_year_id,
  classroom_id: props.schoolClass.classroom_id,
  name: props.schoolClass.name,
  section_code: props.schoolClass.section_code,
  capacity: props.schoolClass.capacity,
  class_teacher_id: props.schoolClass.class_teacher_id,
  assistant_teacher_id: props.schoolClass.assistant_teacher_id,
  is_active: props.schoolClass.is_active,
})

function submit() {
  form.put(`/academic/classes/${props.schoolClass.id}`)
}
</script>

<template>
  <Head :title="t('classes.edit')" />

  <VCard>
    <VCardItem>
      <VCardTitle>
        {{ t('classes.edit') }}
      </VCardTitle>

      <VCardSubtitle>
        {{ schoolClass.name }} {{ schoolClass.section_code }}
      </VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <ClassForm
          :form="form"
          :academic-years="academicYears"
          :grade-levels="gradeLevels"
          :branches="branches"
          :classrooms="classrooms"
          :teachers="teachers"
          is-edit
        />
      </form>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
      <VBtn
        :as="Link"
        href="/academic/classes"
        color="secondary"
        variant="tonal"
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