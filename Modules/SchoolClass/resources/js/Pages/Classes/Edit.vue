<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ClassForm from './_Form.vue'

const props = defineProps({
  class: {
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
  grade_level_id: props.class.grade_level_id,
  academic_year_id: props.class.academic_year_id,
  classroom_id: props.class.classroom_id,
  name: props.class.name,
  section_code: props.class.section_code,
  capacity: props.class.capacity,
  class_teacher_id: props.class.class_teacher_id,
  assistant_teacher_id: props.class.assistant_teacher_id,
  is_active: props.class.is_active,
})

function submit() {
  form.put(`/academic/classes/${props.class.id}`)
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
        {{ $props.class.name }} {{ $props.class.section_code }}
      </VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <ClassForm
          :form="form"
          :academic-years="academicYears"
          :grade-levels="gradeLevels"
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
